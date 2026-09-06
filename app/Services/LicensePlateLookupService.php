<?php

namespace App\Services;

class LicensePlateLookupService
{
    public function __construct(private VehicleLookupService $vehicleLookupService)
    {
    }

    /**
     * @return array{
     *     success: bool,
     *     status: string,
     *     normalized_plate: string,
     *     message: string,
     *     vehicle: ?array<string, mixed>
     * }
     */
    public function lookup(string $licensePlate): array
    {
        return $this->vehicleLookupService->lookup($licensePlate)->toArray();
    }
}
