<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Get authenticated user profile with summary.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $orders = Order::with(['items.product', 'items.variant'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $activeOrders = $orders->filter(function (Order $order): bool {
            return strtolower((string) $order->order_status) !== 'delivered' && strtolower((string) $order->order_status) !== 'cancelled';
        })->values();

        $deliveredOrders = $orders->filter(function (Order $order): bool {
            return strtolower((string) $order->order_status) === 'delivered';
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Profile retrieved successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'gender' => $user->gender,
                    'role' => $user->role,
                    'shipping_address' => [
                        'address' => $user->address,
                        'city' => $user->city,
                        'state' => $user->state,
                        'postal_code' => $user->postal_code,
                    ],
                    'orders_count' => (int) $user->orders_count,
                    'total_spent' => (float) $user->total_spent,
                    'last_login_at' => $user->last_login_at,
                    'created_at' => $user->created_at,
                ],
                'summary' => [
                    'total_orders' => $orders->count(),
                    'active_orders' => $activeOrders->count(),
                    'delivered_orders' => $deliveredOrders->count(),
                    'total_spent' => (float) $user->total_spent,
                ],
            ],
        ], 200);
    }

    /**
     * Update user personal details.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string|in:Female,Male,Other',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->gender = $validated['gender'] ?? null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'gender' => $user->gender,
                    'role' => $user->role,
                ],
            ],
        ], 200);
    }

    /**
     * Update user shipping address.
     */
    public function updateAddress(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
        ]);

        $user->address = $validated['address'] ?? null;
        $user->city = $validated['city'] ?? null;
        $user->state = $validated['state'] ?? null;
        $user->postal_code = $validated['postal_code'] ?? null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Address updated successfully',
            'data' => [
                'shipping_address' => [
                    'address' => $user->address,
                    'city' => $user->city,
                    'state' => $user->state,
                    'postal_code' => $user->postal_code,
                ],
            ],
        ], 200);
    }

    /**
     * Update user account password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided current password does not match our records.'],
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password updated successfully.',
        ], 200);
    }
}
