<?php

namespace App\Http\Controllers;

use App\Models\Scrapyard;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
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
}
