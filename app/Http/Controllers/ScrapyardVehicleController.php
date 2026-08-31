<?php

namespace App\Http\Controllers;

use App\Models\Scrapyard;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ScrapyardVehicleController extends Controller
{
    public function index(Request $request): View
    {
        $scrapyard = $this->scrapyard($request);
        $vehicles = Vehicle::query()
            ->with('images')
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

        return view('scrapyard.vehicles.index', [
            'scrapyard' => $scrapyard,
            'vehicles' => $vehicles,
        ]);
    }

    public function create(Request $request): View
    {
        $scrapyard = $this->scrapyard($request);

        return view('scrapyard.vehicles.create', [
            'scrapyard' => $scrapyard,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $scrapyard = $this->scrapyard($request);

        $validated = $request->validate([
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . ((int) date('Y') + 1)],
            'license_plate' => ['nullable', 'string', 'max:255'],
            'fuel' => ['nullable', 'string', 'max:255'],
            'engine' => ['nullable', 'string', 'max:255'],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if (! empty($validated['license_plate'])) {
            $validated['license_plate'] = strtoupper(preg_replace('/[\s-]+/', '', $validated['license_plate']) ?? '');
        }

        $photos = $request->file('photos', []);
        unset($validated['photos']);

        $vehicle = DB::transaction(function () use ($validated, $scrapyard, $photos): Vehicle {
            $vehicle = Vehicle::query()->create([
                ...$validated,
                'scrapyard_id' => $scrapyard->id,
            ]);

            $this->storeVehicleImages($vehicle, $photos);

            return $vehicle;
        });

        return redirect()
            ->route('scrapyard.vehicles.show', $vehicle)
            ->with('success', 'Le véhicule a été ajouté.');
    }

    public function show(Request $request, Vehicle $vehicle): View
    {
        $scrapyard = $this->scrapyard($request);
        $this->ensureVehicleBelongsToScrapyard($vehicle, $scrapyard);

        $vehicle->load([
            'images',
            'parts' => function ($query) {
                $query->with('images')->latest();
            },
        ]);

        return view('scrapyard.vehicles.show', [
            'scrapyard' => $scrapyard,
            'vehicle' => $vehicle,
        ]);
    }

    public function edit(Request $request, Vehicle $vehicle): View
    {
        $scrapyard = $this->scrapyard($request);
        $this->ensureVehicleBelongsToScrapyard($vehicle, $scrapyard);
        $vehicle->load('images');

        return view('scrapyard.vehicles.edit', [
            'scrapyard' => $scrapyard,
            'vehicle' => $vehicle,
        ]);
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $scrapyard = $this->scrapyard($request);
        $this->ensureVehicleBelongsToScrapyard($vehicle, $scrapyard);
        $remainingPhotoSlots = max(0, 5 - $vehicle->images()->count());

        $validated = $request->validate([
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . ((int) date('Y') + 1)],
            'license_plate' => ['nullable', 'string', 'max:255'],
            'fuel' => ['nullable', 'string', 'max:255'],
            'engine' => ['nullable', 'string', 'max:255'],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'photos' => ['nullable', 'array', 'max:' . $remainingPhotoSlots],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if (! empty($validated['license_plate'])) {
            $validated['license_plate'] = strtoupper(preg_replace('/[\s-]+/', '', $validated['license_plate']) ?? '');
        }

        $photos = $request->file('photos', []);
        unset($validated['photos']);

        DB::transaction(function () use ($vehicle, $validated, $photos): void {
            $vehicle->update($validated);
            $this->storeVehicleImages($vehicle, $photos);
        });

        return redirect()
            ->route('scrapyard.vehicles.show', $vehicle)
            ->with('success', 'Le véhicule a été mis à jour.');
    }

    public function destroyImage(Request $request, Vehicle $vehicle, VehicleImage $image): RedirectResponse
    {
        $scrapyard = $this->scrapyard($request);
        $this->ensureVehicleBelongsToScrapyard($vehicle, $scrapyard);

        abort_unless((int) $image->vehicle_id === (int) $vehicle->id, 404);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        $this->reorderVehicleImages($vehicle);

        return back()->with('success', 'La photo du véhicule a été supprimée.');
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
    private function storeVehicleImages(Vehicle $vehicle, array $photos): void
    {
        $storedPaths = [];

        try {
            $position = ((int) $vehicle->images()->max('position')) + 1;

            foreach ($photos as $photo) {
                $path = $photo->store('vehicle-images', 'public');
                $storedPaths[] = $path;

                $vehicle->images()->create([
                    'path' => $path,
                    'position' => $position++,
                ]);
            }
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);

            throw $exception;
        }
    }

    private function reorderVehicleImages(Vehicle $vehicle): void
    {
        $vehicle->images()
            ->orderBy('position')
            ->get()
            ->values()
            ->each(function (VehicleImage $image, int $index): void {
                $image->update(['position' => $index + 1]);
            });
    }
}
