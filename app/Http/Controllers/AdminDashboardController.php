<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\SavedPartSearch;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'users_total' => User::query()->count(),
                'clients_total' => User::query()->where('role', 'client')->count(),
                'professionals_total' => User::query()->where('role', 'professional')->count(),
                'scrapyards_total' => Scrapyard::query()->count(),
                'vehicles_total' => Vehicle::query()->count(),
                'parts_total' => Part::query()->count(),
                'requests_total' => PartHoldRequest::query()->count(),
                'saved_searches_total' => SavedPartSearch::query()->count(),
            ],
        ]);
    }
}
