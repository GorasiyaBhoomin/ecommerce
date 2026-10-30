<?php

use App\Models\cart_items;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\VariantGroup;
use App\Models\VariantValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('category api returns all categories and category-wise products', function () {
    $category = Category::create([
        'name' => 'Smartphones',
        'slug' => 'smartphones',
        'display_order' => 1,
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Pixel 9',
        'slug' => 'pixel-9',
        'is_active' => true,
    ]);

    $product->variants()->create([
        'sku' => 'PIXEL-9-128',
        'actual_price' => 79999,
        'discounted_price' => 74999,
        'stock_qty' => 10,
        'is_active' => true,
    ]);

    // 1. Get all categories
    $catResponse = $this->getJson('/api/categories');
    $catResponse->assertOk()
        ->assertJson(['status' => true])
        ->assertJsonFragment(['name' => 'Smartphones', 'products_count' => 1]);

    // 2. Get single category
    $singleCatResponse = $this->getJson('/api/categories/smartphones');
    $singleCatResponse->assertOk()
        ->assertJsonPath('data.category.slug', 'smartphones');

    // 3. Get category-wise products
    $catProductsResponse = $this->getJson('/api/categories/smartphones/products');
    $catProductsResponse->assertOk()
        ->assertJsonPath('data.total_products', 1)
        ->assertJsonFragment(['name' => 'Pixel 9', 'from_price' => 74999]);
});

test('product apis return details, images, specifications, and variants', function () {
    $cat = Category::create(['name' => 'Laptops', 'slug' => 'laptops', 'is_active' => true]);
    $groupRam = VariantGroup::create(['name' => 'RAM', 'label' => 'RAM', 'is_active' => true]);
    $valRam = VariantValue::create(['variant_group_id' => $groupRam->id, 'value' => '16GB']);

    $product = Product::create([
        'category_id' => $cat->id,
        'name' => 'NovaBook Pro',
        'slug' => 'novabook-pro',
        'sku' => 'NOVA-PRO',
        'description' => 'Flagship laptop',
        'is_active' => true,
    ]);

    $product->images()->create([
        'path' => 'uploads/products/laptop-front.webp',
        'position' => 1,
    ]);

    $product->specifications()->create([
        'label' => 'Processor',
        'value' => 'Intel Core Ultra 7',
        'position' => 1,
    ]);

    $variant = $product->variants()->create([
        'sku' => 'NOVA-16-512',
        'actual_price' => 95000,
        'discounted_price' => 89999,
        'stock_qty' => 8,
        'is_active' => true,
    ]);
    $variant->values()->attach($valRam->id);

    // 1. Product details
    $detailResponse = $this->getJson('/api/products/novabook-pro');
    $detailResponse->assertOk()
        ->assertJsonPath('data.product.name', 'NovaBook Pro')
        ->assertJsonPath('data.product.total_stock', 8)
        ->assertJsonPath('data.product.is_in_stock', true);

    // 2. Product images
    $imagesResponse = $this->getJson('/api/products/novabook-pro/images');
    $imagesResponse->assertOk()
        ->assertJsonCount(1, 'data.images');

    // 3. Product specifications
    $specsResponse = $this->getJson('/api/products/novabook-pro/specifications');
    $specsResponse->assertOk()
        ->assertJsonFragment(['label' => 'Processor', 'value' => 'Intel Core Ultra 7']);

    // 4. Product variants
    $variantsResponse = $this->getJson('/api/products/novabook-pro/variants');
    $variantsResponse->assertOk()
        ->assertJsonFragment(['sku' => 'NOVA-16-512', 'effective_price' => 89999]);
});

