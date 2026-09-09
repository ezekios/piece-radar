<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($this->homePathFor($request->user()->role));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Un nouveau lien de vérification a été envoyé.');
    }

    private function homePathFor(?string $role): string
    {
        $user = request()->user();

        return match ($role) {
            'professional' => route('professional.account.show'),
            'scrapyard' => $user?->scrapyard?->is_active
                ? route('scrapyard.dashboard')
                : route('scrapyard.pending'),
            default => route('client.requests.index'),
        };
    }
}
