<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $recentParts = Part::query()
            ->with(['images', 'vehicle.scrapyard'])
            ->where('status', 'available')
            ->where('is_published', true)
            ->latest()
            ->limit(4)
            ->get();

        return view('welcome', [
            'recentParts' => $recentParts,
        ]);
    }
}
