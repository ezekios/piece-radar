<?php

namespace App\Http\Controllers;

use App\Models\SavedPartSearch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientSavedPartSearchController extends Controller
{
    public function index(Request $request): View
    {
        $savedSearches = $request->user()
            ->savedPartSearches()
            ->with(['matchedPart.images', 'matchedPart.vehicle.scrapyard'])
            ->latest()
            ->get();

        return view('client.saved-searches.index', [
            'savedSearches' => $savedSearches,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'part_name' => ['required', 'string', 'max:255'],
            'part_category' => ['nullable', 'string', 'max:255'],
            'vehicle_brand' => ['required', 'string', 'max:255'],
            'vehicle_model' => ['required', 'string', 'max:255'],
            'vehicle_year' => ['nullable', 'integer', 'min:1900', 'max:' . (now()->year + 1)],
        ]);

        SavedPartSearch::query()->create([
            'user_id' => $request->user()->id,
            'vehicle_brand' => $this->clean($validated['vehicle_brand']),
            'vehicle_model' => $this->clean($validated['vehicle_model']),
            'vehicle_year' => $validated['vehicle_year'] ?? null,
            'part_name' => $this->clean($validated['part_name']),
            'part_category' => $this->cleanNullable($validated['part_category'] ?? null),
            'status' => 'active',
        ]);

        return redirect()
            ->route('client.saved-searches.index')
            ->with('success', 'Votre recherche a été enregistrée. Elle sera suivie automatiquement.');
    }

    private function clean(string $value): string
    {
        return Str::squish($value);
    }

    private function cleanNullable(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = Str::squish($value);

        return $value === '' ? null : $value;
    }
}