test('product filter api supports category, search, price range, stock, and sorting simultaneously', function () {
    $catAudio = Category::create(['name' => 'Audio', 'slug' => 'audio', 'is_active' => true]);
    $catGadgets = Category::create(['name' => 'Gadgets', 'slug' => 'gadgets', 'is_active' => true]);

    $p1 = Product::create(['category_id' => $catAudio->id, 'name' => 'Noise Cancelling Headphones', 'slug' => 'nc-headphones', 'is_active' => true]);
    $p1->variants()->create(['sku' => 'NC-1', 'actual_price' => 4000, 'discounted_price' => 3500, 'stock_qty' => 5, 'is_active' => true]);

    $p2 = Product::create(['category_id' => $catAudio->id, 'name' => 'Budget Earbuds', 'slug' => 'budget-earbuds', 'is_active' => true]);
    $p2->variants()->create(['sku' => 'BUD-1', 'actual_price' => 1200, 'discounted_price' => 999, 'stock_qty' => 0, 'is_active' => true]);

    $p3 = Product::create(['category_id' => $catGadgets->id, 'name' => 'Smart Audio Speaker', 'slug' => 'smart-speaker', 'is_active' => true]);
    $p3->variants()->create(['sku' => 'SPK-1', 'actual_price' => 3000, 'stock_qty' => 10, 'is_active' => true]);

    // Filter: category=audio + in_stock=1 + min_price=2000
    $filterResponse = $this->getJson('/api/products?category=audio&in_stock=1&min_price=2000');
    $filterResponse->assertOk()
        ->assertJsonFragment(['name' => 'Noise Cancelling Headphones'])
        ->assertJsonMissing(['name' => 'Budget Earbuds'])
        ->assertJsonMissing(['name' => 'Smart Audio Speaker']);

    // Search: "Earbuds"
    $searchResponse = $this->getJson('/api/products?search=Earbuds');
    $searchResponse->assertOk()
        ->assertJsonFragment(['name' => 'Budget Earbuds'])
        ->assertJsonMissing(['name' => 'Noise Cancelling Headphones']);
});

test('auth and profile apis allow registration, login, profile updates and logout', function () {
    // 1. Registration
    $reg = $this->postJson('/api/register', [
        'name' => 'Kunal Shah',
        'email' => 'kunal@example.com',
        'password' => 'pass12345',
        'password_confirmation' => 'pass12345',
        'phone' => '9898989898',
        'address' => '101 Tech Hub',
        'city' => 'Bengaluru',
        'state' => 'Karnataka',
        'postal_code' => '560001',
    ]);
    $reg->assertCreated()
        ->assertJsonStructure(['data' => ['user', 'token']]);

    $token = $reg->json('data.token');

    // 2. Profile read
    $profile = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/profile');
    $profile->assertOk()
        ->assertJsonPath('data.user.email', 'kunal@example.com');

    // 3. Profile update
    $update = $this->withHeader('Authorization', 'Bearer '.$token)->putJson('/api/profile', [
        'name' => 'Kunal V. Shah',
        'email' => 'kunal@example.com',
        'phone' => '9898989899',
    ]);
    $update->assertOk()
        ->assertJsonPath('data.user.name', 'Kunal V. Shah');

    // 4. Address update
    $addr = $this->withHeader('Authorization', 'Bearer '.$token)->putJson('/api/profile/address', [
        'address' => '202 Silicon Valley',
        'city' => 'Bengaluru',
        'state' => 'Karnataka',
        'postal_code' => '560100',
    ]);
    $addr->assertOk()
        ->assertJsonPath('data.shipping_address.postal_code', '560100');

    // 5. Logout
    $logout = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/logout');
    $logout->assertOk();
});

