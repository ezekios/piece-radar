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
}
