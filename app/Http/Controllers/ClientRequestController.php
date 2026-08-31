<?php

namespace App\Http\Controllers;

use App\Models\PartHoldRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ClientRequestController extends Controller
{
    public function index(Request $request): View
    {
        $requests = $request->user()
            ->partHoldRequests()
            ->with(['part.vehicle.scrapyard'])
            ->latest()
            ->get();

        return view('client.requests.index', [
            'requests' => $requests,
        ]);
    }

    public function show(Request $request, PartHoldRequest $partHoldRequest): View
    {
        $partHoldRequest->load(['user', 'part.vehicle.scrapyard']);

        abort_unless((int) $partHoldRequest->user_id === (int) $request->user()->id, 404);

        return view('client.requests.show', [
            'partHoldRequest' => $partHoldRequest,
        ]);
    }
}