test('cart apis validate stock, calculate delivery fees and manage quantities', function () {
    $user = User::create([
        'name' => 'Buyer',
        'email' => 'buyer@example.com',
        'password' => Hash::make('password123'),
        'phone' => '9123456780',
        'role' => 'user',
    ]);
    $token = $user->createToken('test')->plainTextToken;

    $cat = Category::create(['name' => 'Wear', 'slug' => 'wear', 'is_active' => true]);
    $prod = Product::create(['category_id' => $cat->id, 'name' => 'T-Shirt', 'slug' => 't-shirt', 'is_active' => true]);
    $variant = $prod->variants()->create([
        'sku' => 'TSHIRT-L',
        'actual_price' => 500,
        'discounted_price' => 300,
        'stock_qty' => 5,
        'is_active' => true,
    ]);

    // 1. Add 1 item (300 <= 499 => delivery_fee 49)
    $add = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/cart', [
        'product_id' => $prod->id,
        'variant_id' => $variant->id,
        'quantity' => 1,
    ]);
    $add->assertCreated();

    $cartId = $add->json('data.cart_item.id');

    $view = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/cart');
    $view->assertOk()
        ->assertJsonPath('data.summary.subtotal', 300)
        ->assertJsonPath('data.summary.delivery_fee', 49)
        ->assertJsonPath('data.summary.total', 349);

    // 2. Reject quantity exceeding stock (available 5, trying 10)
    $over = $this->withHeader('Authorization', 'Bearer '.$token)->putJson('/api/cart/'.$cartId, [
        'quantity' => 10,
    ]);
    $over->assertStatus(422)
        ->assertJson(['status' => false]);

    // 3. Update to 2 items (300 * 2 = 600 > 499 => delivery_fee 0)
    $update = $this->withHeader('Authorization', 'Bearer '.$token)->putJson('/api/cart/'.$cartId, [
        'quantity' => 2,
    ]);
    $update->assertOk();

    $view2 = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/cart');
    $view2->assertOk()
        ->assertJsonPath('data.summary.subtotal', 600)
        ->assertJsonPath('data.summary.delivery_fee', 0)
        ->assertJsonPath('data.summary.total', 600);

    // 4. Remove item
    $remove = $this->withHeader('Authorization', 'Bearer '.$token)->deleteJson('/api/cart/'.$cartId);
    $remove->assertOk();
    expect(cart_items::where('user_id', $user->id)->count())->toBe(0);
});

test('order apis handle placing orders, items, status tracking, cancellation and invoices', function () {
    $user = User::create([
        'name' => 'Order Tester',
        'email' => 'ordertester@example.com',
        'password' => Hash::make('password123'),
        'phone' => '9900990099',
        'role' => 'user',
    ]);
    $token = $user->createToken('test')->plainTextToken;

    $cat = Category::create(['name' => 'Tech', 'slug' => 'tech', 'is_active' => true]);
    $prod = Product::create(['category_id' => $cat->id, 'name' => 'Smart Watch', 'slug' => 'smart-watch', 'is_active' => true]);
    $variant = $prod->variants()->create([
        'sku' => 'SW-1',
        'actual_price' => 3000,
        'discounted_price' => 2500,
        'stock_qty' => 10,
        'is_active' => true,
    ]);

    // 1. Place COD order
    $place = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/orders', [
        'customer_name' => 'Order Tester',
        'phone' => '9900990099',
        'address' => 'Plot 45, Ring Road',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'postal_code' => '395007',
        'payment_method' => 'cod',
        'items' => [
            [
                'product_id' => $prod->id,
                'variant_id' => $variant->id,
                'quantity' => 1,
            ],
        ],
    ]);
    $place->assertCreated();
    $orderId = $place->json('data.order.id');

    // Stock should be 9
    expect($variant->fresh()->stock_qty)->toBe(9);

    // 2. Get order items
    $itemsResponse = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/orders/'.$orderId.'/items');
    $itemsResponse->assertOk()
        ->assertJsonPath('data.items_count', 1);

    // 3. Track order
    $trackResponse = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/orders/'.$orderId.'/track');
    $trackResponse->assertOk()
        ->assertJsonPath('data.order_id', $orderId)
        ->assertJsonPath('data.order_status', 'pending');

    // 4. Cancel order and verify stock restored to 10
    $cancelResponse = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/orders/'.$orderId.'/cancel');
    $cancelResponse->assertOk()
        ->assertJsonPath('data.order.order_status', 'cancelled');

    expect($variant->fresh()->stock_qty)->toBe(10);
});

