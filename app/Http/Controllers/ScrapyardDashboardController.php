<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\Scrapyard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ScrapyardDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $scrapyard = $this->scrapyard($request);
        $latestRequests = collect();
        $stats = [
            'vehicles_total' => 0,
            'parts_total' => 0,
            'available_parts' => 0,
            'reserved_parts' => 0,
            'preparingPartsCount' => 0,
            'publishedPartsCount' => 0,
            'unpublishedPartsCount' => 0,
            'requests_total' => 0,
            'pending_requests' => 0,
            'accepted_requests' => 0,
            'refused_requests' => 0,
            'cancelled_requests' => 0,
            'completed_requests' => 0,
            'expired_requests' => 0,
        ];

        $partsQuery = Part::query()
            ->whereHas('vehicle', function ($query) use ($scrapyard) {
                $query->where('scrapyard_id', $scrapyard->id);
            });

        $requestsQuery = PartHoldRequest::query()
            ->whereHas('part.vehicle', function ($query) use ($scrapyard) {
                $query->where('scrapyard_id', $scrapyard->id);
            });

        $stats = [
            'vehicles_total' => $scrapyard->vehicles()->count(),
            'parts_total' => (clone $partsQuery)->count(),
            'available_parts' => (clone $partsQuery)->where('status', 'available')->count(),
            'reserved_parts' => (clone $partsQuery)->where('status', 'reserved')->count(),
            'preparingPartsCount' => (clone $partsQuery)->where('status', 'preparing')->count(),
            'publishedPartsCount' => (clone $partsQuery)->where('is_published', true)->count(),
            'unpublishedPartsCount' => (clone $partsQuery)->where('is_published', false)->count(),
            'requests_total' => (clone $requestsQuery)->count(),
            'pending_requests' => (clone $requestsQuery)->where('status', 'pending')->count(),
            'accepted_requests' => (clone $requestsQuery)->where('status', 'accepted')->count(),
            'refused_requests' => (clone $requestsQuery)->where('status', 'refused')->count(),
            'cancelled_requests' => (clone $requestsQuery)->where('status', 'cancelled')->count(),
            'completed_requests' => (clone $requestsQuery)->where('status', 'completed')->count(),
            'expired_requests' => (clone $requestsQuery)->where('status', 'expired')->count(),
        ];

        $latestRequests = (clone $requestsQuery)
            ->with(['user', 'part.vehicle'])
            ->latest()
            ->limit(5)
            ->get();

        return view('scrapyard.dashboard', [
            'latestRequests' => $latestRequests,
            'scrapyard' => $scrapyard,
            'stats' => $stats,
        ]);
    }

    private function scrapyard(Request $request): Scrapyard
    {
        $scrapyard = $request->user()?->scrapyard;

        abort_unless($scrapyard, 403);

        return $scrapyard;
    }
}
