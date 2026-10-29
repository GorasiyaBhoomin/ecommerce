<?php

namespace App\Http\Controllers;

use App\Models\cart_items;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\users;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class ShopController extends Controller
{
    /**
     * Stock quantity at or below which a variant is flagged as low stock.
     */
    private const LOW_STOCK_THRESHOLD = 5;

    private const COLOR_HEX_MAP = [
        'black' => '#0F172A',
        'white' => '#FFFFFF',
        'silver' => '#CBD5E1',
        'grey' => '#94A3B8',
        'gray' => '#94A3B8',
        'beige' => '#E7E5E4',
        'red' => '#DC2626',
        'maroon' => '#7F1D1D',
        'orange' => '#F97316',
        'yellow' => '#EAB308',
        'gold' => '#D4AF37',
        'green' => '#16A34A',
        'olive' => '#4D7C0F',
        'teal' => '#0F766E',
        'blue' => '#2563EB',
        'navy' => '#1E3A8A',
        'purple' => '#7C3AED',
        'violet' => '#7C3AED',
        'pink' => '#DB2777',
        'brown' => '#78350F',
    ];

    public function index(Request $request): View
    {
        $categorySlug = $request->string('category')->trim()->toString();
        $search = $request->string('search')->trim()->toString();

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with(['category', 'images', 'variants.values.group'])
            ->where('is_active', true)
            ->when($categorySlug !== '', fn ($query) => $query->whereHas(
                'category',
                fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug),
            ))
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product): array => $this->catalogCard($product))
            ->all();

        $featuredProduct = Product::query()
            ->with(['category', 'images', 'variants.values.group'])
            ->where('is_active', true)
            ->orderBy('name')
            ->first();

        return view('user.index', [
            'products' => $products,
            'categories' => $categories,
            'categorySlug' => $categorySlug,
            'search' => $search,
            'totalProducts' => Product::query()->where('is_active', true)->count(),
            'featured' => $featuredProduct !== null ? $this->catalogCard($featuredProduct) : null,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'images', 'specifications', 'variants.values.group']);

        $variants = $this->variantMatrix($product);

        $defaultKey = array_key_first(array_filter(
            $variants,
            fn (array $variant): bool => $variant['stock_qty'] > 0,
        )) ?? array_key_first($variants);

        return view('user.product', [
            'product' => $product,
            'images' => $product->images->map(fn ($image): string => Str::startsWith($image->path, ['http://', 'https://']) ? $image->path : asset($image->path))->all(),
            'specifications' => $product->specifications
                ->map(fn ($specification): array => [
                    'label' => $specification->label,
                    'value' => $specification->value,
                ])
                ->all(),
            'defaultVariant' => $defaultKey !== null ? $variants[$defaultKey] : null,
            'payload' => [
                'groups' => $this->variantGroups($product),
                'variants' => $variants,
                'selected' => $defaultKey !== null
                    ? array_map('intval', explode('-', substr($defaultKey, 1)))
                    : [],
                'lowStockThreshold' => self::LOW_STOCK_THRESHOLD,
            ],
        ]);
    }

    /**
     * Build the data needed by a storefront product card.
     *
     * @return array<string, mixed>
     */
    private function catalogCard(Product $product): array
    {
        $variants = $product->variants->where('is_active', true);
        $stock = (int) $variants->sum('stock_qty');

        if ($stock <= 0) {
            $stockState = 'out_of_stock';
            $stockLabel = 'Out of stock';
        } elseif ($stock <= self::LOW_STOCK_THRESHOLD) {
            $stockState = 'low_stock';
            $stockLabel = 'Low stock ('.$stock.' left)';
        } else {
            $stockState = 'in_stock';
            $stockLabel = 'In stock';
        }

        $prices = $variants->map(fn (ProductVariant $variant): float => $this->effectivePrice($variant));

        return [
            'name' => $product->name,
            'slug' => $product->slug,
            'category' => $product->category->name ?? 'Uncategorised',
            'image' => $product->images->first() !== null ? (Str::startsWith($product->images->first()->path, ['http://', 'https://']) ? $product->images->first()->path : asset($product->images->first()->path)) : null,
            'description' => $product->description,
            'stock_state' => $stockState,
            'stock_label' => $stockLabel,
            'variant_summary' => $this->variantSummary($variants),
            'from_price' => $prices->isNotEmpty() ? $prices->min() : null,
        ];
    }

    /**
     * Describe the variant options of a product in a single line.
     *
     * @param  Collection<int, ProductVariant>  $variants
     */
    private function variantSummary($variants): string
    {
        $groupNames = $variants
            ->flatMap(fn (ProductVariant $variant) => $variant->values->pluck('group.name'))
            ->filter()
            ->unique()
            ->values();

        if ($groupNames->isEmpty()) {
            return 'No variants';
        }

        return $variants->count().' '.Str::plural('variant', $variants->count()).' • '.$groupNames->implode(' • ');
    }

    /**
     * Build the selectable variant groups of a product, ordered by group id.
     *
     * @return array<int, array<string, mixed>>
     */
    private function variantGroups(Product $product): array
    {
        $groups = [];

        foreach ($product->variants as $variant) {
            if (! $variant->is_active) {
                continue;
            }

            foreach ($variant->values as $value) {
                if ($value->group === null) {
                    continue;
                }

                $group = $value->group;

                $groups[$group->id] ??= [
                    'id' => $group->id,
                    'name' => $group->name,
                    'is_color' => (bool) preg_match('/colou?r/i', $group->name),
                    'values' => [],
                ];

                $groups[$group->id]['values'][$value->id] = [
                    'id' => $value->id,
                    'value' => $value->value,
                    'hex' => $this->colorHex($value->value),
                ];
            }
        }

        ksort($groups);

        return array_values(array_map(function (array $group): array {
            $group['values'] = array_values($group['values']);

            return $group;
        }, $groups));
    }

    private function variantMatrix(Product $product): array
    {
        $matrix = [];

        foreach ($product->variants as $variant) {
            if (! $variant->is_active) {
                continue;
            }

            $valueIds = $variant->values
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->sort()
                ->values()
                ->all();

            // A variant without option values acts as the product's default variant.
            $matrix['v'.implode('-', $valueIds)] = [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'actual_price' => (float) $variant->actual_price,
                'discounted_price' => (float) $variant->discounted_price,
                'price' => $this->effectivePrice($variant),
                'stock_qty' => (int) $variant->stock_qty,
                'is_low_stock' => $variant->stock_qty > 0 && $variant->stock_qty <= self::LOW_STOCK_THRESHOLD,
                'is_out_of_stock' => $variant->stock_qty <= 0,
            ];
        }

        return $matrix;
    }

    private function effectivePrice(ProductVariant $variant): float
    {
        $discounted = (float) $variant->discounted_price;

        return $discounted > 0 ? $discounted : (float) $variant->actual_price;
    }

    private function colorHex(string $value): string
    {
        return self::COLOR_HEX_MAP[mb_strtolower(trim($value))] ?? '#CBD5E1';
    }

    // add to cart
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Use your custom session login
        $userId = session('user_id');

        if (! $userId) {
            return redirect('/login')
                ->with('error', 'Please login first.');
        }

        $variant = ProductVariant::findOrFail($request->variant_id);

        if ($variant->product_id != $request->product_id) {
            return back()->with('error', 'Invalid product variant.');
        }

        // Find existing cart item
        $cartItem = cart_items::where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->where('variant_id', $request->variant_id)
            ->first();

        $newQuantity = ($cartItem?->quantity ?? 0) + (int) $request->quantity;

        // Check stock
        if ($newQuantity > $variant->stock_qty) {
            return back()->with('error', 'Only '.$variant->stock_qty.' units available.');
        }

        if ($cartItem) {
            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        } else {
            cart_items::create([
                'user_id' => $userId,
                'product_id' => $request->product_id,
                'variant_id' => $request->variant_id,
                'quantity' => $request->quantity,
            ]);
        }

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout');
        }

        return back()->with('success', 'Product added to cart successfully!');
    }
    // Display cart

    public function viewCart()
    {
        $userId = session('user_id');

        if (! $userId) {
            return redirect('/login')
                ->with('error', 'Please login first.');
        }

        // Get logged-in user
        $user = users::find($userId);

        if (! $user) {
            return redirect('/login')
                ->with('error', 'User not found.');
        }

        $cartItems = cart_items::with([
            'product',
            'variant.values',
        ])
            ->where('user_id', $userId)
            ->get();

        $subtotal = 0;
        $totalSavings = 0;

        foreach ($cartItems as $item) {

            $actualPrice = (float) (
                $item->variant?->actual_price ?? 0
            );

            $discountedPrice = (float) (
                $item->variant?->discounted_price ?? 0
            );

            $price = $discountedPrice > 0
                ? $discountedPrice
                : $actualPrice;

            $subtotal += $price * $item->quantity;

            $totalSavings += max(
                0,
                $actualPrice - $price
            ) * $item->quantity;
        }

        $deliveryFee = ($cartItems->isNotEmpty() && $subtotal <= 499) ? 49 : 0;

        $total = $subtotal + $deliveryFee;

        return view('user.checkout', compact(
            'user',
            'cartItems',
            'subtotal',
            'totalSavings',
            'deliveryFee',
            'total'
        ));
    }

    public function updateQuantity(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = cart_items::with('variant')
            ->where('user_id', session('user_id'))
            ->findOrFail($id);

        if (! $cartItem->variant ||
            $request->quantity > $cartItem->variant->stock_qty) {
            return back()->with('error', 'Requested quantity is unavailable.');
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return back()->with('success', 'Quantity updated!');
    }

    public function removeCart($id)
    {
        $cartItem = cart_items::where('user_id', session('user_id'))
            ->findOrFail($id);

        $cartItem->delete();

        return back()->with('success', 'Product removed!');
    }

    private function processStripePayment(Request $request, int $userId)
    {
        $cartItems = cart_items::with('product', 'variant')
            ->where('user_id', $userId)
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Your cart is empty.');
        }

        $lineItems = [];
        $subtotal = 0;

        foreach ($cartItems as $item) {
            $price = $this->effectivePrice($item->variant);
            $subtotal += $price * $item->quantity;

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'inr',
                    'product_data' => [
                        'name' => $item->product->name,
                    ],
                    'unit_amount' => $price * 100,
                ],
                'quantity' => $item->quantity,
            ];
        }

        $deliveryFee = $subtotal > 499 ? 0 : 49;

        if ($deliveryFee > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'inr',
                    'product_data' => [
                        'name' => 'Standard Express Delivery',
                    ],
                    'unit_amount' => $deliveryFee * 100,
                ],
                'quantity' => 1,
            ];
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = Session::create([
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('payment.stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.stripe.cancel'),
                'metadata' => [
                    'user_id' => $userId,
                    'customer_name' => $request->customer_name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'postal_code' => $request->postal_code,
                    'landmark' => $request->landmark,
                ],
                'payment_intent_data' => [
                    'metadata' => [
                        'user_id' => $userId,
                    ],
                ],
            ]);

            return redirect($session->url);
        } catch (\Exception $e) {
            return back()->with('error', 'Stripe Error: '.$e->getMessage());
        }
    }

    public function stripeSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return redirect()->route('checkout')
                ->with('error', 'Invalid payment session.');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // Retrieve the actual Checkout Session from Stripe.
            $session = Session::retrieve($sessionId);

            // Confirm that payment succeeded.
            if ($session->payment_status !== 'paid') {
                return redirect()->route('checkout')
                    ->with('error', 'Payment not completed.');
            }

            $userId = (int) $session->metadata->user_id;

            // Ensure the session belongs to the correct customer.
            if (! $userId || $userId !== (int) session('user_id')) {
                return redirect('/login')
                    ->with('error', 'Please log in to view your order.');
            }

            // Prevent duplicate orders if the success URL is refreshed.
            $existingOrder = Order::where(
                'stripe_session_id',
                $sessionId
            )->where('user_id', $userId)->first();

            if ($existingOrder) {
                return redirect()->route('orders.index')
                    ->with('success', 'Your payment and order are already confirmed.');
            }

            $this->fulfillStripeCheckoutSession($session);

            return redirect()->route('orders.index')
                ->with('success', 'Payment successful! Your order has been placed.');

        } catch (\Throwable $e) {
            Log::error('Stripe checkout success error', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('checkout')
                ->with(
                    'error',
                    'Payment processing needs attention. If you were charged, please contact support before paying again.'
                );
        }
    }

    /**
     * Create the order for a paid Stripe Checkout Session.
     *
     * Shared by the payment redirect (stripeSuccess) and the Stripe webhook.
     * Idempotent: returns the existing order when the session was already fulfilled.
     */
    public function fulfillStripeCheckoutSession(Session $session): Order
    {
        $sessionId = $session->id;

        $existingOrder = Order::where('stripe_session_id', $sessionId)->first();

        if ($existingOrder) {
            return $existingOrder;
        }

        // Get the Payment Intent ID from Stripe.
        $paymentIntentId = $session->payment_intent;

        if (is_object($paymentIntentId)) {
            $paymentIntentId = $paymentIntentId->id ?? null;
        }

        if (! $paymentIntentId) {
            throw new \Exception('Stripe Payment ID was not found.');
        }

        $userId = (int) $session->metadata->user_id;
        $customerData = $session->metadata->toArray();

        return DB::transaction(function () use (
            $userId,
            $customerData,
            $sessionId,
            $paymentIntentId,
            $session
        ) {
            $cartItems = cart_items::with('product', 'variant')
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw new \Exception(
                    'Your cart is empty. Please contact support if payment was deducted.'
                );
            }

            $subtotal = 0;
            $orderProducts = [];

            foreach ($cartItems as $item) {
                $variant = ProductVariant::where(
                    'id',
                    $item->variant_id
                )->lockForUpdate()->first();

                if (
                    ! $variant ||
                    ! $item->product ||
                    $variant->product_id != $item->product_id ||
                    $variant->stock_qty < $item->quantity
                ) {
                    throw new \Exception(
                        'Product stock is unavailable for '.
                        ($item->product->name ?? 'an item in your cart')
                    );
                }

                $price = $this->effectivePrice($variant);
                $itemSubtotal = $price * $item->quantity;

                $subtotal += $itemSubtotal;

                $orderProducts[] = [
                    'product_id' => $item->product_id,
                    'variant_id' => $variant->id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $price,
                    'subtotal' => $itemSubtotal,
                ];

                // Reserve the stock for this order.
                $variant->stock_qty -= $item->quantity;
                $variant->save();
            }

            $deliveryFee = $subtotal > 499 ? 0 : 49;
            $total = $subtotal + $deliveryFee;

            // Verify the charged amount matches the order total.
            if ((int) $session->amount_total !== (int) round($total * 100)) {
                throw new \Exception(
                    'The Stripe payment amount does not match the order total.'
                );
            }

            $order = Order::create([
                'user_id' => $userId,
                'customer_name' => $customerData['customer_name'],
                'phone' => $customerData['phone'],
                'address' => $customerData['address'],
                'city' => $customerData['city'],
                'state' => $customerData['state'],
                'postal_code' => $customerData['postal_code'],
                'landmark' => $customerData['landmark'] ?? null,

                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $total,

                'payment_method' => 'stripe',
                'payment_status' => 'paid',
                'order_status' => 'pending',

                // Save both Stripe identifiers.
                'stripe_session_id' => $sessionId,
                'stripe_payment_id' => $paymentIntentId,
                'payment_id' => $paymentIntentId,
            ]);

            foreach ($orderProducts as $orderProduct) {
                $order->items()->create($orderProduct);
            }

            users::where('id', $userId)
                ->increment('orders_count');

            users::where('id', $userId)->increment(
                'total_spent',
                $order->total_amount
            );

            cart_items::where('user_id', $userId)->delete();

            return $order;
        });
    }

    /**
     * Handle a failed Stripe payment reported by the webhook.
     */
    public function handleFailedStripePayment(object $paymentIntent): void
    {
        $paymentIntentId = $paymentIntent->id ?? null;

        Log::warning('Stripe payment failed', [
            'payment_intent' => $paymentIntentId,
            'user_id' => $paymentIntent->metadata->user_id ?? null,
            'reason' => $paymentIntent->last_payment_error->message ?? null,
        ]);

        if (! $paymentIntentId) {
            return;
        }

        Order::where('stripe_payment_id', $paymentIntentId)
            ->where('payment_status', '!=', 'paid')
            ->update(['payment_status' => 'failed']);
    }

    /**
     * Handle a refunded Stripe payment reported by the webhook.
     */
    public function handleRefundedStripePayment(object $charge): void
    {
        $paymentIntentId = $charge->payment_intent ?? null;

        if (! $paymentIntentId) {
            return;
        }

        Order::where('stripe_payment_id', $paymentIntentId)
            ->update(['payment_status' => 'refunded']);

        Log::info('Stripe payment refunded', [
            'payment_intent' => $paymentIntentId,
            'amount' => $charge->amount_refunded / 100,
        ]);
    }

    public function stripeCancel()
    {
        return redirect()->route('checkout')->with('error', 'Payment was cancelled. You can try again or choose another payment method.');
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|digits:6',
            'landmark' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cod,stripe',
        ]);

        $userId = session('user_id');

        if (! $userId) {
            return redirect('/login');
        }

        if ($request->payment_method === 'stripe') {
            return $this->processStripePayment($request, $userId);
        }

        $order = DB::transaction(function () use ($request, $userId) {

            $cartItems = cart_items::where('user_id', $userId)
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart is empty.',
                ]);
            }

            $orderProducts = [];
            $subtotal = 0;

            foreach ($cartItems as $item) {

                $variant = ProductVariant::where('id', $item->variant_id)
                    ->lockForUpdate()
                    ->first();

                if (! $variant ||
                    $variant->product_id != $item->product_id ||
                    $variant->stock_qty < $item->quantity) {

                    throw ValidationException::withMessages([
                        'cart' => 'A product is unavailable or has insufficient stock.',
                    ]);
                }

                $product = $item->product;

                if (! $product) {
                    throw ValidationException::withMessages([
                        'cart' => 'A product is no longer available.',
                    ]);
                }

                $price = $this->effectivePrice($variant);
                $itemSubtotal = $price * $item->quantity;

                $subtotal += $itemSubtotal;

                $orderProducts[] = [
                    'product_id' => $item->product_id,
                    'variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'quantity' => $item->quantity,
                    'price' => $price,
                    'subtotal' => $itemSubtotal,
                ];

                $variant->stock_qty -= $item->quantity;
                $variant->save();
            }

            $deliveryFee = $subtotal > 499 ? 0 : 49;

            $order = Order::create([
                'user_id' => $userId,
                'customer_name' => $request->customer_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'landmark' => $request->landmark,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $subtotal + $deliveryFee,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'order_status' => 'pending',
            ]);

            foreach ($orderProducts as $orderProduct) {
                $order->items()->create($orderProduct);
            }

            // Update logged-in user's total orders
            users::where('id', $userId)->increment('orders_count');

            // Update logged-in user's total spending
            users::where('id', $userId)->increment(
                'total_spent',
                $order->total_amount
            );

            // Clear user's cart
            cart_items::where('user_id', $userId)->delete();

            return $order;

        });

        return redirect('/myorders')
            ->with('success', 'Order placed successfully!');
    }

    public function orderSuccess($id)
    {
        $userId = session('user_id');

        if (! $userId) {
            return redirect('/login');
        }

        $order = Order::where('user_id', $userId)
            ->findOrFail($id);

        return redirect('/myorders')
            ->with('success', 'Order placed successfully!');

    }

    public function myOrders()
    {
        $userId = session('user_id');

        if (! $userId) {
            return redirect('/login');
        }

        $orders = Order::where('user_id', $userId)
            ->with('items')
            ->latest()
            ->get();

        return view('user.my-orders', compact('orders'));
    }

    public function downloadInvoice($id)
    {
        $userId = session('user_id');

        if (! $userId) {
            return redirect('/login');
        }

        $order = Order::where('user_id', $userId)
            ->with(['items.variant.values', 'user'])
            ->findOrFail($id);

        return Pdf::loadView('pdf.invoice', compact('order'))
            ->setPaper('a4')
            ->download('invoice-SK-'.$order->id.'.pdf');
    }
}
