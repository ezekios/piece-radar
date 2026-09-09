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
        return $role === 'professional'
            ? route('professional.account.show')
            : route('client.requests.index');
    }
}
