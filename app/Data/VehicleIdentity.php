<?php

namespace App\Data;

class VehicleIdentity
{
    public function __construct(
        public readonly ?string $brand = null,
        public readonly ?string $model = null,
        public readonly ?int $year = null,
        public readonly ?string $version = null,
        public readonly ?string $engine = null,
        public readonly ?string $fuel = null,
        public readonly ?string $engineCode = null,
        public readonly ?string $kType = null,
        public readonly ?string $vin = null,
        public readonly ?string $typeMine = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            brand: self::stringOrNull($data['brand'] ?? null),
            model: self::stringOrNull($data['model'] ?? null),
            year: is_numeric($data['year'] ?? null) ? (int) $data['year'] : null,
            version: self::stringOrNull($data['version'] ?? null),
            engine: self::stringOrNull($data['engine'] ?? null),
            fuel: self::stringOrNull($data['fuel'] ?? null),
            engineCode: self::stringOrNull($data['engineCode'] ?? null),
            kType: self::stringOrNull($data['kType'] ?? null),
            vin: self::stringOrNull($data['vin'] ?? null),
            typeMine: self::stringOrNull($data['typeMine'] ?? null),
        );
    }

    /**
     * @return array{
     *     brand: ?string,
     *     model: ?string,
     *     year: ?int,
     *     version: ?string,
     *     engine: ?string,
     *     fuel: ?string,
     *     engineCode: ?string,
     *     kType: ?string,
     *     vin: ?string,
     *     typeMine: ?string
     * }
     */
    public function toArray(): array
    {
        return [
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year,
            'version' => $this->version,
            'engine' => $this->engine,
            'fuel' => $this->fuel,
            'engineCode' => $this->engineCode,
            'kType' => $this->kType,
            'vin' => $this->vin,
            'typeMine' => $this->typeMine,
        ];
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
