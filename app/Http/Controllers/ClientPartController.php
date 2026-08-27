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
    public function index(Request $request): View
    {
        $parts = Part::query()
            ->with(['vehicle.scrapyard'])
            ->where('status', 'available')
            ->where('is_published', true)
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%' . (string) $request->string('q')->trim() . '%';

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', $search)
                        ->orWhere('reference', 'like', $search)
                        ->orWhere('oem_reference', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category', (string) $request->string('category')->trim());
            })
            ->when($request->filled('brand'), function ($query) use ($request) {
                $brand = '%' . (string) $request->string('brand')->trim() . '%';

                $query->whereHas('vehicle', function ($query) use ($brand) {
                    $query->where('brand', 'like', $brand);
                });
            })
            ->when($request->filled('model'), function ($query) use ($request) {
                $model = '%' . (string) $request->string('model')->trim() . '%';

                $query->whereHas('vehicle', function ($query) use ($model) {
                    $query->where('model', 'like', $model);
                });
            })
            ->when($request->filled('city'), function ($query) use ($request) {
                $city = '%' . (string) $request->string('city')->trim() . '%';

                $query->whereHas('vehicle.scrapyard', function ($query) use ($city) {
                    $query->where('city', 'like', $city);
                });
            })
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
