<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

test('cancels unpaid orders older than 30 minutes and restores variant stock', function () {
    $user = User::factory()->create();

    $category = Category::create([
        'name' => 'Electronics',
        'slug' => 'electronics',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Wireless Earbuds',
        'slug' => 'wireless-earbuds',
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'sku' => 'WEB-BLK',
        'actual_price' => 1999,
        'discounted_price' => 1499,
        'stock_qty' => 10,
        'is_active' => true,
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'customer_name' => 'Tester John',
        'phone' => '9876543210',
        'address' => '456 Tech Park',
        'city' => 'Bangalore',
        'state' => 'Karnataka',
        'postal_code' => '560001',
        'subtotal' => 2998,
        'delivery_fee' => 0,
        'total_amount' => 2998,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'order_status' => 'pending',
    ]);
    $order->timestamps = false;
    $order->created_at = now()->subMinutes(35);
    $order->save();

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'product_name' => $product->name,
        'quantity' => 2,
        'price' => 1499,
        'subtotal' => 2998,
    ]);

    $this->artisan('orders:cancel-unpaid')
        ->expectsOutputToContain('Found 1 unpaid order(s) eligible for cancellation.')
        ->assertSuccessful();

    $order->refresh();
    $variant->refresh();

    expect($order->order_status)->toBe('cancelled')
        ->and($order->payment_status)->toBe('cancelled')
        ->and($variant->stock_qty)->toBe(12);
});

test('does not cancel unpaid orders that are younger than 30 minutes', function () {
    $user = User::factory()->create();

    $category = Category::create([
        'name' => 'Fashion',
        'slug' => 'fashion',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Cotton Shirt',
        'slug' => 'cotton-shirt',
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'sku' => 'CS-BLU-M',
        'actual_price' => 999,
        'discounted_price' => 799,
        'stock_qty' => 15,
        'is_active' => true,
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'customer_name' => 'Recent Buyer',
        'phone' => '9876543210',
        'address' => '12 Marine Lines',
        'city' => 'Mumbai',
        'state' => 'Maharashtra',
        'postal_code' => '400001',
        'subtotal' => 799,
        'delivery_fee' => 49,
        'total_amount' => 848,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'order_status' => 'pending',
    ]);
    $order->timestamps = false;
    $order->created_at = now()->subMinutes(15);
    $order->save();

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'product_name' => $product->name,
        'quantity' => 3,
        'price' => 799,
        'subtotal' => 2397,
    ]);

    $this->artisan('orders:cancel-unpaid')
        ->expectsOutputToContain('No unpaid expired orders found.')
        ->assertSuccessful();

    $order->refresh();
    $variant->refresh();

    expect($order->order_status)->toBe('pending')
        ->and($order->payment_status)->toBe('pending')
        ->and($variant->stock_qty)->toBe(15);
});

test('does not cancel successfully paid orders even if older than 30 minutes', function () {
    $user = User::factory()->create();

    $category = Category::create([
        'name' => 'Books',
        'slug' => 'books',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Laravel Mastery',
        'slug' => 'laravel-mastery',
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'sku' => 'BK-LM-HARD',
        'actual_price' => 1200,
        'discounted_price' => 999,
        'stock_qty' => 20,
        'is_active' => true,
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'customer_name' => 'Paid Customer',
        'phone' => '9876543210',
        'address' => '78 Lake View',
        'city' => 'Pune',
        'state' => 'Maharashtra',
        'postal_code' => '411001',
        'subtotal' => 999,
        'delivery_fee' => 0,
        'total_amount' => 999,
        'payment_method' => 'stripe',
        'payment_status' => 'paid',
        'order_status' => 'pending',
    ]);
    $order->timestamps = false;
    $order->created_at = now()->subMinutes(60);
    $order->save();

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'product_name' => $product->name,
        'quantity' => 1,
        'price' => 999,
        'subtotal' => 999,
    ]);

    $this->artisan('orders:cancel-unpaid')
        ->expectsOutputToContain('No unpaid expired orders found.')
        ->assertSuccessful();

    $order->refresh();
    $variant->refresh();

    expect($order->order_status)->toBe('pending')
        ->and($order->payment_status)->toBe('paid')
        ->and($variant->stock_qty)->toBe(20);
});

