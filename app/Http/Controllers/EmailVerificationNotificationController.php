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
        return $role === 'professional'
            ? route('professional.account.show')
            : route('client.requests.index');
    }
}
