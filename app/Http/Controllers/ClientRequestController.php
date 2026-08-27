<?php

namespace App\Http\Controllers;

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
}
