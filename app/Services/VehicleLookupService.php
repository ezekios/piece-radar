<?php

namespace App\Services;

use App\Contracts\PlateProviderInterface;
use App\Data\VehicleLookupResult;
use Illuminate\Support\Facades\Cache;

class VehicleLookupService
{
    public function __construct(private PlateProviderInterface $provider)
    {
    }

    public function lookup(string $licensePlate): VehicleLookupResult
    {
        $normalizedPlate = $this->normalizePlate($licensePlate);

        if (! $this->isValidPlate($normalizedPlate)) {
            return VehicleLookupResult::failure(
                'invalid_format',
                $normalizedPlate,
                VehicleLookupResult::invalidFormatMessage(),
            );
        }

        $cacheKey = $this->cacheKey($normalizedPlate);
        $cachedResult = Cache::get($cacheKey);

        if (is_array($cachedResult)) {
            return VehicleLookupResult::fromArray($cachedResult);
        }

        $result = $this->provider->lookup($normalizedPlate);

        if ($result->success || $result->status === 'not_found') {
            Cache::put($cacheKey, $result->toArray(), now()->addHours(24));
        }

        return $result;
    }

    public function normalizePlate(string $licensePlate): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/i', '', trim($licensePlate)) ?? '');
    }

    private function isValidPlate(string $normalizedPlate): bool
    {
        return preg_match('/^[A-Z0-9]{5,12}$/', $normalizedPlate) === 1;
    }

    private function cacheKey(string $normalizedPlate): string
    {
        return 'vehicle_lookup:' . hash('sha256', $normalizedPlate);
    }
}
