<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsScrapyard
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user, 403);
        abort_unless($user->role === 'scrapyard', 403);
        abort_unless($user->scrapyard()->exists(), 403);

        return $next($request);
    }
}
