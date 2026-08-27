<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientPartController extends Controller
{
    public function index(): View
    {
        $parts = Part::query()
            ->with(['vehicle.scrapyard'])
            ->where('status', 'available')
            ->where('is_published', true)
            ->latest()
            ->get();

        return view('client.parts.index', [
            'parts' => $parts,
        ]);
    }

    public function show(Part $part): View
    {
        abort_unless($part->is_published && $part->status === 'available', 404);

        $part->load(['vehicle.scrapyard']);

        return view('client.parts.show', [
            'part' => $part,
        ]);
    }

    public function requestForm(Part $part): View
    {
        abort_unless($part->is_published && $part->status === 'available', 404);

        $part->load(['vehicle.scrapyard']);

        return view('client.parts.request', [
            'part' => $part,
        ]);
    }

    public function storeRequest(Request $request, Part $part): RedirectResponse
    {
        abort_unless($part->is_published && $part->status === 'available', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'customer_message' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = User::query()->firstOrNew([
            'email' => $validated['email'],
        ]);

        if ($user->exists && $user->role !== 'client') {
            return back()
                ->withErrors(['email' => 'Cette adresse email ne peut pas être utilisée pour une demande client.'])
                ->withInput();
        }

        if (! $user->exists) {
            $user->fill([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'password' => Hash::make(Str::random(32)),
            ]);
            $user->role = 'client';
            $user->save();
        }

        session(['client_email' => $user->email]);

        PartHoldRequest::query()->create([
            'user_id' => $user->id,
            'part_id' => $part->id,
            'status' => 'pending',
            'customer_message' => $validated['customer_message'] ?? null,
        ]);

        return redirect()
            ->route('pieces.show', $part)
            ->with('success', 'Votre demande de mise de côté a bien été envoyée à la casse.');
    }
}
