<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\Scrapyard;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ScrapyardVehiclePartController extends Controller
{
    public function create(Request $request, Vehicle $vehicle): View
    {
        $scrapyard = $this->scrapyard($request);
        $this->ensureVehicleBelongsToScrapyard($vehicle, $scrapyard);

        return view('scrapyard.vehicle-parts.create', [
            'scrapyard' => $scrapyard,
            'vehicle' => $vehicle,
        ]);
    }

    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $scrapyard = $this->scrapyard($request);
        $this->ensureVehicleBelongsToScrapyard($vehicle, $scrapyard);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'condition' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'oem_reference' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:preparing,available,reserved,sold,unavailable'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $photos = $request->file('photos', []);
        unset($validated['photos']);

        DB::transaction(function () use ($validated, $vehicle, $photos): void {
            $part = Part::query()->create([
                ...$validated,
                'vehicle_id' => $vehicle->id,
                'condition' => $validated['condition'] ?? 'unknown',
                'status' => $validated['status'] ?? 'preparing',
                'is_published' => false,
            ]);

            $this->storePartImages($part, $photos);
        });

        return redirect()
            ->route('scrapyard.vehicles.show', $vehicle)
            ->with('success', 'La pièce a été ajoutée au véhicule.');
    }

    private function scrapyard(Request $request): Scrapyard
    {
        $scrapyard = $request->user()?->scrapyard;

        abort_unless($scrapyard, 403);

        return $scrapyard;
    }

    private function ensureVehicleBelongsToScrapyard(Vehicle $vehicle, Scrapyard $scrapyard): void
    {
        abort_unless((int) $vehicle->scrapyard_id === (int) $scrapyard->id, 404);
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>  $photos
     */
    private function storePartImages(Part $part, array $photos): void
    {
        $storedPaths = [];

        try {
            $position = ((int) $part->images()->max('position')) + 1;

            foreach ($photos as $photo) {
                $path = $photo->store('part-images', 'public');
                $storedPaths[] = $path;

                $part->images()->create([
                    'path' => $path,
                    'position' => $position++,
                ]);
            }
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);

            throw $exception;
        }
    }
}
