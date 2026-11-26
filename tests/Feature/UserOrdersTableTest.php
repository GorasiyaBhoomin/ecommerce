<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::create([
        'id' => 1,
        'name' => 'Hiral Patel',
        'email' => 'hiral@example.com',
        'password' => Hash::make('password'),
        'role' => 'user',
    ]);

    $category = Category::create([
        'name' => 'Electronics',
        'slug' => 'electronics',
        'is_active' => true,
    ]);

    $p1 = Product::create([
        'category_id' => $category->id,
        'name' => 'Sony Wireless Headphones',
        'slug' => 'sony-headphones',
        'sku' => 'SONY-01',
        'is_active' => true,
    ]);
    $v1 = $p1->variants()->create([
        'sku' => 'SONY-01-V',
        'actual_price' => 1200,
        'discounted_price' => 1200,
        'stock_qty' => 10,
        'is_active' => true,
    ]);

    $p2 = Product::create([
        'category_id' => $category->id,
        'name' => 'Mechanical Gaming Keyboard',
        'slug' => 'gaming-keyboard',
        'sku' => 'KEY-01',
        'is_active' => true,
    ]);
    $v2 = $p2->variants()->create([
        'sku' => 'KEY-01-V',
        'actual_price' => 3500,
        'discounted_price' => 3500,
        'stock_qty' => 5,
        'is_active' => true,
    ]);

    $p3 = Product::create([
        'category_id' => $category->id,
        'name' => 'Cotton Crewneck T-Shirt',
        'slug' => 'cotton-tshirt',
        'sku' => 'TSH-01',
        'is_active' => true,
    ]);
    $v3 = $p3->variants()->create([
        'sku' => 'TSH-01-V',
        'actual_price' => 450,
        'discounted_price' => 450,
        'stock_qty' => 20,
        'is_active' => true,
    ]);

    // Create 3 orders with distinct properties
    $this->order1 = Order::create([
        'user_id' => $this->user->id,
        'customer_name' => 'Hiral Patel',
        'phone' => '9876543210',
        'address' => '123 SG Highway',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'postal_code' => '380015',
        'subtotal' => 1200,
        'delivery_fee' => 0,
        'total_amount' => 1200,
        'payment_method' => 'cod',
        'payment_status' => 'pending',
        'order_status' => 'processing',
        'created_at' => now()->subDays(2),
    ]);

    OrderItem::create([
        'order_id' => $this->order1->id,
        'product_id' => $p1->id,
        'variant_id' => $v1->id,
        'product_name' => 'Sony Wireless Headphones',
        'quantity' => 1,
        'price' => 1200,
        'subtotal' => 1200,
    ]);

    $this->order2 = Order::create([
        'user_id' => $this->user->id,
        'customer_name' => 'Hiral Patel',
        'phone' => '9876543210',
        'address' => '123 SG Highway',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'postal_code' => '380015',
        'subtotal' => 3500,
        'delivery_fee' => 50,
        'total_amount' => 3550,
        'payment_method' => 'razorpay',
        'payment_id' => 'pay_Rzp123456',
        'payment_status' => 'completed',
        'order_status' => 'delivered',
        'created_at' => now()->subDay(),
    ]);

    OrderItem::create([
        'order_id' => $this->order2->id,
        'product_id' => $p2->id,
        'variant_id' => $v2->id,
        'product_name' => 'Mechanical Gaming Keyboard',
        'quantity' => 1,
        'price' => 3500,
        'subtotal' => 3500,
    ]);

    $this->order3 = Order::create([
        'user_id' => $this->user->id,
        'customer_name' => 'Hiral Patel',
        'phone' => '9876543210',
        'address' => '123 SG Highway',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'postal_code' => '380015',
        'subtotal' => 450,
        'delivery_fee' => 0,
        'total_amount' => 450,
        'payment_method' => 'stripe',
        'payment_id' => 'ch_Stripe987',
        'payment_status' => 'refunded',
        'order_status' => 'cancelled',
        'created_at' => now(),
    ]);

    OrderItem::create([
        'order_id' => $this->order3->id,
        'product_id' => $p3->id,
        'variant_id' => $v3->id,
        'product_name' => 'Cotton Crewneck T-Shirt',
        'quantity' => 1,
        'price' => 450,
        'subtotal' => 450,
    ]);
});

