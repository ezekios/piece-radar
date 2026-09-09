<?php

namespace App\Http\Controllers;

use App\Models\Scrapyard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminScrapyardController extends Controller
{
    public function index(): View
    {
        return view('admin.scrapyards.index', [
            'scrapyards' => Scrapyard::query()
                ->with('user')
                ->latest()
                ->get(),
        ]);
    }

    public function updateStatus(Request $request, Scrapyard $scrapyard): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        if ((bool) $validated['is_active'] && ! $scrapyard->user?->hasVerifiedEmail()) {
            return redirect()
                ->route('admin.scrapyards.index')
                ->with('error', 'Impossible d’activer cette casse tant que l’adresse email du compte n’a pas été vérifiée.');
        }

        $scrapyard->forceFill([
            'is_active' => (bool) $validated['is_active'],
        ])->save();

        return redirect()
            ->route('admin.scrapyards.index')
            ->with('success', 'Le statut de la casse a été mis à jour.');
    }
}
