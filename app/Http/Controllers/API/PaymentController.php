<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class PaymentController extends Controller
{
    /**
     * Get available payment methods and delivery fee policies.
     */
    public function methods(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Payment methods retrieved successfully',
            'data' => [
                'currency' => 'INR',
                'currency_symbol' => '₹',
                'methods' => [
                    [
                        'id' => 'cod',
                        'name' => 'Cash on Delivery (COD)',
                        'description' => 'Pay cash when package is delivered to your doorstep.',
                        'is_active' => true,
                    ],
                    [
                        'id' => 'stripe',
                        'name' => 'Online Payment (Stripe)',
                        'description' => 'Pay securely using Credit/Debit Cards, UPI, Netbanking via Stripe.',
                        'is_active' => ! empty(config('services.stripe.secret')),
                    ],
                ],
                'delivery_policy' => [
                    'free_shipping_threshold' => 499.00,
                    'standard_shipping_charge' => 49.00,
                    'message' => 'Free delivery on all orders above ₹499. ₹49 delivery charge for orders under ₹499.',
                ],
            ],
        ], 200);
    }

    /**
     * Create payment request for an order.
     */
    public function createPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:stripe,cod',
        ]);

        $user = $request->user();

        $query = Order::with('items.product')->where('id', $validated['order_id']);
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $order = $query->first();

        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'status' => false,
                'message' => 'This order has already been paid for.',
            ], 422);
        }

        if ($validated['payment_method'] === 'cod') {
            $order->payment_method = 'cod';
            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Cash on delivery payment selected successfully.',
                'data' => [
                    'order_id' => $order->id,
                    'payment_method' => 'cod',
                    'payment_status' => $order->payment_status,
                    'total_amount' => (float) $order->total_amount,
                ],
            ], 200);
        }

        // Stripe online checkout
        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            return response()->json([
                'status' => false,
                'message' => 'Stripe payment gateway is not configured.',
            ], 500);
        }

        $lineItems = [];
        foreach ($order->items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'inr',
                    'product_data' => [
                        'name' => $item->product_name,
                    ],
                    'unit_amount' => (int) round($item->price * 100),
                ],
                'quantity' => $item->quantity,
            ];
        }

        if ($order->delivery_fee > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'inr',
                    'product_data' => [
                        'name' => 'Standard Express Delivery',
                    ],
                    'unit_amount' => (int) ($order->delivery_fee * 100),
                ],
                'quantity' => 1,
            ];
        }

        Stripe::setApiKey($stripeSecret);

        try {
            $session = StripeSession::create([
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('payment.stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.stripe.cancel'),
                'metadata' => [
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                ],
            ]);

            $order->stripe_session_id = $session->id;
            $order->payment_method = 'stripe';
            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Stripe payment session created successfully',
                'data' => [
                    'order_id' => $order->id,
                    'payment_method' => 'stripe',
                    'session_id' => $session->id,
                    'checkout_url' => $session->url,
                    'total_amount' => (float) $order->total_amount,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Stripe checkout initialization failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify payment status using Stripe Checkout session ID or order ID.
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'nullable|string',
            'order_id' => 'nullable|integer',
        ]);

        if (empty($validated['session_id']) && empty($validated['order_id'])) {
            return response()->json([
                'status' => false,
                'message' => 'Either session_id or order_id must be provided.',
            ], 422);
        }

        $user = $request->user();

        // 1. If verified by session_id with Stripe
        if (! empty($validated['session_id'])) {
            $sessionId = $validated['session_id'];

            // Check if order already recorded with this session
            $order = Order::where('stripe_session_id', $sessionId)->first();

            $stripeSecret = config('services.stripe.secret');
            if (empty($stripeSecret)) {
                if ($order && $order->payment_status === 'paid') {
                    return response()->json([
                        'status' => true,
                        'message' => 'Payment already verified.',
                        'data' => [
                            'order_id' => $order->id,
                            'transaction_id' => $order->payment_id,
                            'payment_status' => 'paid',
                            'total_amount' => (float) $order->total_amount,
                        ],
                    ], 200);
                }

                return response()->json([
                    'status' => false,
                    'message' => 'Stripe secret key not configured to verify session.',
                ], 500);
            }

            try {
                Stripe::setApiKey($stripeSecret);
                $session = StripeSession::retrieve($sessionId);

                if ($session->payment_status !== 'paid') {
                    return response()->json([
                        'status' => false,
                        'message' => 'Payment has not been completed. Status: '.$session->payment_status,
                        'payment_status' => $session->payment_status,
                    ], 400);
                }

                $paymentIntentId = $session->payment_intent;
                if (is_object($paymentIntentId)) {
                    $paymentIntentId = $paymentIntentId->id ?? null;
                }

                if ($order) {
                    $order->payment_status = 'paid';
                    $order->stripe_payment_id = $paymentIntentId;
                    $order->payment_id = $paymentIntentId;
                    $order->save();
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Payment verified successfully.',
                    'data' => [
                        'order_id' => $order?->id,
                        'order_number' => $order ? 'SK-'.$order->id : null,
                        'session_id' => $sessionId,
                        'transaction_id' => $paymentIntentId,
                        'payment_status' => 'paid',
                        'total_amount' => $order ? (float) $order->total_amount : ($session->amount_total / 100),
                    ],
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment verification error: '.$e->getMessage(),
                ], 500);
            }
        }

        // 2. If verified by order_id
        $order = Order::where('id', $validated['order_id'])->first();
        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Payment status retrieved',
            'data' => [
                'order_id' => $order->id,
                'order_number' => 'SK-'.$order->id,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'transaction_id' => $order->payment_id,
                'total_amount' => (float) $order->total_amount,
            ],
        ], 200);
    }
}
