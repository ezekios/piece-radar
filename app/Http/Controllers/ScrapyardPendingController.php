<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ScrapyardPendingController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()->scrapyard?->is_active) {
            return redirect()->route('scrapyard.dashboard');
        }

        return view('scrapyard.pending-validation', [
            'scrapyard' => $request->user()->scrapyard,
        ]);
    }
}
