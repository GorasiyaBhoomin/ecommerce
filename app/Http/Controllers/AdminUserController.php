<?php

namespace App\Http\Controllers;

use App\Models\users;

class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin.admin-users', [
            'users' => users::orderByDesc('created_at')->get(),
        ]);
    }
}
