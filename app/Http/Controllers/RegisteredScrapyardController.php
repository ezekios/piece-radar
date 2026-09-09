<?php

namespace App\Http\Controllers;

use App\Models\Scrapyard;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisteredScrapyardController extends Controller
{
    public function create(): View
    {
        return view('auth.register-scrapyard');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'scrapyard_name' => ['required', 'string', 'max:255'],
            'siret' => ['required', 'string', 'regex:/^\d{14}$/', 'unique:scrapyards,siret'],
            'scrapyard_email' => ['nullable', 'email', 'max:255'],
            'scrapyard_phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $user = new User([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => $validated['password'],
            ]);
            $user->forceFill(['role' => 'scrapyard'])->save();

            Scrapyard::query()->create([
                'user_id' => $user->id,
                'name' => $validated['scrapyard_name'],
                'slug' => $this->uniqueScrapyardSlug($validated['scrapyard_name']),
                'siret' => $validated['siret'],
                'email' => $validated['scrapyard_email'] ?? null,
                'phone' => $validated['scrapyard_phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'city' => $validated['city'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_active' => false,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice');
    }

    private function uniqueScrapyardSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'casse';
        $slug = $baseSlug;
        $counter = 2;

        while (Scrapyard::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
