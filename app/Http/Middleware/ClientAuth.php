<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ClientAuth
{
    /**
     * Ensure a client (client guard) is authenticated and still portal-enabled.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $client = Auth::guard('client')->user();

        if (! $client) {
            return redirect()->route('portal.login');
        }

        // Revoke access immediately if the admin disabled the portal.
        if (! $client->portal_enabled) {
            Auth::guard('client')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('portal.login')
                ->withErrors(['email' => 'Your portal access has been disabled.']);
        }

        return $next($request);
    }
}
