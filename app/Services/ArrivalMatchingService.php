<?php

namespace App\Services;

use App\Models\Part;
use App\Models\SavedPartSearch;
use Illuminate\Support\Str;

class ArrivalMatchingService
{
    public function matchPart(Part $part): int
    {
        $part->loadMissing('vehicle');

        if (! $part->is_published || $part->status !== 'available' || ! $part->vehicle) {
            return 0;
        }

        $matchedCount = 0;

        SavedPartSearch::query()
            ->where('status', 'active')
            ->whereNull('matched_part_id')
            ->orderBy('id')
            ->chunkById(100, function ($savedSearches) use ($part, &$matchedCount): void {
                foreach ($savedSearches as $savedSearch) {
                    if (! $this->isCompatible($savedSearch, $part)) {
                        continue;
                    }

                    $savedSearch->forceFill([
                        'status' => 'matched',
                        'matched_part_id' => $part->id,
                        'matched_at' => now(),
                    ])->save();

                    $matchedCount++;
                }
            });

        return $matchedCount;
    }

    private function isCompatible(SavedPartSearch $savedSearch, Part $part): bool
    {
        $vehicle = $part->vehicle;

        if (! $vehicle) {
            return false;
        }

        if ($this->normalize($savedSearch->vehicle_brand) !== $this->normalize($vehicle->brand)) {
            return false;
        }

        if ($this->normalize($savedSearch->vehicle_model) !== $this->normalize($vehicle->model)) {
            return false;
        }

        if (
            $savedSearch->vehicle_year !== null
            && (int) $savedSearch->vehicle_year !== (int) $vehicle->year
        ) {
            return false;
        }

        if (! $this->partNameMatches($savedSearch->part_name, $part->name)) {
            return false;
        }

        $savedCategory = $this->normalize($savedSearch->part_category);

        if ($savedCategory !== '' && $savedCategory !== $this->normalize($part->category)) {
            return false;
        }

        return true;
    }

    private function partNameMatches(string $savedPartName, string $partName): bool
    {
        $savedPartName = $this->normalize($savedPartName);
        $partName = $this->normalize($partName);

        return $savedPartName !== ''
            && $partName !== ''
            && (str_contains($partName, $savedPartName) || str_contains($savedPartName, $partName));
    }

    private function normalize(?string $value): string
    {
        $value = Str::ascii((string) $value);
        $value = Str::lower(trim($value));

        return preg_replace('/\s+/', ' ', $value) ?? '';
    }
}
