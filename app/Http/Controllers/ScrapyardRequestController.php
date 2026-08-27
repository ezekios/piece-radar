<?php

namespace App\Http\Controllers;

use App\Models\PartHoldRequest;
use App\Models\Scrapyard;
use Illuminate\Contracts\View\View;

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
}
