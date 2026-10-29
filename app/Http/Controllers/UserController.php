<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login()
    {
        return view('user.login');
    }

    public function register()
    {
        return view('user.register');
    }

    public function registerSubmit(Request $request)
    {
        $registrationData = $request->validate([
            'name' => 'required|regex:/^[a-zA-Z]+$/u|min:3|max:12',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
            'phone' => 'required',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
        ]);

        $users = new users;
        $users->name = $request->name;
        $users->email = $request->email;
        $users->password = Hash::make($request->password);
        $users->phone = $request->phone ?? null;
        $users->address = $request->address ?? null;
        $users->city = $request->city ?? null;
        $users->state = $request->state ?? null;
        $users->postal_code = $request->postal_code ?? null;
        $users->role = 'user';
        $users->orders_count = 0;
        $users->total_spent = 0.00;
        $users->last_login_at = $request->last_login_at ?? null;

        $users->save();

        return redirect('/login')->with('success', 'Registration successful. You can now log in.');
    }

    public function userlogin(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        $user = users::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            session(['user_id' => $user->id, 'user_name' => $user->name, 'user_role' => $user->role]);

            return $user->role == 'admin'
                ? redirect('/adminorders')->with('success', 'Welcome Admin!')
                : redirect('/index')->with('success', 'Login successful!');
        }

        return back()->with('error', 'Invalid email or password.');
    }

    public function profile(Request $request)
    {
        $userId = $request->session()->get('user_id');
        $user = users::find($userId);

        if (! $user) {
            return redirect('/login')->with('error', 'Please log in to continue.');
        }

        // Fetch orders with items and products
        $orders = Order::with(['items.product', 'items.variant'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        $activeOrders = $orders->filter(function ($order) {
            return $order->order_status !== 'delivered';
        });

        $previousOrders = $orders->filter(function ($order) {
            return $order->order_status === 'delivered';
        });

        return view('user.profile', [
            'user' => $user,
            'activeOrders' => $activeOrders,
            'previousOrders' => $previousOrders,
        ]);
    }

    public function profileUpdate(Request $request)
    {
        $user = users::find($request->session()->get('user_id'));

        if (! $user) {
            return redirect('/login')->with('error', 'Please log in to continue.');
        }

        $profileData = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string|in:Female,Male,Other',
        ]);

        $user->name = $profileData['name'];
        $user->email = $profileData['email'];
        $user->phone = $profileData['phone'] ?? null;
        $user->gender = $profileData['gender'] ?? null;
        $user->save();

        $request->session()->put('user_name', $user->name);

        return redirect('/profile#personal')->with('success', 'Profile updated successfully.');
    }

    public function addressUpdate(Request $request)
    {
        $user = users::find($request->session()->get('user_id'));

        if (! $user) {
            return redirect('/login')->with('error', 'Please log in to continue.');
        }

        $addressData = $request->validate([
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
        ]);

        $user->address = $addressData['address'] ?? null;
        $user->city = $addressData['city'] ?? null;
        $user->state = $addressData['state'] ?? null;
        $user->postal_code = $addressData['postal_code'] ?? null;
        $user->save();

        return redirect('/profile#addresses')->with('success', 'Address updated successfully.');
    }

    public function securityUpdate(Request $request)
    {
        $user = users::find($request->session()->get('user_id'));

        if (! $user) {
            return redirect('/login')->with('error', 'Please log in to continue.');
        }

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect('/profile#security')->with('success', 'Password updated successfully.');
    }

    public function logout()
    {
        session()->flush();

        return redirect('/login')->with('success', 'Logged out successfully.');
    }
}