test('guest is redirected to login when visiting myorders', function () {
    $response = $this->get('/myorders');
    $response->assertRedirect('/login');
});

test('user can view my orders page with cards view by default', function () {
    $response = $this->withSession(['user_id' => $this->user->id])->get('/myorders');

    $response->assertStatus(200);
    $response->assertSee('My Orders & Tracking');
    $response->assertSee("Order #SK-{$this->order1->id}");
    $response->assertSee("Order #SK-{$this->order2->id}");
    $response->assertSee("Order #SK-{$this->order3->id}");
    $response->assertSee('Tracker Cards');
    $response->assertSee('Orders Table');
});

test('user can view my orders in dedicated table view mode', function () {
    $response = $this->withSession(['user_id' => $this->user->id])->get('/myorders?view=table');

    $response->assertStatus(200);
    $response->assertSee('products-table');
    $response->assertSee('Order #');
    $response->assertSee('Products Ordered');
    $response->assertSee('Total Amount');
    $response->assertSee('Live Status');
    $response->assertSee('Sony Wireless Headphones');
    $response->assertSee('Mechanical Gaming Keyboard');
    $response->assertSee('Cotton Crewneck T-Shirt');
});

test('user can search orders by product name', function () {
    $response = $this->withSession(['user_id' => $this->user->id])
        ->get('/myorders?view=table&search=Mechanical');

    $response->assertStatus(200);
    $response->assertSee('Mechanical Gaming Keyboard');
    $response->assertDontSee('Sony Wireless Headphones');
    $response->assertDontSee('Cotton Crewneck T-Shirt');
});

test('user can search orders by order id', function () {
    $response = $this->withSession(['user_id' => $this->user->id])
        ->get("/myorders?view=table&search=SK-{$this->order1->id}");

    $response->assertStatus(200);
    $response->assertSee('Sony Wireless Headphones');
    $response->assertDontSee('Mechanical Gaming Keyboard');
});

test('user can filter orders by status', function () {
    $response = $this->withSession(['user_id' => $this->user->id])
        ->get('/myorders?view=table&status=delivered');

    $response->assertStatus(200);
    $response->assertSee('Mechanical Gaming Keyboard');
    $response->assertDontSee('Sony Wireless Headphones');
    $response->assertDontSee('Cotton Crewneck T-Shirt');
});

test('user can sort orders by total amount descending and ascending', function () {
    $responseDesc = $this->withSession(['user_id' => $this->user->id])
        ->get('/myorders?view=table&sort_by=total_amount&sort_dir=desc');

    $responseDesc->assertStatus(200);
    // Highest is 3550 (order2), then 1200 (order1), then 450 (order3)
    $pos2 = strpos($responseDesc->getContent(), "#SK-{$this->order2->id}");
    $pos1 = strpos($responseDesc->getContent(), "#SK-{$this->order1->id}");
    $pos3 = strpos($responseDesc->getContent(), "#SK-{$this->order3->id}");
    expect($pos2)->toBeLessThan($pos1)
        ->and($pos1)->toBeLessThan($pos3);

    $responseAsc = $this->withSession(['user_id' => $this->user->id])
        ->get('/myorders?view=table&sort_by=total_amount&sort_dir=asc');

    $responseAsc->assertStatus(200);
    $posAsc3 = strpos($responseAsc->getContent(), "#SK-{$this->order3->id}");
    $posAsc1 = strpos($responseAsc->getContent(), "#SK-{$this->order1->id}");
    $posAsc2 = strpos($responseAsc->getContent(), "#SK-{$this->order2->id}");
    expect($posAsc3)->toBeLessThan($posAsc1)
        ->and($posAsc1)->toBeLessThan($posAsc2);
});

test('user sees helpful empty state when no orders match criteria', function () {
    $response = $this->withSession(['user_id' => $this->user->id])
        ->get('/myorders?view=table&search=NonExistentGadgetXYZ');

    $response->assertStatus(200);
    $response->assertSee('No orders found');
    $response->assertSee('Reset Filters');
});
