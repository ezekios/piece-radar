<?php

namespace App\Http\Controllers;

use App\Models\PartHoldRequest;
use App\Models\Scrapyard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ScrapyardRequestController extends Controller
{
    public function index(): View
    {
        $scrapyard = Scrapyard::query()->first();

        $requests = collect();
        $pendingRequestsCount = 0;

        if ($scrapyard) {
            $requests = PartHoldRequest::query()
                ->with(['user', 'part.vehicle.scrapyard'])
                ->whereHas('part.vehicle', function ($query) use ($scrapyard) {
                    $query->where('scrapyard_id', $scrapyard->id);
                })
                ->latest()
                ->get();

            $pendingRequestsCount = $requests
                ->where('status', 'pending')
                ->count();
        }

        return view('scrapyard.requests.index', [
            'scrapyard' => $scrapyard,
            'requests' => $requests,
            'pendingRequestsCount' => $pendingRequestsCount,
        ]);
    }

    public function show(PartHoldRequest $partHoldRequest): View
    {
        $scrapyard = $this->ensureRequestBelongsToFirstScrapyard($partHoldRequest);
        $partHoldRequest->load(['user', 'part.vehicle.scrapyard']);

        return view('scrapyard.requests.show', [
            'scrapyard' => $scrapyard,
            'partHoldRequest' => $partHoldRequest,
        ]);
    }

    public function accept(PartHoldRequest $partHoldRequest): RedirectResponse
    {
        $this->ensureRequestBelongsToFirstScrapyard($partHoldRequest);

        if ($partHoldRequest->status !== 'pending') {
            return redirect()
                ->route('scrapyard.requests.show', $partHoldRequest)
                ->with('error', 'Cette demande ne peut plus être traitée.');
        }

        DB::transaction(function () use ($partHoldRequest): void {
            $handledAt = now();

            $partHoldRequest->update([
                'status' => 'accepted',
                'handled_at' => $handledAt,
                'reserved_until' => $handledAt->copy()->addHours(48),
            ]);

            $partHoldRequest->part->update([
                'status' => 'reserved',
            ]);

            PartHoldRequest::query()
                ->where('part_id', $partHoldRequest->part_id)
                ->whereKeyNot($partHoldRequest->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'refused',
                    'handled_at' => $handledAt,
                ]);
        });

        return redirect()
            ->route('scrapyard.requests.show', $partHoldRequest)
            ->with('success', 'La demande a été acceptée. La pièce est maintenant mise de côté.');
    }

    public function refuse(PartHoldRequest $partHoldRequest): RedirectResponse
    {
        $this->ensureRequestBelongsToFirstScrapyard($partHoldRequest);

        if ($partHoldRequest->status !== 'pending') {
            return redirect()
                ->route('scrapyard.requests.show', $partHoldRequest)
                ->with('error', 'Cette demande ne peut plus être traitée.');
        }

        $partHoldRequest->update([
            'status' => 'refused',
            'handled_at' => now(),
        ]);

        return redirect()
            ->route('scrapyard.requests.show', $partHoldRequest)
            ->with('success', 'La demande a été refusée.');
    }

    private function ensureRequestBelongsToFirstScrapyard(PartHoldRequest $partHoldRequest): Scrapyard
    {
        $scrapyard = Scrapyard::query()->first();

        abort_unless($scrapyard, 404);

        $partHoldRequest->loadMissing(['part.vehicle']);

        abort_unless(
            (int) ($partHoldRequest->part?->vehicle?->scrapyard_id) === (int) $scrapyard->id,
            404,
        );

        return $scrapyard;
    }
}
