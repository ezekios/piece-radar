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
        $scrapyard = $this->scrapyard($request);
        $allowedStatuses = ['available', 'reserved', 'sold', 'unavailable', 'preparing'];
        $allowedPublications = ['published', 'unpublished'];
        $activePublication = in_array($request->string('publication')->toString(), $allowedPublications, true)
            ? $request->string('publication')->toString()
            : null;

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
            ->when($activePublication === 'published', function ($query) {
                $query->where('is_published', true);
            })
            ->when($activePublication === 'unpublished', function ($query) {
                $query->where('is_published', false);
            })
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

        return view('scrapyard.parts.index', [
            'activePublication' => $activePublication,
            'parts' => $parts,
            'scrapyard' => $scrapyard,
        ]);
    }

    public function show(Request $request, Part $part): View
    {
        $scrapyard = $this->scrapyard($request);

        $part->load(['vehicle.scrapyard']);
        $this->ensurePartBelongsToScrapyard($part, $scrapyard);

        return view('scrapyard.parts.show', [
            'part' => $part,
            'scrapyard' => $scrapyard,
        ]);
    }

    public function preparation(Request $request, Part $part): View
    {
        $scrapyard = $this->scrapyard($request);

        $part->load(['vehicle.scrapyard']);
        $this->ensurePartBelongsToScrapyard($part, $scrapyard);

        return view('scrapyard.parts.preparation', [
            'part' => $part,
            'scrapyard' => $scrapyard,
        ]);
    }

    public function updatePreparation(Request $request, Part $part): RedirectResponse
    {
        $scrapyard = $this->scrapyard($request);

        $part->load('vehicle');
        $this->ensurePartBelongsToScrapyard($part, $scrapyard);

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

        foreach (['condition', 'status'] as $requiredEnumField) {
            if (($validated[$requiredEnumField] ?? null) === null) {
                unset($validated[$requiredEnumField]);
            }
        }

        $part->fill($validated);
        $part->save();

        return redirect()
            ->route('scrapyard.parts.show', $part)
            ->with('success', 'Les informations de préparation de la pièce ont été mises à jour.');
    }

    public function updateStatus(Request $request, Part $part): RedirectResponse
    {
        $scrapyard = $this->scrapyard($request);

        $part->load('vehicle');
        $this->ensurePartBelongsToScrapyard($part, $scrapyard);

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

    public function publish(Request $request, Part $part): RedirectResponse
    {
        $scrapyard = $this->scrapyard($request);

        $part->load('vehicle');
        $this->ensurePartBelongsToScrapyard($part, $scrapyard);

        $part->status = 'available';
        $part->is_published = true;
        $part->save();

        return redirect()
            ->route('scrapyard.parts.show', $part)
            ->with('success', 'La pièce a été publiée et est maintenant disponible côté client.');
    }

    public function unpublish(Request $request, Part $part): RedirectResponse
    {
        $scrapyard = $this->scrapyard($request);

        $part->load('vehicle');
        $this->ensurePartBelongsToScrapyard($part, $scrapyard);

        $part->is_published = false;
        $part->save();

        return redirect()
            ->route('scrapyard.parts.show', $part)
            ->with('success', 'La pièce a été retirée de la publication côté client.');
    }

    private function scrapyard(Request $request): Scrapyard
    {
        $scrapyard = $request->user()?->scrapyard;

        abort_unless($scrapyard, 403);

        return $scrapyard;
    }

    private function ensurePartBelongsToScrapyard(Part $part, Scrapyard $scrapyard): void
    {
        $part->loadMissing('vehicle');

        abort_unless((int) ($part->vehicle?->scrapyard_id) === (int) $scrapyard->id, 404);
    }
}
