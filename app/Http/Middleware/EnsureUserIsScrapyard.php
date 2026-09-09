<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsScrapyard
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        abort_unless($user, 403);
        abort_unless($user->role === 'scrapyard', 403);
        abort_unless($user->scrapyard()->exists(), 403);

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        if (! $user->scrapyard->is_active && ! $request->routeIs('scrapyard.pending')) {
            return redirect()->route('scrapyard.pending');
        }

        return $next($request);
    }
}