test('does not restore stock more than once if order is already cancelled', function () {
    $user = User::factory()->create();

    $category = Category::create([
        'name' => 'Groceries',
        'slug' => 'groceries',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Organic Honey',
        'slug' => 'organic-honey',
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'sku' => 'HNY-500G',
        'actual_price' => 500,
        'discounted_price' => 450,
        'stock_qty' => 8,
        'is_active' => true,
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'customer_name' => 'Cancelled Buyer',
        'phone' => '9876543210',
        'address' => '90 High Street',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'postal_code' => '380001',
        'subtotal' => 450,
        'delivery_fee' => 49,
        'total_amount' => 499,
        'payment_method' => 'razorpay',
        'payment_status' => 'pending',
        'order_status' => 'cancelled',
    ]);
    $order->timestamps = false;
    $order->created_at = now()->subMinutes(50);
    $order->save();

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'product_name' => $product->name,
        'quantity' => 2,
        'price' => 450,
        'subtotal' => 900,
    ]);

    $this->artisan('orders:cancel-unpaid')
        ->assertSuccessful();

    $variant->refresh();
    expect($variant->stock_qty)->toBe(8);
});

test('running the command multiple times does not restore stock repeatedly', function () {
    $user = User::factory()->create();

    $category = Category::create([
        'name' => 'Fitness',
        'slug' => 'fitness',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Dumbbells Set',
        'slug' => 'dumbbells-set',
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'sku' => 'DB-10KG',
        'actual_price' => 2500,
        'discounted_price' => 2200,
        'stock_qty' => 5,
        'is_active' => true,
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'customer_name' => 'Gym Guy',
        'phone' => '9876543210',
        'address' => '33 Workout Lane',
        'city' => 'Delhi',
        'state' => 'Delhi',
        'postal_code' => '110001',
        'subtotal' => 2200,
        'delivery_fee' => 0,
        'total_amount' => 2200,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'order_status' => 'pending',
    ]);
    $order->timestamps = false;
    $order->created_at = now()->subMinutes(40);
    $order->save();

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'product_name' => $product->name,
        'quantity' => 2,
        'price' => 2200,
        'subtotal' => 4400,
    ]);

    // First run cancels and restores stock from 5 to 7
    $this->artisan('orders:cancel-unpaid')->assertSuccessful();
    expect($variant->fresh()->stock_qty)->toBe(7);

    // Second run must find nothing and NOT restore stock again
    $this->artisan('orders:cancel-unpaid')
        ->expectsOutputToContain('No unpaid expired orders found.')
        ->assertSuccessful();

    expect($variant->fresh()->stock_qty)->toBe(7);
});

test('handles multi-item orders with different variants and restores all stock quantities', function () {
    $user = User::factory()->create();

    $category = Category::create([
        'name' => 'Stationery',
        'slug' => 'stationery',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Gel Pens',
        'slug' => 'gel-pens',
        'is_active' => true,
    ]);

    $variantA = ProductVariant::create([
        'product_id' => $product->id,
        'sku' => 'PEN-BLUE',
        'actual_price' => 50,
        'discounted_price' => 40,
        'stock_qty' => 10,
        'is_active' => true,
    ]);

    $variantB = ProductVariant::create([
        'product_id' => $product->id,
        'sku' => 'PEN-BLACK',
        'actual_price' => 50,
        'discounted_price' => 40,
        'stock_qty' => 20,
        'is_active' => true,
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'customer_name' => 'Multi Buyer',
        'phone' => '9876543210',
        'address' => '22 College Road',
        'city' => 'Kolkata',
        'state' => 'West Bengal',
        'postal_code' => '700001',
        'subtotal' => 360,
        'delivery_fee' => 49,
        'total_amount' => 409,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'order_status' => 'pending',
    ]);
    $order->timestamps = false;
    $order->created_at = now()->subMinutes(45);
    $order->save();

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'variant_id' => $variantA->id,
        'product_name' => 'Gel Pen Blue',
        'quantity' => 4,
        'price' => 40,
        'subtotal' => 160,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'variant_id' => $variantB->id,
        'product_name' => 'Gel Pen Black',
        'quantity' => 5,
        'price' => 40,
        'subtotal' => 200,
    ]);

    $this->artisan('orders:cancel-unpaid')->assertSuccessful();

    $order->refresh();
    $variantA->refresh();
    $variantB->refresh();

    expect($order->order_status)->toBe('cancelled')
        ->and($order->payment_status)->toBe('cancelled')
        ->and($variantA->stock_qty)->toBe(14) // 10 + 4
        ->and($variantB->stock_qty)->toBe(25); // 20 + 5
});

