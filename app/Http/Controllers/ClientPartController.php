<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Contracts\View\View;

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
}