test('return and exchange apis validate delivered status and requested replacement', function () {
    $user = User::create([
        'name' => 'Return Tester',
        'email' => 'returntester@example.com',
        'password' => Hash::make('password123'),
        'phone' => '9111111111',
        'role' => 'user',
    ]);
    $token = $user->createToken('test')->plainTextToken;

    $cat = Category::create(['name' => 'Apparel', 'slug' => 'apparel', 'is_active' => true]);
    $prod = Product::create(['category_id' => $cat->id, 'name' => 'Jacket', 'slug' => 'jacket', 'is_active' => true]);
    $variantM = $prod->variants()->create(['sku' => 'JKT-M', 'actual_price' => 2000, 'stock_qty' => 5, 'is_active' => true]);
    $variantL = $prod->variants()->create(['sku' => 'JKT-L', 'actual_price' => 2000, 'stock_qty' => 4, 'is_active' => true]);

    $order = Order::create([
        'user_id' => $user->id,
        'customer_name' => 'Return Tester',
        'phone' => '9111111111',
        'address' => '55 Market Yard',
        'city' => 'Rajkot',
        'state' => 'Gujarat',
        'postal_code' => '360001',
        'subtotal' => 2000,
        'delivery_fee' => 0,
        'total_amount' => 2000,
        'payment_method' => 'cod',
        'payment_status' => 'paid',
        'order_status' => 'delivered',
    ]);

    // 1. Check eligibility
    $statusCheck = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/orders/'.$order->id.'/return-exchange-status');
    $statusCheck->assertOk()
        ->assertJsonPath('data.is_eligible_for_return', true)
        ->assertJsonPath('data.is_eligible_for_exchange', true);

    // 2. Submit exchange request for size L
    $exchange = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/orders/'.$order->id.'/exchange', [
        'reason' => 'Size too small, need large',
        'requested_variant_id' => $variantL->id,
    ]);
    $exchange->assertOk()
        ->assertJsonPath('data.order_status', 'exchange_requested')
        ->assertJsonPath('data.requested_variant.sku', 'JKT-L');
});

test('payment apis return supported methods and handle payment creation', function () {
    $user = User::create([
        'name' => 'Payment Tester',
        'email' => 'paymenttester@example.com',
        'password' => Hash::make('password123'),
        'phone' => '9222222222',
        'role' => 'user',
    ]);
    $token = $user->createToken('test')->plainTextToken;

    // 1. Get payment methods
    $methods = $this->getJson('/api/payment/methods');
    $methods->assertOk()
        ->assertJsonPath('data.currency', 'INR')
        ->assertJsonFragment(['id' => 'cod']);

    // 2. Create payment request for an order
    $order = Order::create([
        'user_id' => $user->id,
        'customer_name' => 'Payment Tester',
        'phone' => '9222222222',
        'address' => '33 Station Road',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'postal_code' => '380002',
        'subtotal' => 1500,
        'delivery_fee' => 0,
        'total_amount' => 1500,
        'payment_method' => 'cod',
        'payment_status' => 'pending',
        'order_status' => 'pending',
    ]);

    $createPayment = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/payment/create', [
        'order_id' => $order->id,
        'payment_method' => 'cod',
    ]);
    $createPayment->assertOk()
        ->assertJsonPath('data.payment_method', 'cod');
});

test('unauthenticated post request to api orders returns 401 json without crashing', function () {
    $response = $this->post('/api/orders', ['email' => 'test@example.com']);
    $response->assertStatus(401)
        ->assertJson([
            'status' => false,
            'message' => 'Unauthenticated. Please provide a valid Bearer token in the Authorization header.',
        ]);
});