test('accepts custom --minutes threshold option', function () {
    $user = User::factory()->create();

    $category = Category::create([
        'name' => 'Tools',
        'slug' => 'tools',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Screwdriver Set',
        'slug' => 'screwdriver-set',
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'sku' => 'TOOL-SD',
        'actual_price' => 600,
        'discounted_price' => 500,
        'stock_qty' => 6,
        'is_active' => true,
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'customer_name' => 'Quick Buyer',
        'phone' => '9876543210',
        'address' => '10 Industrial Area',
        'city' => 'Chandigarh',
        'state' => 'Punjab',
        'postal_code' => '160001',
        'subtotal' => 500,
        'delivery_fee' => 0,
        'total_amount' => 500,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'order_status' => 'pending',
    ]);
    $order->timestamps = false;
    $order->created_at = now()->subMinutes(12);
    $order->save();

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'product_name' => $product->name,
        'quantity' => 2,
        'price' => 500,
        'subtotal' => 1000,
    ]);

    // Running with default 30 minutes does not cancel
    $this->artisan('orders:cancel-unpaid')
        ->expectsOutputToContain('No unpaid expired orders found.')
        ->assertSuccessful();
    expect($variant->fresh()->stock_qty)->toBe(6);

    // Running with --minutes=10 cancels it
    $this->artisan('orders:cancel-unpaid --minutes=10')
        ->expectsOutputToContain('Found 1 unpaid order(s) eligible for cancellation.')
        ->assertSuccessful();

    $order->refresh();
    $variant->refresh();

    expect($order->order_status)->toBe('cancelled')
        ->and($variant->stock_qty)->toBe(8);
});

test('logs every automatically cancelled order with order ID and cancellation time', function () {
    Log::spy();

    $user = User::factory()->create();

    $category = Category::create([
        'name' => 'Logging Category',
        'slug' => 'logging-category',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Smart Watch',
        'slug' => 'smart-watch',
        'is_active' => true,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'sku' => 'SW-BLK',
        'actual_price' => 3000,
        'discounted_price' => 2500,
        'stock_qty' => 10,
        'is_active' => true,
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'customer_name' => 'Log Inspector',
        'phone' => '9876543210',
        'address' => '77 Audit Street',
        'city' => 'Jaipur',
        'state' => 'Rajasthan',
        'postal_code' => '302001',
        'subtotal' => 2500,
        'delivery_fee' => 0,
        'total_amount' => 2500,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'order_status' => 'pending',
    ]);
    $order->timestamps = false;
    $order->created_at = now()->subMinutes(45);
    $order->save();

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'product_name' => $product->name,
        'quantity' => 1,
        'price' => 2500,
        'subtotal' => 2500,
    ]);

    $this->artisan('orders:cancel-unpaid')->assertSuccessful();

    Log::shouldHaveReceived('info')->withArgs(function (string $message, array $context) use ($order) {
        return str_contains($message, "Order #{$order->id} automatically cancelled")
            && isset($context['order_id'])
            && (int) $context['order_id'] === $order->id
            && isset($context['cancellation_time'])
            && ! empty($context['cancellation_time']);
    });
});

test('scheduler registers orders:cancel-unpaid to run every minute', function () {
    /** @var Schedule $schedule */
    $schedule = app(Schedule::class);

    $scheduledEvents = collect($schedule->events());

    $matchedEvent = $scheduledEvents->first(function (Event $event) {
        return str_contains((string) $event->command, 'orders:cancel-unpaid');
    });

    expect($matchedEvent)->not->toBeNull()
        ->and($matchedEvent->expression)->toBe('* * * * *');
});
