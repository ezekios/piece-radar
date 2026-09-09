<?php

namespace App\Http\Controllers;

use App\Models\SavedPartSearch;
use App\Models\Scrapyard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ScrapyardCorrespondenceController extends Controller
{
    public function index(Request $request): View
    {
        $scrapyard = $this->scrapyard($request);

        $correspondences = SavedPartSearch::query()
            ->with(['matchedPart.images', 'matchedPart.vehicle'])
            ->where('status', 'matched')
            ->whereNotNull('matched_part_id')
            ->whereHas('matchedPart.vehicle', function ($query) use ($scrapyard) {
                $query->where('scrapyard_id', $scrapyard->id);
            })
            ->latest('matched_at')
            ->latest()
            ->get();

        return view('scrapyard.correspondences.index', [
            'correspondences' => $correspondences,
            'scrapyard' => $scrapyard,
        ]);
    }

    private function scrapyard(Request $request): Scrapyard
    {
        $scrapyard = $request->user()?->scrapyard;

        abort_unless($scrapyard, 403);

        return $scrapyard;
    }
}
