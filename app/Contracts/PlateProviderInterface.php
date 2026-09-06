<?php

namespace App\Contracts;

use App\Data\VehicleLookupResult;

interface PlateProviderInterface
{
    public function lookup(string $normalizedPlate): VehicleLookupResult;
}
