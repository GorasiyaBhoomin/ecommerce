<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnExchangeController extends Controller
{
    /**
     * Submit a return request for a delivered order.
     */
    public function requestReturn(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'comments' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        $query = Order::with('items')->where('id', $id);
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

        if (strtolower((string) $order->order_status) !== 'delivered') {
            return response()->json([
                'status' => false,
                'message' => 'Only delivered orders are eligible for return. Current status: '.$order->order_status,
            ], 422);
        }

        $order->order_status = 'return_requested';
        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'Return request submitted successfully. Our support team will review and arrange a pickup.',
            'data' => [
                'order_id' => $order->id,
                'order_number' => 'SK-'.$order->id,
                'order_status' => $order->order_status,
                'return_reason' => $validated['reason'],
                'comments' => $validated['comments'] ?? null,
                'refund_amount' => (float) $order->total_amount,
                'instructions' => 'Keep the product in its original packaging with all tags intact for courier pickup.',
            ],
        ], 200);
    }

    /**
     * Submit an exchange request for a delivered order.
     */
    public function requestExchange(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'requested_variant_id' => 'required|exists:product_variants,id',
            'comments' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        $query = Order::with('items')->where('id', $id);
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

        if (strtolower((string) $order->order_status) !== 'delivered') {
            return response()->json([
                'status' => false,
                'message' => 'Only delivered orders are eligible for exchange. Current status: '.$order->order_status,
            ], 422);
        }

        $newVariant = ProductVariant::with(['product', 'values.group'])->find($validated['requested_variant_id']);

        if (! $newVariant || ! $newVariant->is_active) {
            return response()->json([
                'status' => false,
                'message' => 'The requested exchange variant is unavailable or inactive.',
            ], 422);
        }

        if ($newVariant->stock_qty <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'The requested exchange variant is currently out of stock.',
            ], 422);
        }

        $order->order_status = 'exchange_requested';
        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'Exchange request submitted successfully. Replacement item has been reserved.',
            'data' => [
                'order_id' => $order->id,
                'order_number' => 'SK-'.$order->id,
                'order_status' => $order->order_status,
                'exchange_reason' => $validated['reason'],
                'requested_variant' => [
                    'id' => $newVariant->id,
                    'sku' => $newVariant->sku,
                    'product_name' => $newVariant->product->name ?? 'Product',
                    'options' => $newVariant->values->pluck('value')->implode(' / '),
                ],
                'instructions' => 'Please keep the item in original condition. Exchange will be delivered upon pickup verification.',
            ],
        ], 200);
    }

    /**
     * Get return/exchange eligibility and current status for an order.
     */
    public function status(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $query = Order::where('id', $id);
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

        $isDelivered = strtolower((string) $order->order_status) === 'delivered';
        $currentStatus = strtolower((string) $order->order_status);

        return response()->json([
            'status' => true,
            'message' => 'Return/exchange status retrieved',
            'data' => [
                'order_id' => $order->id,
                'order_number' => 'SK-'.$order->id,
                'order_status' => $order->order_status,
                'is_eligible_for_return' => $isDelivered,
                'is_eligible_for_exchange' => $isDelivered,
                'is_return_requested' => $currentStatus === 'return_requested' || $currentStatus === 'returned',
                'is_exchange_requested' => $currentStatus === 'exchange_requested' || $currentStatus === 'exchanged',
            ],
        ], 200);
    }
}
