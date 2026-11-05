<?php

namespace App\Http\Controllers;

use App\Jobs\SendWelcomeEmailJob;
use App\Mail\ResetPasswordMail;
use App\Models\Order;
use App\Models\users;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Razorpay\Api\Api;

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

        SendWelcomeEmailJob::dispatch($users)->onQueue('emails');

        return redirect('/login')->with('success', 'Registration successful. You can now log in.');
    }

    public function userlogin(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        $user = users::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $intended = session()->pull('url.intended');

            if ($user->role === 'admin') {
                session([
                    'admin_id' => $user->id,
                    'admin_name' => $user->name,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_role' => 'admin',
                ]);

                if (! is_string($intended) || ! str_contains($intended, 'admin')) {
                    $intended = '/adminorders';
                }

                return redirect($intended)->with('success', 'Welcome Admin!');
            }

            session([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => 'user',
            ]);

            $redirectUrl = ($intended && ! str_contains($intended, 'admin')) ? $intended : '/index';

            return redirect($redirectUrl)->with('success', 'Login successful!');
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

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        session()->forget(['user_id', 'user_name', 'user_role']);

        if (! session('admin_id')) {
            session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect('/login')->with('success', 'Logged out successfully.');
    }

    // API REGISTER
    public function apiRegister(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|regex:/^[a-zA-Z]+$/u|min:3|max:12',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'phone' => 'required',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
        ]);

        $user = new users;

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->phone = $data['phone'];
        $user->address = $data['address'] ?? null;
        $user->city = $data['city'] ?? null;
        $user->state = $data['state'] ?? null;
        $user->postal_code = $data['postal_code'] ?? null;
        $user->role = 'user';
        $user->orders_count = 0;
        $user->total_spent = 0.00;

        $user->save();

        $token = $user->createToken('shopkart-api')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // API LOGIN
    public function apiLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = users::where(
            'email',
            $request->email
        )->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        if (isset($user->status) && $user->status !== 'active') {
            return response()->json([
                'status' => false,
                'message' => 'Your account is not active',
            ], 403);
        }

        $user->last_login_at = now();
        $user->save();

        $token = $user->createToken('shopkart-api')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    // API LOGOUT
    public function apiLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout successful',
        ]);
    }

    // API PROFILE
    public function apiProfile(Request $request)
    {
        return response()->json([
            'status' => true,
            'data' => $request->user(),
        ]);
    }

    // ROZORPAY
    public function index()
    {
        return view('user.rozorpay');
    }

    public function payment(Request $request)
    {
        // $user = users::find($request->session()->get('user_id'));
        // $order = Order::find($request->session()->get('order_id'));
        // $order->payment_status = 'paid';
        // $order->save();
        // return view('rozorpay');

        $amount = $request->input('amount');
        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
        $orderData = [
            'receipt' => 'order_'.rand(1000, 9999),
            'amount' => $amount * 100,
            'currency' => 'INR',
            'payment_capture' => 1,
        ];

        $order = $api->order->create($orderData);

        // echo $order['id'];
        return view('user.payment', ['orderId' => $order['id'], 'amount' => $amount * 100]);
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotPassword(): View
    {
        return view('user.forgot-password');
    }

    /**
     * Send password reset link email.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = users::where('email', $request->email)->first();

        if (! $user) {
            return back()->withInput()->withErrors([
                'email' => 'We could not find an account with that email address.',
            ]);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => now(),
            ]
        );

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);

        $mailSent = false;
        try {
            Mail::to($request->email)->send(new ResetPasswordMail($user, $resetUrl));
            $mailSent = true;
        } catch (\Throwable $e) {
            $mailSent = false;
        }

        $redirect = back()->with('success', 'A password reset link has been emailed to '.$request->email.'. Please check your inbox.');

        if (! $mailSent) {
            $redirect->with('warning', 'Notice: Could not connect to mail server in this environment.')
                ->with('reset_link', $resetUrl);
        }

        return $redirect;
    }

    /**
     * Show the reset password form.
     */
    public function showResetPassword(Request $request, string $token): View|RedirectResponse
    {
        $email = $request->query('email');

        $record = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->when($email, fn ($q) => $q->where('email', $email))
            ->first();

        if (! $record) {
            return redirect()->route('password.request')
                ->with('error', 'This password reset link is invalid or has already been used.');
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            return redirect()->route('password.request')
                ->with('error', 'This password reset link has expired. Please request a new one.');
        }

        return view('user.reset-password', [
            'token' => $token,
            'email' => $record->email ?? $email,
        ]);
    }

    /**
     * Handle the password reset submission.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (! $record) {
            return back()->withInput()->withErrors([
                'email' => 'Invalid password reset token or mismatched email.',
            ]);
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'This password reset token has expired. Please request a new link.',
            ]);
        }

        $user = users::where('email', $request->email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->remember_token = null;
            $user->save();
        }

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Your password has been successfully reset! You can now log in with your new password.');
    }
}
