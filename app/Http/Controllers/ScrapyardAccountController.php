<?php

namespace App\Http\Controllers;

use App\Models\Scrapyard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ScrapyardAccountController extends Controller
{
    public function show(Request $request): View
    {
        return view('scrapyard.account.show', [
            'user' => $request->user(),
            'scrapyard' => $this->scrapyardFor($request),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $request->user()->forceFill([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
        ])->save();

        return redirect()
            ->route('scrapyard.account.show')
            ->with('success', 'Votre compte professionnel a été mis à jour.');
    }

    public function updateScrapyard(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->scrapyardFor($request)->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'city' => $validated['city'] ?? null,
            'description' => $validated['description'] ?? null,
        ])->save();

        return redirect()
            ->route('scrapyard.account.show')
            ->with('success', 'Les informations de la casse ont été mises à jour.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Le mot de passe actuel est incorrect.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        $request->session()->regenerate();

        return redirect()
            ->route('scrapyard.account.show')
            ->with('success', 'Votre mot de passe a été mis à jour.');
    }

    private function scrapyardFor(Request $request): Scrapyard
    {
        $scrapyard = $request->user()->scrapyard()->first();

        abort_unless($scrapyard, 403);

        return $scrapyard;
    }
}
