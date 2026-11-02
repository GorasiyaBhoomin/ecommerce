<?php

namespace App\Http\Controllers;

use App\Models\users;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        if ($search === '') {
            $search = $request->string('q')->trim()->toString();
        }
        $role = $request->string('role')->trim()->toString();

        $query = users::query();

        if ($role !== '' && $role !== 'all') {
            $query->where('role', $role);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('city', 'like', '%'.$search.'%');
            });
        }

        return view('admin.admin-users', [
            'users' => $query->orderByDesc('created_at')->get(),
            'search' => $search,
            'role' => $role,
        ]);
    }
}
