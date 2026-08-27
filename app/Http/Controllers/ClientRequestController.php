<?php

namespace App\Http\Controllers;

use App\Models\PartHoldRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;

class ClientRequestController extends Controller
{
    public function index(): View
    {
        $clientEmail = session('client_email');
        $requests = collect();

        if ($clientEmail) {
            $client = User::query()
                ->where('email', $clientEmail)
                ->first();

            if ($client) {
                $requests = $client->partHoldRequests()
                    ->with(['part.vehicle.scrapyard'])
                    ->latest()
                    ->get();
            }
        }

        return view('client.requests.index', [
            'clientEmail' => $clientEmail,
            'requests' => $requests,
        ]);
    }

    public function show(PartHoldRequest $partHoldRequest): View
    {
        $clientEmail = session('client_email');

        abort_unless($clientEmail, 404);

        $partHoldRequest->load(['user', 'part.vehicle.scrapyard']);

        abort_unless($partHoldRequest->user?->email === $clientEmail, 404);

        return view('client.requests.show', [
            'partHoldRequest' => $partHoldRequest,
        ]);
    }
}
