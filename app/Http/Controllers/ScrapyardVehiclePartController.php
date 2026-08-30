<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\Scrapyard;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ScrapyardVehiclePartController extends Controller
{
    public function create(Vehicle $vehicle): View
    {
        $scrapyard = Scrapyard::query()->first();

        if ($scrapyard) {
            abort_unless((int) $vehicle->scrapyard_id === (int) $scrapyard->id, 404);
        }

        return view('scrapyard.vehicle-parts.create', [
            'scrapyard' => $scrapyard,
            'vehicle' => $vehicle,
        ]);
    }

    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $this->ensureVehicleBelongsToFirstScrapyard($vehicle);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'condition' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'oem_reference' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:preparing,available,reserved,sold,unavailable'],
        ]);

        Part::query()->create([
            ...$validated,
            'vehicle_id' => $vehicle->id,
            'condition' => $validated['condition'] ?? 'unknown',
            'status' => $validated['status'] ?? 'preparing',
            'is_published' => false,
        ]);

        return redirect()
            ->route('scrapyard.vehicles.show', $vehicle)
            ->with('success', 'La pièce a été ajoutée au véhicule.');
    }

    private function ensureVehicleBelongsToFirstScrapyard(Vehicle $vehicle): Scrapyard
    {
        $scrapyard = Scrapyard::query()->first();

        abort_unless($scrapyard, 404);

        abort_unless((int) $vehicle->scrapyard_id === (int) $scrapyard->id, 404);

        return $scrapyard;
    }
}
