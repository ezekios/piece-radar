<?php

namespace App\Services;

use App\Contracts\PlateProviderInterface;
use App\Data\VehicleIdentity;
use App\Data\VehicleLookupResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ApiPlaqueProvider implements PlateProviderInterface
{
    public function lookup(string $normalizedPlate): VehicleLookupResult
    {
        $key = (string) config('services.api_plaque.key');
        $host = (string) config('services.api_plaque.host');

        if ($key === '' || $host === '') {
            return VehicleLookupResult::failure(
                'not_configured',
                $normalizedPlate,
                VehicleLookupResult::genericUnavailableMessage(),
            );
        }

        try {
            $response = Http::baseUrl('https://' . $host)
                ->acceptJson()
                ->withHeaders([
                    'x-rapidapi-key' => $key,
                    'x-rapidapi-host' => $host,
                    'Content-Type' => 'application/json',
                ])
                ->timeout((int) config('services.api_plaque.timeout', 8))
                ->get('/', ['plaque' => $normalizedPlate]);
        } catch (ConnectionException) {
            $this->logProviderIssue('timeout');

            return VehicleLookupResult::failure(
                'provider_unavailable',
                $normalizedPlate,
                VehicleLookupResult::genericUnavailableMessage(),
            );
        } catch (Throwable $exception) {
            $this->logProviderIssue('exception', ['exception' => $exception::class]);

            return VehicleLookupResult::failure(
                'provider_unavailable',
                $normalizedPlate,
                VehicleLookupResult::genericUnavailableMessage(),
            );
        }

        if ($response->status() === 400) {
            return VehicleLookupResult::failure(
                'invalid_format',
                $normalizedPlate,
                VehicleLookupResult::invalidFormatMessage(),
            );
        }

        if ($response->status() === 404) {
            return VehicleLookupResult::failure(
                'not_found',
                $normalizedPlate,
                VehicleLookupResult::notFoundMessage(),
            );
        }

        if (in_array($response->status(), [401, 429], true) || $response->serverError()) {
            $this->logProviderIssue('http_error', ['status' => $response->status()]);

            return VehicleLookupResult::failure(
                'provider_unavailable',
                $normalizedPlate,
                VehicleLookupResult::genericUnavailableMessage(),
            );
        }

        if (! $response->successful()) {
            $this->logProviderIssue('unexpected_status', ['status' => $response->status()]);

            return VehicleLookupResult::failure(
                'provider_unavailable',
                $normalizedPlate,
                VehicleLookupResult::genericUnavailableMessage(),
            );
        }

        return $this->resultFromResponse($response, $normalizedPlate);
    }

    private function resultFromResponse(Response $response, string $normalizedPlate): VehicleLookupResult
    {
        $payload = $response->json();

        if (
            ! is_array($payload)
            || ($payload['error'] ?? false) === true
        ) {
            $this->logProviderIssue('invalid_payload', ['status' => $response->status()]);

            return VehicleLookupResult::failure(
                'provider_unavailable',
                $normalizedPlate,
                VehicleLookupResult::genericUnavailableMessage(),
            );
        }

        $data = is_array($payload['data'] ?? null)
            ? $payload['data']
            : $payload;

        $vehicle = $this->vehicleFromPayloadData($data);

        if ($vehicle->brand === null || $vehicle->model === null) {
            $this->logProviderIssue('missing_identity', ['status' => $response->status()]);

            return VehicleLookupResult::failure(
                'provider_unavailable',
                $normalizedPlate,
                VehicleLookupResult::genericUnavailableMessage(),
            );
        }

        return VehicleLookupResult::success($normalizedPlate, $vehicle);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function vehicleFromPayloadData(array $data): VehicleIdentity
    {
        $version = $this->firstClean($data, ['AWN_version', 'version']);
        $year = $this->yearFromDate($this->firstClean($data, ['AWN_date_mise_en_circulation_us']))
            ?? $this->yearFromDate($this->firstClean($data, ['AWN_date_mise_en_circulation']))
            ?? $this->yearFromValue($data['annee'] ?? null)
            ?? $this->yearFromDate($this->clean($data['date_mise_en_circulation_us'] ?? null))
            ?? $this->yearFromDate($this->clean($data['date_mise_circulation'] ?? null));
        $fuel = $this->firstClean($data, ['AWN_energie_description', 'AWN_energie', 'energie', 'motorisation']);
        $engine = $this->firstClean($data, ['AWN_label_moteur'])
            ?? $this->engineFromVersion($version);

        return new VehicleIdentity(
            brand: $this->formatName($this->firstClean($data, ['AWN_marque', 'marque'])),
            model: $this->formatName($this->firstClean($data, ['AWN_modele', 'modele'])),
            year: $year,
            version: $version,
            engine: $engine,
            fuel: $this->formatName($fuel),
            engineCode: $this->firstClean($data, ['AWN_code_moteur', 'code_moteur']),
            kType: $this->firstClean($data, ['AWN_k_type', 'k_type']),
            vin: $this->firstClean($data, ['AWN_VIN', 'VIN', 'vin']),
            typeMine: $this->firstClean($data, ['AWN_type_mine', 'type_mine']),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $keys
     */
    private function firstClean(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $this->clean($data[$key] ?? null);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function clean(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '' || Str::upper($value) === 'INCONNU') {
            return null;
        }

        return $value;
    }

    private function formatName(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = mb_convert_case(mb_strtolower($value), MB_CASE_TITLE, 'UTF-8');

        return preg_replace_callback(
            '/\b(i|ii|iii|iv|v|vi|vii|viii|ix|x)\b/i',
            fn (array $matches): string => mb_strtoupper($matches[0]),
            $value,
        ) ?: $value;
    }

    private function yearFromDate(?string $date): ?int
    {
        if ($date === null) {
            return null;
        }

        try {
            return Carbon::parse($date)->year;
        } catch (Throwable) {
            return null;
        }
    }

    private function yearFromValue(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $year = (int) $value;

        return $year >= 1900 && $year <= ((int) date('Y')) + 1
            ? $year
            : null;
    }

    private function engineFromVersion(?string $version): ?string
    {
        if ($version === null) {
            return null;
        }

        return trim(Str::before($version, '[')) ?: null;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function logProviderIssue(string $reason, array $context = []): void
    {
        Log::warning('License plate provider lookup failed.', [
            'provider' => 'api_plaque',
            'reason' => $reason,
            ...$context,
        ]);
    }
}
