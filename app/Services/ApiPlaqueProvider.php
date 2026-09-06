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
            || ! is_array($payload['data'] ?? null)
        ) {
            $this->logProviderIssue('invalid_payload', ['status' => $response->status()]);

            return VehicleLookupResult::failure(
                'provider_unavailable',
                $normalizedPlate,
                VehicleLookupResult::genericUnavailableMessage(),
            );
        }

        $vehicle = $this->vehicleFromPayloadData($payload['data']);

        if ($vehicle->brand === null && $vehicle->model === null) {
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
        $version = $this->clean($data['version'] ?? null);

        return new VehicleIdentity(
            brand: $this->formatName($this->clean($data['marque'] ?? null)),
            model: $this->formatName($this->clean($data['modele'] ?? null)),
            year: $this->yearFromDate($this->clean($data['date_mise_en_circulation_us'] ?? null)),
            version: $version,
            engine: $this->engineFromVersion($version),
            fuel: $this->formatName($this->clean($data['energie'] ?? null)),
            engineCode: $this->clean($data['code_moteur'] ?? null),
            kType: $this->clean($data['k_type'] ?? null),
            vin: $this->clean($data['VIN'] ?? null),
            typeMine: $this->clean($data['type_mine'] ?? null),
        );
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
