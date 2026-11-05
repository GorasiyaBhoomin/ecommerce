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
        $adminId = $request->session()->get('admin_id');

        // Fallback: check if active user session belongs to an admin
        if (! $adminId) {
            $userId = $request->session()->get('user_id');
            if ($userId) {
                $user = User::find($userId);
                if ($user && $user->role === 'admin') {
                    $adminId = $user->id;
                    $request->session()->put('admin_id', $adminId);
                    $request->session()->put('admin_name', $user->name);
                }
            }
        }

        if (! $adminId) {
            if ($request->isMethod('GET')) {
                $request->session()->put('url.intended', $request->fullUrl());
            }

            return redirect('/login')->with('error', 'Please log in as an administrator to access the admin panel.');
        }

        $admin = User::find($adminId);

        if (! $admin || $admin->role !== 'admin') {
            $request->session()->forget(['admin_id', 'admin_name']);

            return redirect('/login')->with('error', 'Access denied. You do not have administrator permissions to access this page.');
        }

        return $next($request);
    }
}
