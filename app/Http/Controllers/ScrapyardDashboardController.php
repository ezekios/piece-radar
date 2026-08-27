<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\Scrapyard;
use Illuminate\Contracts\View\View;

class ScrapyardDashboardController extends Controller
{
    public function index(): View
    {
        $scrapyard = Scrapyard::query()->first();
        $latestRequests = collect();
        $stats = [
            'vehicles_total' => 0,
            'parts_total' => 0,
            'available_parts' => 0,
            'reserved_parts' => 0,
            'requests_total' => 0,
            'pending_requests' => 0,
            'accepted_requests' => 0,
            'refused_requests' => 0,
        ];

        if ($scrapyard) {
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
                'requests_total' => (clone $requestsQuery)->count(),
                'pending_requests' => (clone $requestsQuery)->where('status', 'pending')->count(),
                'accepted_requests' => (clone $requestsQuery)->where('status', 'accepted')->count(),
                'refused_requests' => (clone $requestsQuery)->where('status', 'refused')->count(),
            ];

            $latestRequests = (clone $requestsQuery)
                ->with(['user', 'part.vehicle'])
                ->latest()
                ->limit(5)
                ->get();
        }

        return view('scrapyard.dashboard', [
            'latestRequests' => $latestRequests,
            'scrapyard' => $scrapyard,
            'stats' => $stats,
        ]);
    }
}
