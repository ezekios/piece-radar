<?php

namespace App\Services;

use App\Models\PartHoldRequest;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class ExpirePartHoldReservations
{
    public function handle(?CarbonInterface $now = null): int
    {
        $now ??= now();
        $expiredCount = 0;

        PartHoldRequest::query()
            ->where('status', 'accepted')
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<=', $now)
            ->pluck('id')
            ->each(function (int $requestId) use ($now, &$expiredCount): void {
                DB::transaction(function () use ($requestId, $now, &$expiredCount): void {
                    $partHoldRequest = PartHoldRequest::query()
                        ->with('part')
                        ->whereKey($requestId)
                        ->lockForUpdate()
                        ->first();

                    if (
                        ! $partHoldRequest
                        || $partHoldRequest->status !== 'accepted'
                        || ! $partHoldRequest->reserved_until
                        || $partHoldRequest->reserved_until->isAfter($now)
                    ) {
                        return;
                    }

                    $partHoldRequest->update([
                        'status' => 'expired',
                        'handled_at' => $now,
                    ]);

                    $partHoldRequest->part?->update([
                        'status' => 'available',
                    ]);

                    $expiredCount++;
                });
            });

        return $expiredCount;
    }
}
