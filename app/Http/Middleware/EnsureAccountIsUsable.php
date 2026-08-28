<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs on every authenticated request.
 *
 * 1. If the account has been deactivated by an Administrator since the
 *    session was created, log them out immediately (records/history stay
 *    intact — only login access is revoked).
 * 2. If the account was just created by an Administrator and still has a
 *    temporary password, force the user to change it before they can
 *    reach anything else in the dashboard.
 */
class EnsureAccountIsUsable
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been deactivated. Please contact an Administrator.']);
        }

        $allowedWhileMustChange = [
            'password.force.edit',
            'password.force.update',
            'logout',
        ];

        if ($user->must_change_password && ! $request->routeIs($allowedWhileMustChange)) {
            return redirect()->route('password.force.edit');
        }

        return $next($request);
    }
}
