<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($this->homePathFor($request->user()->role));
        }

        return view('auth.verify-email');
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
