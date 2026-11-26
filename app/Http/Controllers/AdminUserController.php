<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        $status = $request->string('status')->trim()->toString();

        $query = users::query();

        if ($role !== '' && $role !== 'all') {
            $query->where('role', $role);
        }

        if ($status === 'active') {
            $query->whereNotNull('last_login_at');
        } elseif ($status === 'inactive') {
            $query->whereNull('last_login_at');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('city', 'like', '%'.$search.'%')
                    ->orWhere('state', 'like', '%'.$search.'%')
                    ->orWhere('address', 'like', '%'.$search.'%');
            });
        }

        // Sorting
        $allowedSorts = ['name', 'email', 'orders_count', 'total_spent', 'created_at'];
        $sortBy = $request->string('sort_by')->trim()->toString();
        $sortDir = strtolower($request->string('sort_dir', 'desc')->toString()) === 'asc' ? 'asc' : 'desc';

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderByDesc('created_at');
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $users = $query->paginate($perPage)->withQueryString();

        return view('admin.admin-users', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'perPage' => $perPage,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
        ]);
    }

    public function toggleStatus(User $user)
    {
        $currentAdminId = session('admin_id') ?? session('user_id');
        if ($user->id == $currentAdminId) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot change the status of your own account.',
                ], 422);
            }

            return back()->with('error', 'You cannot change the status of your own account.');
        }

        if ($user->last_login_at !== null) {
            $user->last_login_at = null;
        } else {
            $user->last_login_at = now();
        }
        $user->save();

        $isActive = $user->last_login_at !== null;
        $statusText = $isActive ? 'Active' : 'Inactive';

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $isActive,
                'status_text' => $statusText,
                'message' => "User \"{$user->name}\" status updated to {$statusText}.",
            ]);
        }

        return back()->with('success', "User \"{$user->name}\" status updated to {$statusText}.");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,user',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'user' => $user,
                'message' => "User \"{$user->name}\" updated successfully.",
            ]);
        }

        return back()->with('success', "User \"{$user->name}\" updated successfully.");
    }

    public function destroy(User $user)
    {
        $currentAdminId = session('admin_id') ?? session('user_id');
        if ($user->id == $currentAdminId) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.',
                ], 422);
            }

            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "User \"{$name}\" deleted successfully.",
            ]);
        }

        return redirect('/adminusers')->with('success', "User \"{$name}\" deleted successfully.");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $currentAdminId = session('admin_id') ?? session('user_id');
        $ids = collect($request->input('ids', []))
            ->reject(fn ($id) => $id == $currentAdminId)
            ->values()
            ->all();

        $count = User::whereIn('id', $ids)->delete();

        $message = "Deleted {$count} user(s) successfully.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'deleted_count' => $count,
                'message' => $message,
            ]);
        }

        return redirect('/adminusers')->with('success', $message);
    }
}
