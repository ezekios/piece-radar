<?php

namespace App\Http\Controllers;

use App\Models\Scrapyard;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ScrapyardVehicleController extends Controller
{
    public function index(Request $request): View
    {
        $scrapyard = Scrapyard::query()->first();
        $vehicles = collect();

        if ($scrapyard) {
            $vehicles = Vehicle::query()
                ->withCount('parts')
                ->where('scrapyard_id', $scrapyard->id)
                ->when($request->filled('q'), function ($query) use ($request) {
                    $search = '%' . (string) $request->string('q')->trim() . '%';

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('brand', 'like', $search)
                            ->orWhere('model', 'like', $search)
                            ->orWhere('year', 'like', $search)
                            ->orWhere('license_plate', 'like', $search)
                            ->orWhere('fuel', 'like', $search)
                            ->orWhere('engine', 'like', $search);
                    });
                })
                ->latest()
                ->get();
        }

        return view('scrapyard.vehicles.index', [
            'scrapyard' => $scrapyard,
            'vehicles' => $vehicles,
        ]);
    }

    public function create(): View
    {
        $scrapyard = Scrapyard::query()->first();

        return view('scrapyard.vehicles.create', [
            'scrapyard' => $scrapyard,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $scrapyard = Scrapyard::query()->first();

        abort_unless($scrapyard, 404);

        $validated = $request->validate([
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . ((int) date('Y') + 1)],
            'license_plate' => ['nullable', 'string', 'max:255'],
            'fuel' => ['nullable', 'string', 'max:255'],
            'engine' => ['nullable', 'string', 'max:255'],
            'mileage' => ['nullable', 'integer', 'min:0'],
        ]);

        if (! empty($validated['license_plate'])) {
            $validated['license_plate'] = strtoupper(preg_replace('/[\s-]+/', '', $validated['license_plate']) ?? '');
        }

        $vehicle = Vehicle::query()->create([
            ...$validated,
            'scrapyard_id' => $scrapyard->id,
        ]);

        return redirect()
            ->route('scrapyard.vehicles.show', $vehicle)
            ->with('success', 'Le véhicule a été ajouté.');
    }

    public function show(Vehicle $vehicle): View
    {
        $scrapyard = Scrapyard::query()->first();

        abort_unless($scrapyard, 404);

        abort_unless((int) $vehicle->scrapyard_id === (int) $scrapyard->id, 404);

        $vehicle->load([
            'parts' => function ($query) {
                $query->latest();
            },
        ]);

        return view('scrapyard.vehicles.show', [
            'scrapyard' => $scrapyard,
            'vehicle' => $vehicle,
        ]);
    }

    public function edit(Vehicle $vehicle): View
    {
        $scrapyard = Scrapyard::query()->first();

        abort_unless($scrapyard, 404);

        abort_unless((int) $vehicle->scrapyard_id === (int) $scrapyard->id, 404);

        return view('scrapyard.vehicles.edit', [
            'scrapyard' => $scrapyard,
            'vehicle' => $vehicle,
        ]);
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $scrapyard = Scrapyard::query()->first();

        abort_unless($scrapyard, 404);

        abort_unless((int) $vehicle->scrapyard_id === (int) $scrapyard->id, 404);

        $validated = $request->validate([
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . ((int) date('Y') + 1)],
            'license_plate' => ['nullable', 'string', 'max:255'],
            'fuel' => ['nullable', 'string', 'max:255'],
            'engine' => ['nullable', 'string', 'max:255'],
            'mileage' => ['nullable', 'integer', 'min:0'],
        ]);

        if (! empty($validated['license_plate'])) {
            $validated['license_plate'] = strtoupper(preg_replace('/[\s-]+/', '', $validated['license_plate']) ?? '');
        }

        $vehicle->update($validated);

        return redirect()
            ->route('scrapyard.vehicles.show', $vehicle)
            ->with('success', 'Le véhicule a été mis à jour.');
    }
}
