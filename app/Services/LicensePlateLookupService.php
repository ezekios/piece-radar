<?php

namespace App\Services;

class LicensePlateLookupService
{
    /**
     * Prepare a license plate lookup response without calling an external API.
     *
     * @return array{
     *     success: bool,
     *     status: string,
     *     normalized_plate: string,
     *     message: string,
     *     vehicle: null
     * }
     */
    public function lookup(string $licensePlate): array
    {
        $normalizedPlate = strtoupper(preg_replace('/[\s-]+/', '', trim($licensePlate)) ?? '');

        return [
            'success' => false,
            'status' => 'not_configured',
            'normalized_plate' => $normalizedPlate,
            'message' => "L’API d’immatriculation n’est pas encore configurée.",
            'vehicle' => null,
        ];
    }
}
