<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\cart_items;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class OrderController extends Controller
{
    /**
     * List user orders with optional filtering by status or type.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $request->string('status')->trim()->toString();
        $type = $request->string('type')->trim()->toString();

        $query = Order::with(['items.product.images', 'items.variant.values.group'])
            ->where('user_id', $user->id)
            ->latest();

        if ($status !== '') {
            $query->where('order_status', $status);
        }

        if ($type === 'active') {
            $query->whereNotIn('order_status', ['delivered', 'cancelled']);
        } elseif ($type === 'delivered' || $type === 'completed') {
            $query->where('order_status', 'delivered');
        } elseif ($type === 'cancelled') {
            $query->where('order_status', 'cancelled');
        }

        $orders = $query->get()->map(fn (Order $order): array => $this->transformOrder($order));

        $activeCount = Order::where('user_id', $user->id)
            ->whereNotIn('order_status', ['delivered', 'cancelled'])
            ->count();

        $deliveredCount = Order::where('user_id', $user->id)
            ->where('order_status', 'delivered')
            ->count();

        return response()->json([
            'status' => true,
            'message' => 'Orders retrieved successfully',
            'data' => [
                'orders' => $orders,
                'summary' => [
                    'total_orders' => $orders->count(),
                    'active_orders' => $activeCount,
                    'delivered_orders' => $deliveredCount,
                    'total_spent' => (float) $user->total_spent,
                ],
            ],
        ], 200);
    }

    /**
     * Get single order details.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $query = Order::with(['items.product.images', 'items.variant.values.group', 'user'])
            ->where('id', $id);

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

        return response()->json([
            'status' => true,
            'message' => 'Order retrieved successfully',
            'data' => [
                'order' => $this->transformOrder($order, true),
            ],
        ], 200);
    }

    /**
     * Get items of an order.
     */
    public function items(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $query = Order::with(['items.product.images', 'items.variant.values.group'])
            ->where('id', $id);

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

        $transformed = $this->transformOrder($order, false);

        return response()->json([
            'status' => true,
            'message' => 'Order items retrieved successfully',
            'data' => [
                'order_id' => $order->id,
                'order_number' => 'SK-'.$order->id,
                'items_count' => count($transformed['items']),
                'items' => $transformed['items'],
            ],
        ], 200);
    }

    /**
     * Place a new order (from cart or direct items) using COD or Stripe.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|digits:6',
            'landmark' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cod,stripe',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.variant_id' => 'required_with:items|exists:product_variants,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        $user = $request->user();
        $isDirectOrder = ! empty($validated['items']);

        // Handle Stripe payment session generation if stripe chosen
        if ($validated['payment_method'] === 'stripe') {
            return $this->handleStripeOrder($request, $user, $validated, $isDirectOrder);
        }

        // Handle Cash on Delivery (COD) order
        try {
            $order = DB::transaction(function () use ($validated, $user, $isDirectOrder) {
                $orderProducts = [];
                $subtotal = 0.0;

                if ($isDirectOrder) {
                    foreach ($validated['items'] as $itemData) {
                        $variant = ProductVariant::where('id', $itemData['variant_id'])
                            ->lockForUpdate()
                            ->first();

                        $product = Product::find($itemData['product_id']);

                        if (! $variant || ! $product || $variant->product_id != $product->id || ! $variant->is_active) {
                            throw new \Exception('Product variant is invalid or inactive.');
                        }

                        if ($variant->stock_qty < $itemData['quantity']) {
                            throw new \Exception('Insufficient stock for "'.$product->name.'". Only '.$variant->stock_qty.' units left.');
                        }

                        $price = $this->effectivePrice($variant);
                        $itemSubtotal = $price * $itemData['quantity'];
                        $subtotal += $itemSubtotal;

                        $orderProducts[] = [
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'product_name' => $product->name,
                            'quantity' => $itemData['quantity'],
                            'price' => $price,
                            'subtotal' => $itemSubtotal,
                            'variant_model' => $variant,
                        ];
                    }
                } else {
                    $cartItems = cart_items::with(['product', 'variant'])
                        ->where('user_id', $user->id)
                        ->lockForUpdate()
                        ->get();

                    if ($cartItems->isEmpty()) {
                        throw new \Exception('Your cart is empty. Please add items to cart before placing an order.');
                    }

                    foreach ($cartItems as $cartItem) {
                        $variant = ProductVariant::where('id', $cartItem->variant_id)
                            ->lockForUpdate()
                            ->first();

                        $product = $cartItem->product;

                        if (! $variant || ! $product || $variant->product_id != $cartItem->product_id || ! $variant->is_active) {
                            throw new \Exception('A product variant in your cart is no longer available.');
                        }

                        if ($variant->stock_qty < $cartItem->quantity) {
                            throw new \Exception('Insufficient stock for "'.$product->name.'". Available: '.$variant->stock_qty);
                        }

                        $price = $this->effectivePrice($variant);
                        $itemSubtotal = $price * $cartItem->quantity;
                        $subtotal += $itemSubtotal;

                        $orderProducts[] = [
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'product_name' => $product->name,
                            'quantity' => $cartItem->quantity,
                            'price' => $price,
                            'subtotal' => $itemSubtotal,
                            'variant_model' => $variant,
                        ];
                    }
                }

                $deliveryFee = $subtotal > 499 ? 0.0 : 49.0;
                $totalAmount = $subtotal + $deliveryFee;

                $order = Order::create([
                    'user_id' => $user->id,
                    'customer_name' => $validated['customer_name'],
                    'phone' => $validated['phone'],
                    'address' => $validated['address'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'postal_code' => $validated['postal_code'],
                    'landmark' => $validated['landmark'] ?? null,
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'total_amount' => $totalAmount,
                    'payment_method' => 'cod',
                    'payment_status' => 'pending',
                    'order_status' => 'pending',
                ]);

                foreach ($orderProducts as $item) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'variant_id' => $item['variant_id'],
                        'product_name' => $item['product_name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    // Deduct stock
                    $variantModel = $item['variant_model'];
                    $variantModel->stock_qty -= $item['quantity'];
                    $variantModel->save();
                }

                // Increment user totals
                User::where('id', $user->id)->increment('orders_count');
                User::where('id', $user->id)->increment('total_spent', $totalAmount);

                // Clear cart if placed from cart
                if (! $isDirectOrder) {
                    cart_items::where('user_id', $user->id)->delete();
                }

                return $order;
            });

            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order' => $this->transformOrder($order->load(['items.product', 'items.variant.values.group']), true),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel an existing order (only allowed before shipment).
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $query = Order::with('items.variant')->where('id', $id);
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

        $cancellableStatuses = ['pending', 'received', 'process', 'processing'];
        if (! in_array(strtolower((string) $order->order_status), $cancellableStatuses)) {
            return response()->json([
                'status' => false,
                'message' => 'This order cannot be cancelled because it is already '.($order->order_status).'.',
            ], 422);
        }

        DB::transaction(function () use ($order) {
            // Restore variant stocks
            foreach ($order->items as $item) {
                if ($item->variant) {
                    $item->variant->stock_qty += $item->quantity;
                    $item->variant->save();
                }
            }

            $order->order_status = 'cancelled';
            if ($order->payment_status === 'paid') {
                $order->payment_status = 'refund_pending';
            } else {
                $order->payment_status = 'cancelled';
            }
            $order->save();
        });

        return response()->json([
            'status' => true,
            'message' => 'Order cancelled successfully and stock has been restored.',
            'data' => [
                'order' => $this->transformOrder($order->fresh(['items.product', 'items.variant.values.group']), true),
            ],
        ], 200);
    }

    /**
     * Track order status timeline.
     */
    public function track(Request $request, int $id): JsonResponse
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

        $currentStatus = strtolower((string) $order->order_status);

        $stages = [
            [
                'stage' => 'placed',
                'label' => 'Order Placed',
                'description' => 'Your order has been received.',
                'completed' => true,
                'current' => $currentStatus === 'pending' || $currentStatus === 'received',
                'timestamp' => $order->created_at?->format('d M Y, h:i A'),
            ],
            [
                'stage' => 'processing',
                'label' => 'Processing',
                'description' => 'Your order is being packed and prepared.',
                'completed' => in_array($currentStatus, ['processing', 'process', 'shipping', 'shipped', 'out_for_delivery', 'delivered']),
                'current' => $currentStatus === 'processing' || $currentStatus === 'process',
                'timestamp' => null,
            ],
            [
                'stage' => 'shipped',
                'label' => 'Shipped',
                'description' => 'Your order has been dispatched from the warehouse.',
                'completed' => in_array($currentStatus, ['shipping', 'shipped', 'out_for_delivery', 'delivered']),
                'current' => $currentStatus === 'shipping' || $currentStatus === 'shipped',
                'timestamp' => null,
            ],
            [
                'stage' => 'out_for_delivery',
                'label' => 'Out for Delivery',
                'description' => 'The courier is delivering your package today.',
                'completed' => in_array($currentStatus, ['out_for_delivery', 'delivered']),
                'current' => $currentStatus === 'out_for_delivery',
                'timestamp' => null,
            ],
            [
                'stage' => 'delivered',
                'label' => 'Delivered',
                'description' => 'Package has been delivered.',
                'completed' => $currentStatus === 'delivered',
                'current' => $currentStatus === 'delivered',
                'timestamp' => $currentStatus === 'delivered' ? $order->updated_at?->format('d M Y, h:i A') : null,
            ],
        ];

        return response()->json([
            'status' => true,
            'message' => 'Order tracking retrieved successfully',
            'data' => [
                'order_id' => $order->id,
                'order_number' => 'SK-'.$order->id,
                'order_status' => $order->order_status,
                'payment_status' => $order->payment_status,
                'payment_method' => strtoupper((string) $order->payment_method),
                'total_amount' => (float) $order->total_amount,
                'is_cancelled' => $currentStatus === 'cancelled',
                'is_delivered' => $currentStatus === 'delivered',
                'tracking_timeline' => $stages,
            ],
        ], 200);
    }

    /**
     * Handle Stripe Checkout session initialization for API.
     */
    private function handleStripeOrder(Request $request, User $user, array $validated, bool $isDirectOrder): JsonResponse
    {
        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            return response()->json([
                'status' => false,
                'message' => 'Stripe payment gateway is not configured on this server.',
            ], 500);
        }

        $items = [];
        $subtotal = 0.0;

        if ($isDirectOrder) {
            foreach ($validated['items'] as $itemData) {
                $variant = ProductVariant::find($itemData['variant_id']);
                $product = Product::find($itemData['product_id']);
                if (! $variant || ! $product || $variant->stock_qty < $itemData['quantity']) {
                    return response()->json([
                        'status' => false,
                        'message' => 'One or more items are out of stock.',
                    ], 422);
                }
                $price = $this->effectivePrice($variant);
                $subtotal += $price * $itemData['quantity'];
                $items[] = [
                    'price_data' => [
                        'currency' => 'inr',
                        'product_data' => [
                            'name' => $product->name,
                        ],
                        'unit_amount' => (int) round($price * 100),
                    ],
                    'quantity' => $itemData['quantity'],
                ];
            }
        } else {
            $cartItems = cart_items::with(['product', 'variant'])->where('user_id', $user->id)->get();
            if ($cartItems->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cart is empty.',
                ], 422);
            }

            foreach ($cartItems as $item) {
                $price = $this->effectivePrice($item->variant);
                $subtotal += $price * $item->quantity;
                $items[] = [
                    'price_data' => [
                        'currency' => 'inr',
                        'product_data' => [
                            'name' => $item->product->name,
                        ],
                        'unit_amount' => (int) round($price * 100),
                    ],
                    'quantity' => $item->quantity,
                ];
            }
        }

        $deliveryFee = $subtotal > 499 ? 0.0 : 49.0;
        if ($deliveryFee > 0) {
            $items[] = [
                'price_data' => [
                    'currency' => 'inr',
                    'product_data' => [
                        'name' => 'Standard Express Delivery',
                    ],
                    'unit_amount' => (int) ($deliveryFee * 100),
                ],
                'quantity' => 1,
            ];
        }

        Stripe::setApiKey($stripeSecret);

        try {
            $session = StripeSession::create([
                'line_items' => $items,
                'mode' => 'payment',
                'success_url' => route('payment.stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.stripe.cancel'),
                'metadata' => [
                    'user_id' => $user->id,
                    'customer_name' => $validated['customer_name'],
                    'phone' => $validated['phone'],
                    'address' => $validated['address'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'postal_code' => $validated['postal_code'],
                    'landmark' => $validated['landmark'] ?? '',
                ],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Stripe checkout session initialized',
                'data' => [
                    'payment_method' => 'stripe',
                    'stripe_session_id' => $session->id,
                    'checkout_url' => $session->url,
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'total' => $subtotal + $deliveryFee,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Stripe checkout failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download or view order invoice PDF.
     */
    public function downloadInvoice(Request $request, int $id): Response|JsonResponse
    {
        $user = $request->user();

        $query = Order::with(['items.variant.values', 'user'])->where('id', $id);
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

        return Pdf::loadView('pdf.invoice', compact('order'))
            ->setPaper('a4')
            ->download('invoice-SK-'.$order->id.'.pdf');
    }

    /**
     * Get JSON data for order invoice.
     */
    public function invoiceData(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $query = Order::with(['items.variant.values', 'user'])->where('id', $id);
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

        return response()->json([
            'status' => true,
            'message' => 'Invoice data retrieved successfully',
            'data' => [
                'invoice_number' => 'SK-'.$order->id,
                'order_date' => $order->created_at?->format('d M Y, h:i A'),
                'customer' => [
                    'name' => $order->customer_name,
                    'phone' => $order->phone,
                    'email' => $order->user->email ?? null,
                    'shipping_address' => [
                        'address' => $order->address,
                        'landmark' => $order->landmark,
                        'city' => $order->city,
                        'state' => $order->state,
                        'postal_code' => $order->postal_code,
                    ],
                ],
                'pricing' => [
                    'subtotal' => (float) $order->subtotal,
                    'delivery_fee' => (float) $order->delivery_fee,
                    'total_amount' => (float) $order->total_amount,
                    'payment_method' => strtoupper((string) $order->payment_method),
                    'payment_status' => $order->payment_status,
                ],
                'items' => $order->items->map(fn ($item) => [
                    'product_name' => $item->product_name,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->price,
                    'subtotal' => (float) $item->subtotal,
                    'variant' => $item->variant?->values->pluck('value')->implode(' / '),
                ])->values()->all(),
            ],
        ], 200);
    }

    /**
     * Admin: List all orders with summary metrics.
     */
    public function adminOrders(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        $orders = Order::with('items')->latest()->get();

        $statuses = [
            'received' => ['pending', 'received'],
            'process' => ['processing', 'process'],
            'shipping' => ['shipped', 'shipping'],
            'out_for_delivery' => ['out_for_delivery'],
            'delivered' => ['delivered'],
            'cancelled' => ['cancelled'],
        ];

        $summary = [];
        foreach ($statuses as $key => $values) {
            $filteredOrders = $orders->filter(function ($order) use ($values) {
                return in_array(strtolower(trim($order->order_status ?? 'pending')), $values);
            });

            $summary[$key] = [
                'count' => $filteredOrders->count(),
                'total' => (float) $filteredOrders->sum('total_amount'),
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Admin orders retrieved successfully',
            'data' => [
                'orders' => $orders->map(fn ($o) => $this->transformOrder($o)),
                'summary' => $summary,
            ],
        ], 200);
    }

    /**
     * Admin: Update order status and COD payment status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        $validated = $request->validate([
            'order_status' => 'required|in:received,process,shipping,out_for_delivery,delivered,cancelled,return_requested,returned,exchange_requested,exchanged',
            'cod_payment_collected' => 'nullable|boolean',
        ]);

        $statusMap = [
            'received' => 'pending',
            'process' => 'processing',
            'shipping' => 'shipped',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
            'return_requested' => 'return_requested',
            'returned' => 'returned',
            'exchange_requested' => 'exchange_requested',
            'exchanged' => 'exchanged',
        ];

        $order = Order::find($id);

        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $order->order_status = $statusMap[$validated['order_status']] ?? $validated['order_status'];

        if (
            strtolower(trim($order->payment_method ?? '')) === 'cod' &&
            $validated['order_status'] === 'delivered' &&
            $request->boolean('cod_payment_collected')
        ) {
            $order->payment_status = 'paid';
        }

        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'Order status updated successfully',
            'data' => [
                'order' => $this->transformOrder($order, true),
            ],
        ], 200);
    }

    /**
     * Transform an order model into array.
     *
     * @return array<string, mixed>
     */
    private function transformOrder(Order $order, bool $fullDetails = false): array
    {
        $items = $order->items->map(function ($item) {
            $firstImage = $item->product?->images?->first();
            $imageUrl = null;
            if ($firstImage !== null) {
                $imageUrl = Str::startsWith($firstImage->path, ['http://', 'https://'])
                    ? $firstImage->path
                    : asset($firstImage->path);
            }

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'product_name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'price' => (float) $item->price,
                'subtotal' => (float) $item->subtotal,
                'image' => $imageUrl,
                'variant_options' => $item->variant?->values->pluck('value')->implode(' / '),
            ];
        })->values()->all();

        $result = [
            'id' => $order->id,
            'order_number' => 'SK-'.$order->id,
            'customer_name' => $order->customer_name,
            'phone' => $order->phone,
            'order_status' => $order->order_status ?? 'pending',
            'payment_status' => $order->payment_status ?? 'pending',
            'payment_method' => $order->payment_method,
            'subtotal' => (float) $order->subtotal,
            'delivery_fee' => (float) $order->delivery_fee,
            'total_amount' => (float) $order->total_amount,
            'items_count' => count($items),
            'items' => $items,
            'created_at' => $order->created_at?->toIso8601String(),
            'formatted_date' => $order->created_at?->format('d M Y, h:i A'),
        ];

        if ($fullDetails) {
            $result['shipping_address'] = [
                'address' => $order->address,
                'landmark' => $order->landmark,
                'city' => $order->city,
                'state' => $order->state,
                'postal_code' => $order->postal_code,
            ];
            $result['stripe_session_id'] = $order->stripe_session_id;
            $result['stripe_payment_id'] = $order->stripe_payment_id;
        }

        return $result;
    }

    /**
     * Compute effective price for variant.
     */
    private function effectivePrice(ProductVariant $variant): float
    {
        $discounted = (float) $variant->discounted_price;

        return $discounted > 0 ? $discounted : (float) $variant->actual_price;
    }
}
