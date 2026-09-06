<?php

namespace App\Data;

class VehicleLookupResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $status,
        public readonly string $normalizedPlate,
        public readonly string $message,
        public readonly ?VehicleIdentity $vehicle = null,
    ) {
    }

    public static function success(string $normalizedPlate, VehicleIdentity $vehicle): self
    {
        return new self(
            success: true,
            status: 'identified',
            normalizedPlate: $normalizedPlate,
            message: 'Véhicule identifié. Les résultats sont ajustés avec les informations disponibles.',
            vehicle: $vehicle,
        );
    }

    public static function failure(string $status, string $normalizedPlate, string $message): self
    {
        return new self(
            success: false,
            status: $status,
            normalizedPlate: $normalizedPlate,
            message: $message,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $vehicle = is_array($data['vehicle'] ?? null)
            ? VehicleIdentity::fromArray($data['vehicle'])
            : null;

        return new self(
            success: (bool) ($data['success'] ?? false),
            status: (string) ($data['status'] ?? 'provider_unavailable'),
            normalizedPlate: (string) ($data['normalized_plate'] ?? ''),
            message: (string) ($data['message'] ?? self::genericUnavailableMessage()),
            vehicle: $vehicle,
        );
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
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'status' => $this->status,
            'normalized_plate' => $this->normalizedPlate,
            'message' => $this->message,
            'vehicle' => $this->vehicle?->toArray(),
        ];
    }

    public static function invalidFormatMessage(): string
    {
        return "Le format de la plaque n'est pas reconnu. Vous pouvez poursuivre avec la recherche manuelle.";
    }

    public static function notFoundMessage(): string
    {
        return "Nous n'avons pas pu identifier ce véhicule avec cette plaque. Vous pouvez poursuivre avec la recherche manuelle.";
    }

    public static function genericUnavailableMessage(): string
    {
        return "Le service d'identification du véhicule est temporairement indisponible. Vous pouvez poursuivre avec la recherche manuelle.";
    }
}
