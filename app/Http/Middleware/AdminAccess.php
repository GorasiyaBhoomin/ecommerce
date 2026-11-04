<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->session()->get('user_id');

        if (! $userId) {
            if ($request->isMethod('GET')) {
                $request->session()->put('url.intended', $request->fullUrl());
            }

            return redirect('/login')->with('error', 'Please log in as an administrator to access the admin panel.');
        }

        $userRole = $request->session()->get('user_role');

        if (! $userRole) {
            $user = User::find($userId);
            $userRole = $user?->role;
            if ($userRole) {
                $request->session()->put('user_role', $userRole);
            }
        }

        if ($userRole !== 'admin') {
            return redirect('/login')->with('error', 'Access denied. You do not have administrator permissions to access this page.');
        }

        return $next($request);
    }
}
