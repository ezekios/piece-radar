<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\Scrapyard;
use Illuminate\Contracts\View\View;
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
}
