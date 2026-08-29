<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\Scrapyard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ScrapyardPartController extends Controller
{
    public function index(Request $request): View
    {
        $scrapyard = Scrapyard::query()->first();
        $parts = collect();
        $allowedStatuses = ['available', 'reserved', 'sold', 'unavailable', 'preparing'];

        if ($scrapyard) {
            $parts = Part::query()
                ->with(['vehicle.scrapyard'])
                ->whereHas('vehicle', function ($query) use ($scrapyard) {
                    $query->where('scrapyard_id', $scrapyard->id);
                })
                ->when(
                    $request->filled('status') && in_array($request->string('status')->toString(), $allowedStatuses, true),
                    function ($query) use ($request) {
                        $query->where('status', $request->string('status')->toString());
                    },
                )
                ->when($request->filled('q'), function ($query) use ($request) {
                    $search = '%' . (string) $request->string('q')->trim() . '%';

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('name', 'like', $search)
                            ->orWhere('reference', 'like', $search)
                            ->orWhere('oem_reference', 'like', $search)
                            ->orWhere('description', 'like', $search)
                            ->orWhereHas('vehicle', function ($query) use ($search) {
                                $query
                                    ->where('brand', 'like', $search)
                                    ->orWhere('model', 'like', $search);
                            });
                    });
                })
                ->latest()
                ->get();
        }

        return view('scrapyard.parts.index', [
            'parts' => $parts,
            'scrapyard' => $scrapyard,
        ]);
    }

    public function show(Part $part): View
    {
        $scrapyard = Scrapyard::query()->first();

        abort_unless($scrapyard, 404);

        $part->load(['vehicle.scrapyard']);

        abort_unless(
            (int) ($part->vehicle?->scrapyard_id) === (int) $scrapyard->id,
            404,
        );

        return view('scrapyard.parts.show', [
            'part' => $part,
            'scrapyard' => $scrapyard,
        ]);
    }

    public function updateStatus(Request $request, Part $part): RedirectResponse
    {
        $scrapyard = Scrapyard::query()->first();

        abort_unless($scrapyard, 404);

        $part->load('vehicle');

        abort_unless(
            (int) ($part->vehicle?->scrapyard_id) === (int) $scrapyard->id,
            404,
        );

        $validated = $request->validate([
            'status' => ['required', 'in:available,preparing,reserved,sold,unavailable'],
        ]);

        $part->status = $validated['status'];

        if ($validated['status'] === 'available') {
            $part->is_published = true;
        }

        if (in_array($validated['status'], ['sold', 'unavailable'], true)) {
            $part->is_published = false;
        }

        $part->save();

        return redirect()
            ->route('scrapyard.parts.show', $part)
            ->with('success', 'Le statut de la pièce a été mis à jour.');
    }
}
