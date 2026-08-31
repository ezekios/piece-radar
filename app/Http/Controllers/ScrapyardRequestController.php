<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\Scrapyard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScrapyardRequestController extends Controller
{
    public function index(Request $request): View
    {
        $scrapyard = $this->scrapyard($request);
        $pendingRequestsCount = 0;
        $allowedStatuses = ['pending', 'accepted', 'refused', 'cancelled', 'completed', 'expired'];
        $activeStatus = in_array($request->string('status')->toString(), $allowedStatuses, true)
            ? $request->string('status')->toString()
            : null;

        $requestsQuery = PartHoldRequest::query()
            ->with(['user', 'part.vehicle.scrapyard'])
            ->whereHas('part.vehicle', function ($query) use ($scrapyard) {
                $query->where('scrapyard_id', $scrapyard->id);
            });

        $pendingRequestsCount = (clone $requestsQuery)
            ->where('status', 'pending')
            ->count();

        $requests = $requestsQuery
            ->when($activeStatus, function ($query) use ($activeStatus) {
                $query->where('status', $activeStatus);
            })
            ->latest()
            ->get();

        return view('scrapyard.requests.index', [
            'activeStatus' => $activeStatus,
            'scrapyard' => $scrapyard,
            'requests' => $requests,
            'pendingRequestsCount' => $pendingRequestsCount,
        ]);
    }

    public function show(Request $request, PartHoldRequest $partHoldRequest): View
    {
        $scrapyard = $this->scrapyard($request);
        $this->ensureRequestBelongsToScrapyard($partHoldRequest, $scrapyard);
        $partHoldRequest->load(['user', 'part.vehicle.scrapyard']);

        return view('scrapyard.requests.show', [
            'scrapyard' => $scrapyard,
            'partHoldRequest' => $partHoldRequest,
        ]);
    }

    public function confirmAccept(Request $request, PartHoldRequest $partHoldRequest): View|RedirectResponse
    {
        $scrapyard = $this->scrapyard($request);
        $this->ensureRequestBelongsToScrapyard($partHoldRequest, $scrapyard);

        if ($partHoldRequest->status !== 'pending') {
            return redirect()
                ->route('scrapyard.requests.show', $partHoldRequest)
                ->with('error', 'Cette demande ne peut plus être acceptée.');
        }

        $partHoldRequest->load(['part.vehicle.scrapyard']);

        return view('scrapyard.requests.confirm-accept', [
            'scrapyard' => $scrapyard,
            'partHoldRequest' => $partHoldRequest,
        ]);
    }

    public function accept(Request $request, PartHoldRequest $partHoldRequest): RedirectResponse
    {
        $scrapyard = $this->scrapyard($request);

        $result = DB::transaction(function () use ($partHoldRequest, $scrapyard): array {
            $lockedPart = Part::query()
                ->with('vehicle')
                ->whereKey($partHoldRequest->part_id)
                ->lockForUpdate()
                ->first();

            abort_unless($lockedPart, 404);

            $lockedRequest = PartHoldRequest::query()
                ->whereKey($partHoldRequest->id)
                ->lockForUpdate()
                ->first();

            abort_unless($lockedRequest, 404);
            abort_unless((int) $lockedRequest->part_id === (int) $lockedPart->id, 404);
            abort_unless((int) ($lockedPart->vehicle?->scrapyard_id) === (int) $scrapyard->id, 404);

            if ($lockedRequest->status !== 'pending') {
                return [
                    'accepted' => false,
                    'message' => 'Cette demande ne peut plus être traitée.',
                ];
            }

            if ($lockedPart->status !== 'available') {
                return [
                    'accepted' => false,
                    'message' => 'La pièce n’est plus disponible pour une mise de côté.',
                ];
            }

            $acceptedRequestExists = PartHoldRequest::query()
                ->where('part_id', $lockedPart->id)
                ->whereKeyNot($lockedRequest->id)
                ->where('status', 'accepted')
                ->exists();

            if ($acceptedRequestExists) {
                return [
                    'accepted' => false,
                    'message' => 'Une autre demande a déjà été acceptée pour cette pièce.',
                ];
            }

            $handledAt = now();

            $lockedRequest->update([
                'status' => 'accepted',
                'handled_at' => $handledAt,
                'reserved_until' => $handledAt->copy()->addHours(48),
            ]);

            $lockedPart->update([
                'status' => 'reserved',
            ]);

            PartHoldRequest::query()
                ->where('part_id', $lockedPart->id)
                ->whereKeyNot($lockedRequest->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'refused',
                    'handled_at' => $handledAt,
                ]);

            return ['accepted' => true];
        });

        if (! $result['accepted']) {
            return redirect()
                ->route('scrapyard.requests.show', $partHoldRequest)
                ->with('error', $result['message']);
        }

        return redirect()
            ->route('scrapyard.requests.show', $partHoldRequest)
            ->with('success', 'La demande a été acceptée. La pièce est maintenant mise de côté.');
    }

    public function refuse(Request $request, PartHoldRequest $partHoldRequest): RedirectResponse
    {
        $scrapyard = $this->scrapyard($request);
        $this->ensureRequestBelongsToScrapyard($partHoldRequest, $scrapyard);

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

    public function complete(Request $request, PartHoldRequest $partHoldRequest): RedirectResponse
    {
        $scrapyard = $this->scrapyard($request);
        $this->ensureRequestBelongsToScrapyard($partHoldRequest, $scrapyard);

        if ($partHoldRequest->status !== 'accepted') {
            return redirect()
                ->route('scrapyard.requests.show', $partHoldRequest)
                ->with('error', 'Cette demande ne peut plus être modifiée.');
        }

        DB::transaction(function () use ($partHoldRequest): void {
            $partHoldRequest->update([
                'status' => 'completed',
                'handled_at' => now(),
                'reserved_until' => null,
            ]);

            $partHoldRequest->part->update([
                'status' => 'sold',
                'is_published' => false,
            ]);
        });

        return redirect()
            ->route('scrapyard.requests.show', $partHoldRequest)
            ->with('success', 'La demande a été terminée.');
    }

    public function cancel(Request $request, PartHoldRequest $partHoldRequest): RedirectResponse
    {
        $scrapyard = $this->scrapyard($request);
        $this->ensureRequestBelongsToScrapyard($partHoldRequest, $scrapyard);

        if ($partHoldRequest->status !== 'accepted') {
            return redirect()
                ->route('scrapyard.requests.show', $partHoldRequest)
                ->with('error', 'Cette demande ne peut plus être modifiée.');
        }

        DB::transaction(function () use ($partHoldRequest): void {
            $partHoldRequest->update([
                'status' => 'cancelled',
                'handled_at' => now(),
                'reserved_until' => null,
            ]);

            $partHoldRequest->part->update([
                'status' => 'available',
            ]);
        });

        return redirect()
            ->route('scrapyard.requests.show', $partHoldRequest)
            ->with('success', 'La mise de côté a été annulée.');
    }

    private function scrapyard(Request $request): Scrapyard
    {
        $scrapyard = $request->user()?->scrapyard;

        abort_unless($scrapyard, 403);

        return $scrapyard;
    }

    private function ensureRequestBelongsToScrapyard(PartHoldRequest $partHoldRequest, Scrapyard $scrapyard): void
    {
        $partHoldRequest->loadMissing(['part.vehicle']);

        abort_unless(
            (int) ($partHoldRequest->part?->vehicle?->scrapyard_id) === (int) $scrapyard->id,
            404,
        );
    }
}
