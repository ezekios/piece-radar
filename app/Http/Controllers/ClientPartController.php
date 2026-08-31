<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Services\LicensePlateLookupService;
use App\Services\PartHoldRequestNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientPartController extends Controller
{
    public function index(Request $request, LicensePlateLookupService $licensePlateLookupService): View
    {
        $licensePlate = (string) $request->string('license_plate')->trim();
        $licensePlateLookup = $licensePlate !== ''
            ? $licensePlateLookupService->lookup($licensePlate)
            : null;

        $parts = Part::query()
            ->with(['images', 'vehicle.scrapyard'])
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
            'hasLicensePlate' => $licensePlateLookup !== null,
            'licensePlateLookup' => $licensePlateLookup,
            'parts' => $parts,
        ]);
    }

    public function show(Part $part): View
    {
        abort_unless($part->is_published && $part->status === 'available', 404);

        $part->load(['images', 'vehicle.scrapyard']);

        return view('client.parts.show', [
            'part' => $part,
        ]);
    }

    public function requestForm(Request $request, Part $part): View
    {
        abort_unless($part->is_published && $part->status === 'available', 404);

        $part->load(['images', 'vehicle.scrapyard']);

        return view('client.parts.request', [
            'client' => $request->user(),
            'part' => $part,
        ]);
    }

    public function storeRequest(Request $request, Part $part, PartHoldRequestNotifier $notifier): RedirectResponse
    {
        abort_unless($part->is_published && $part->status === 'available', 404);

        $validated = $request->validate([
            'customer_message' => ['nullable', 'string', 'max:1000'],
        ]);

        $partHoldRequest = PartHoldRequest::query()->create([
            'user_id' => $request->user()->id,
            'part_id' => $part->id,
            'status' => 'pending',
            'customer_message' => $validated['customer_message'] ?? null,
        ]);

        $notifier->newRequest($partHoldRequest);

        return redirect()
            ->route('pieces.show', $part)
            ->with('success', 'Votre demande de mise de côté a bien été envoyée à la casse.');
    }
}
