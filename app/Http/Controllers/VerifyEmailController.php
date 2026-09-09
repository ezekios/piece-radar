<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()
            ->intended($this->homePathFor($request->user()->role))
            ->with('status', 'Votre adresse email a été vérifiée.');
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
