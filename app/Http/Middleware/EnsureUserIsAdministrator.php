<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks any non-Administrator from reaching admin-only routes
 * (User Management, System Settings). This is the server-side
 * enforcement — the UI additionally hides these links from normal
 * Users, but that hiding is cosmetic only; this middleware is what
 * actually protects the routes.
 */
class EnsureUserIsAdministrator
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdministrator()) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
