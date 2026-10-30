<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\cart_items;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Get user's cart items with full breakdown (subtotal, savings, delivery fee, total).
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $cartItems = cart_items::with(['product.images', 'variant.values.group'])
            ->where('user_id', $userId)
            ->get();

        $subtotal = 0.0;
        $totalSavings = 0.0;
        $items = [];

        foreach ($cartItems as $item) {
            $variant = $item->variant;
            $product = $item->product;

            $actualPrice = (float) ($variant?->actual_price ?? 0);
            $discountedPrice = (float) ($variant?->discounted_price ?? 0);
            $unitPrice = $discountedPrice > 0 ? $discountedPrice : $actualPrice;

            $itemSubtotal = $unitPrice * $item->quantity;
            $savings = max(0, $actualPrice - $unitPrice) * $item->quantity;

            $subtotal += $itemSubtotal;
            $totalSavings += $savings;

            $firstImage = $product?->images->first();
            $imageUrl = null;
            if ($firstImage !== null) {
                $imageUrl = Str::startsWith($firstImage->path, ['http://', 'https://'])
                    ? $firstImage->path
                    : asset($firstImage->path);
            }

            $items[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'product_name' => $product?->name ?? 'Product Unavailable',
                'product_slug' => $product?->slug,
                'image' => $imageUrl,
                'sku' => $variant?->sku,
                'actual_price' => $actualPrice,
                'discounted_price' => $discountedPrice,
                'unit_price' => $unitPrice,
                'quantity' => (int) $item->quantity,
                'stock_qty' => (int) ($variant?->stock_qty ?? 0),
                'subtotal' => $itemSubtotal,
                'savings' => $savings,
                'variant_values' => $variant?->values->map(fn ($v) => [
                    'group' => $v->group->name ?? 'Option',
                    'value' => $v->value,
                ])->values()->all() ?? [],
            ];
        }

        $deliveryFee = ($cartItems->isNotEmpty() && $subtotal <= 499) ? 49.0 : 0.0;
        $total = $subtotal + $deliveryFee;

        return response()->json([
            'status' => true,
            'message' => 'Cart retrieved successfully',
            'data' => [
                'items' => $items,
                'summary' => [
                    'total_items_count' => count($items),
                    'total_quantity' => array_sum(array_column($items, 'quantity')),
                    'subtotal' => round($subtotal, 2),
                    'delivery_fee' => $deliveryFee,
                    'total_savings' => round($totalSavings, 2),
                    'total' => round($total, 2),
                    'free_delivery_threshold' => 499,
                    'is_free_delivery' => $deliveryFee === 0.0,
                ],
            ],
        ], 200);
    }

    /**
     * Add product variant to cart.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $userId = $request->user()->id;

        $product = Product::where('id', $validated['product_id'])->where('is_active', true)->first();
        if (! $product) {
            return response()->json([
                'status' => false,
                'message' => 'Product is not available.',
            ], 404);
        }

        $variant = ProductVariant::where('id', $validated['variant_id'])->where('is_active', true)->first();
        if (! $variant || $variant->product_id != $product->id) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid product variant selected.',
            ], 422);
        }

        $cartItem = cart_items::where('user_id', $userId)
            ->where('product_id', $validated['product_id'])
            ->where('variant_id', $validated['variant_id'])
            ->first();

        $newQuantity = ($cartItem?->quantity ?? 0) + (int) $validated['quantity'];

        if ($newQuantity > $variant->stock_qty) {
            return response()->json([
                'status' => false,
                'message' => 'Only '.$variant->stock_qty.' units are available in stock.',
            ], 422);
        }

        if ($cartItem) {
            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        } else {
            $cartItem = cart_items::create([
                'user_id' => $userId,
                'product_id' => $validated['product_id'],
                'variant_id' => $validated['variant_id'],
                'quantity' => $validated['quantity'],
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Product added to cart successfully',
            'data' => [
                'cart_item' => $cartItem,
            ],
        ], 201);
    }

    /**
     * Update quantity of an existing cart item.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $userId = $request->user()->id;

        $cartItem = cart_items::with('variant')
            ->where('user_id', $userId)
            ->find($id);

        if (! $cartItem) {
            return response()->json([
                'status' => false,
                'message' => 'Cart item not found',
            ], 404);
        }

        $variant = $cartItem->variant;
        if (! $variant || $request->quantity > $variant->stock_qty) {
            return response()->json([
                'status' => false,
                'message' => 'Requested quantity exceeds available stock ('.($variant?->stock_qty ?? 0).' units left).',
            ], 422);
        }

        $cartItem->quantity = (int) $request->quantity;
        $cartItem->save();

        return response()->json([
            'status' => true,
            'message' => 'Cart quantity updated successfully',
            'data' => [
                'cart_item' => $cartItem,
            ],
        ], 200);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $cartItem = cart_items::where('user_id', $userId)->find($id);

        if (! $cartItem) {
            return response()->json([
                'status' => false,
                'message' => 'Cart item not found',
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'status' => true,
            'message' => 'Item removed from cart successfully',
        ], 200);
    }

    /**
     * Clear all items in user's cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        cart_items::where('user_id', $userId)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Cart cleared successfully',
        ], 200);
    }
}
