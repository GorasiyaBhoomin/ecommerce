<?php

use App\Mail\ResetPasswordMail;
use App\Models\cart_items;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\VariantGroup;
use App\Models\VariantValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

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

test('admin panel web views support search and filter query parameters', function () {
    $admin = User::create([
        'name' => 'Admin Panel Tester',
        'email' => 'admintester@example.com',
        'password' => Hash::make('password123'),
        'phone' => '9111111111',
        'role' => 'admin',
    ]);

    $category = Category::create([
        'name' => 'Gadgets',
        'slug' => 'gadgets',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Wireless Mouse Pro',
        'slug' => 'wireless-mouse-pro',
        'sku' => 'WMP-01',
        'is_active' => true,
    ]);

    $product->variants()->create([
        'sku' => 'WMP-01-BLK',
        'actual_price' => 1200,
        'stock_qty' => 15,
        'is_active' => true,
    ]);

    $order = Order::create([
        'user_id' => $admin->id,
        'customer_name' => 'John Customer',
        'phone' => '9888888888',
        'address' => '45 Tech Park',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'postal_code' => '395007',
        'subtotal' => 1200,
        'delivery_fee' => 0,
        'total_amount' => 1200,
        'payment_method' => 'cod',
        'payment_status' => 'pending',
        'order_status' => 'pending',
    ]);

    $variantGroup = VariantGroup::create([
        'name' => 'Color',
        'label' => 'Color Choice',
        'is_active' => true,
    ]);

    // 1. Admin products search & stock filter
    $prodSearch = $this->withSession(['user_id' => $admin->id])
        ->get('/adminproducts?search=Wireless&stock=in_stock');
    $prodSearch->assertOk()
        ->assertSee('Wireless Mouse Pro');

    // 2. Admin orders search
    $orderSearch = $this->withSession(['user_id' => $admin->id])
        ->get('/adminorders?search=John');
    $orderSearch->assertOk()
        ->assertSee('John Customer');

    // 3. Admin users search
    $userSearch = $this->withSession(['user_id' => $admin->id])
        ->get('/adminusers?search=Admin');
    $userSearch->assertOk()
        ->assertSee('Admin Panel Tester');

    // 4. Admin categories search
    $catSearch = $this->withSession(['user_id' => $admin->id])
        ->get('/admincategories?search=Gadgets');
    $catSearch->assertOk()
        ->assertSee('Gadgets');

    // 5. Admin variants search
    $varSearch = $this->withSession(['user_id' => $admin->id])
        ->get('/adminvariants?search=Color');
    $varSearch->assertOk()
        ->assertSee('Color');

    // 6. Admin bulk import view and sample dummy CSV download
    $importView = $this->withSession(['user_id' => $admin->id])
        ->get('/adminimportproducts');
    $importView->assertOk()
        ->assertSee('Dummy CSV File For Upload')
        ->assertSee('Download Dummy CSV');

    $sampleCsv = $this->withSession(['user_id' => $admin->id])
        ->get('/adminimportproducts/sample?category_id='.$category->id);
    $sampleCsv->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($sampleCsv->streamedContent())->toContain('Galaxy Phone Neo')
        ->toContain('actual_price');

    // 7. CSV upload stores images into public/uploads/products folder
    Http::fake([
        'https://example.com/*' => Http::response('fake-binary-image', 200, ['Content-Type' => 'image/jpeg']),
    ]);

    $variantGroup->values()->create(['value' => 'Red', 'sku_suffix' => 'RED']);

    $csvContent = implode("\n", [
        'name,sku,actual_price,Color,images',
        'Camera Drone,DRN-01,9999,Red,https://example.com/drone1.jpg;https://example.com/drone2.jpg;https://example.com/drone3.jpg',
    ]);

    $uploadedFile = UploadedFile::fake()->createWithContent('products.csv', $csvContent);

    $uploadResp = $this->withSession(['user_id' => $admin->id])
        ->post('/adminimportproducts', [
            'category_id' => $category->id,
            'csv_file' => $uploadedFile,
        ]);

    $uploadResp->assertRedirect(route('admin.products.index'));

    $importedProduct = Product::where('name', 'Camera Drone')->first();
    expect($importedProduct)->not->toBeNull();
    expect($importedProduct->images)->toHaveCount(3);

    foreach ($importedProduct->images as $img) {
        expect($img->path)->toStartWith('uploads/products/');
        expect(file_exists(public_path($img->path)))->toBeTrue();
        if (file_exists(public_path($img->path))) {
            unlink(public_path($img->path));
        }
    }
});

test('forgot password and reset password web and api flows work with email and token', function () {
    Mail::fake();

    $user = User::create([
        'name' => 'Forgot Tester',
        'email' => 'forgotuser@example.com',
        'password' => Hash::make('oldpassword123'),
        'phone' => '9444444444',
        'role' => 'user',
    ]);

    // 1. Login page contains forgot password link
    $loginPage = $this->get('/login');
    $loginPage->assertOk()
        ->assertSee(route('password.request'));

    // 2. Forgot password page renders
    $forgotPage = $this->get('/forgot-password');
    $forgotPage->assertOk()
        ->assertSee('Forgot Password?');

    // 3. Post forgot password sends reset email & creates token
    $postForgot = $this->post('/forgot-password', [
        'email' => 'forgotuser@example.com',
    ]);
    $postForgot->assertSessionHas('success');

    $tokenRecord = DB::table('password_reset_tokens')
        ->where('email', 'forgotuser@example.com')
        ->first();
    expect($tokenRecord)->not->toBeNull();
    $token = $tokenRecord->token;

    Mail::assertSent(ResetPasswordMail::class, function ($mail) {
        return $mail->hasTo('forgotuser@example.com');
    });

    // 4. Reset password page opens with valid token
    $resetPage = $this->get("/reset-password/{$token}?email=forgotuser@example.com");
    $resetPage->assertOk()
        ->assertSee('Set New Password')
        ->assertSee('forgotuser@example.com');

    // 5. Submit new password updates user password & deletes token
    $resetSubmit = $this->post('/reset-password', [
        'token' => $token,
        'email' => 'forgotuser@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);
    $resetSubmit->assertRedirect(route('login'))
        ->assertSessionHas('success');

    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();

    $tokenAfter = DB::table('password_reset_tokens')
        ->where('email', 'forgotuser@example.com')
        ->first();
    expect($tokenAfter)->toBeNull();

    // 6. API forgot password & reset password
    $apiForgot = $this->postJson('/api/forgot-password', [
        'email' => 'forgotuser@example.com',
    ]);
    $apiForgot->assertOk()
        ->assertJson(['status' => true])
        ->assertJsonStructure(['data' => ['reset_token', 'reset_url']]);

    $apiToken = $apiForgot->json('data.reset_token');

    $apiReset = $this->postJson('/api/reset-password', [
        'token' => $apiToken,
        'email' => 'forgotuser@example.com',
        'password' => 'supersecret99',
        'password_confirmation' => 'supersecret99',
    ]);
    $apiReset->assertOk()
        ->assertJson(['status' => true]);

    $user->refresh();
    expect(Hash::check('supersecret99', $user->password))->toBeTrue();
});

test('user myorders, invoice pdf, and admin orders fetch payment_id or razorpayId according to payment method', function () {
    $user = User::create([
        'name' => 'Payment User',
        'email' => 'paymentuser@example.com',
        'password' => Hash::make('password123'),
        'phone' => '9888877777',
        'role' => 'user',
    ]);

    $admin = User::create([
        'name' => 'Admin Invoice Tester',
        'email' => 'admininvoicetester@example.com',
        'password' => Hash::make('password123'),
        'phone' => '9888866666',
        'role' => 'admin',
    ]);

    $category = Category::create([
        'name' => 'Laptops',
        'slug' => 'laptops-invoice',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'MacBook Pro M3',
        'slug' => 'macbook-pro-m3',
        'sku' => 'MBP-M3',
        'is_active' => true,
    ]);

    $variant = $product->variants()->create([
        'sku' => 'MBP-M3-SLV',
        'actual_price' => 199999,
        'stock_qty' => 10,
        'is_active' => true,
    ]);

    // 1. Razorpay Order
    $razorpayOrder = Order::create([
        'user_id' => $user->id,
        'customer_name' => 'Payment User',
        'phone' => '9888877777',
        'address' => '404 Tech Park',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'postal_code' => '395007',
        'subtotal' => 199999,
        'delivery_fee' => 0,
        'total_amount' => 199999,
        'payment_method' => 'razorpay',
        'payment_id' => 'pay_rzp_test_1001',
        'razorpay_payment_id' => 'pay_rzp_test_1001',
        'razorpay_order_id' => 'order_rzp_test_5001',
        'payment_status' => 'paid',
        'order_status' => 'processing',
    ]);

    $razorpayOrder->items()->create([
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'product_name' => 'MacBook Pro M3',
        'quantity' => 1,
        'price' => 199999,
        'subtotal' => 199999,
    ]);

    // 2. Stripe Order
    $stripeOrder = Order::create([
        'user_id' => $user->id,
        'customer_name' => 'Payment User',
        'phone' => '9888877777',
        'address' => '404 Tech Park',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'postal_code' => '395007',
        'subtotal' => 199999,
        'delivery_fee' => 0,
        'total_amount' => 199999,
        'payment_method' => 'stripe',
        'payment_id' => 'pi_stripe_test_2002',
        'stripe_payment_id' => 'pi_stripe_test_2002',
        'stripe_session_id' => 'cs_test_session_3003',
        'payment_status' => 'paid',
        'order_status' => 'shipped',
    ]);

    $stripeOrder->items()->create([
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'product_name' => 'MacBook Pro M3',
        'quantity' => 1,
        'price' => 199999,
        'subtotal' => 199999,
    ]);

    // 3. User My Orders View
    $myOrdersResponse = $this->withSession(['user_id' => $user->id])->get('/myorders');
    $myOrdersResponse->assertOk()
        ->assertSee('Razorpay ID: pay_rzp_test_1001', false)
        ->assertSee('order_rzp_test_5001')
        ->assertSee('Payment ID: pi_stripe_test_2002', false);

    // 4. Invoice HTML/PDF View Rendering Checks
    $pdfRazorpayHtml = view('pdf.invoice', ['order' => $razorpayOrder->load('items', 'user')])->render();
    expect($pdfRazorpayHtml)
        ->toContain('RAZORPAY (ONLINE)')
        ->toContain('Razorpay Payment ID')
        ->toContain('pay_rzp_test_1001')
        ->toContain('Razorpay Order ID')
        ->toContain('order_rzp_test_5001');

    $pdfStripeHtml = view('pdf.invoice', ['order' => $stripeOrder->load('items', 'user')])->render();
    expect($pdfStripeHtml)
        ->toContain('STRIPE (ONLINE)')
        ->toContain('Stripe Payment ID')
        ->toContain('pi_stripe_test_2002');

    // 5. Download Invoice Routes
    $userPdfDownload = $this->withSession(['user_id' => $user->id])
        ->get("/myorders/{$razorpayOrder->id}/invoice");
    $userPdfDownload->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    $adminPdfDownload = $this->withSession(['user_id' => $admin->id])
        ->get("/adminorderdetail/{$razorpayOrder->id}/invoice");
    $adminPdfDownload->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    // 6. Admin Orders List View
    $adminOrdersResponse = $this->withSession(['user_id' => $admin->id])->get('/adminorders');
    $adminOrdersResponse->assertOk()
        ->assertSee('pay_rzp_test_1001')
        ->assertSee('order_rzp_test_5001')
        ->assertSee('pi_stripe_test_2002');

    // 7. Admin Order Detail View
    $adminDetailResponse = $this->withSession(['user_id' => $admin->id])
        ->get("/adminorderdetail/{$razorpayOrder->id}");
    $adminDetailResponse->assertOk()
        ->assertSee('Razorpay (UPI / Cards / Net Banking)')
        ->assertSee('Razorpay Payment ID')
        ->assertSee('pay_rzp_test_1001')
        ->assertSee('Razorpay Order ID')
        ->assertSee('order_rzp_test_5001');

    // 8. Order API Invoice Data
    $token = $user->createToken('test-token')->plainTextToken;
    $apiInvoice = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson("/api/orders/{$razorpayOrder->id}/invoice-data");
    $apiInvoice->assertOk()
        ->assertJsonPath('data.pricing.payment_method', 'RAZORPAY')
        ->assertJsonPath('data.pricing.razorpay_payment_id', 'pay_rzp_test_1001')
        ->assertJsonPath('data.pricing.razorpay_order_id', 'order_rzp_test_5001');
});

test('storefront catalog paginates products to 6 per page and links to page 2', function () {
    $user = User::create([
        'name' => 'Catalog Tester',
        'email' => 'catalogtester@example.com',
        'password' => Hash::make('password123'),
        'phone' => '9555544444',
        'role' => 'user',
    ]);

    $category = Category::create([
        'name' => 'Smartphones',
        'slug' => 'smartphones',
        'is_active' => true,
    ]);

    // Create exactly 11 active products
    for ($i = 1; $i <= 11; $i++) {
        $pad = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
        $prod = Product::create([
            'category_id' => $category->id,
            'name' => "Phone Model {$pad}",
            'slug' => "phone-model-{$pad}",
            'sku' => "SKU-PH-{$pad}",
            'is_active' => true,
        ]);

        $prod->variants()->create([
            'sku' => "SKU-PH-{$pad}-V1",
            'actual_price' => 10000 + ($i * 500),
            'stock_qty' => 5,
            'is_active' => true,
        ]);
    }

    // Page 1: Should display 6 products out of 11
    $page1Response = $this->withSession(['user_id' => $user->id])->get('/index');
    $page1Response->assertOk()
        ->assertSee('Explore Store Catalog')
        ->assertSee('Showing 1-6 of 11 products')
        ->assertSee('phone-model-01')
        ->assertSee('phone-model-06')
        ->assertDontSee('phone-model-07')
        ->assertSee('page=2')
        ->assertSee('Next');

    // Page 2: Should display the remaining 5 products (7 to 11)
    $page2Response = $this->withSession(['user_id' => $user->id])->get('/index?page=2');
    $page2Response->assertOk()
        ->assertSee('Showing 7-11 of 11 products')
        ->assertSee('phone-model-07')
        ->assertSee('phone-model-11')
        ->assertDontSee('phone-model-06')
        ->assertSee('page=1')
        ->assertSee('Previous');
});

test('admin orders and admin products paginate to 6 items per page with page 2 navigation', function () {
    $admin = User::create([
        'name' => 'Admin Paginator',
        'email' => 'adminpaginator@example.com',
        'password' => Hash::make('password123'),
        'phone' => '9444433333',
        'role' => 'admin',
    ]);

    $category = Category::create([
        'name' => 'Tablets',
        'slug' => 'tablets',
        'is_active' => true,
    ]);

    // Create 11 products
    for ($i = 1; $i <= 11; $i++) {
        $pad = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
        $prod = Product::create([
            'category_id' => $category->id,
            'name' => "Tablet Model {$pad}",
            'slug' => "tablet-model-{$pad}",
            'sku' => "SKU-TAB-{$pad}",
            'is_active' => true,
        ]);

        $prod->variants()->create([
            'sku' => "SKU-TAB-{$pad}-V1",
            'actual_price' => 20000 + ($i * 1000),
            'stock_qty' => 10,
            'is_active' => true,
        ]);
    }

    // 1. Admin Products Page 1
    $adminProdPage1 = $this->withSession(['user_id' => $admin->id])->get('/adminproducts');
    $adminProdPage1->assertOk()
        ->assertSee('Showing 1-6')
        ->assertSee('Tablet Model 11')
        ->assertSee('Tablet Model 06')
        ->assertDontSee('Tablet Model 05')
        ->assertSee('page=2')
        ->assertSee('Next');

    // 2. Admin Products Page 2
    $adminProdPage2 = $this->withSession(['user_id' => $admin->id])->get('/adminproducts?page=2');
    $adminProdPage2->assertOk()
        ->assertSee('Showing 7-11')
        ->assertSee('Tablet Model 05')
        ->assertSee('Tablet Model 01')
        ->assertDontSee('Tablet Model 11')
        ->assertSee('page=1')
        ->assertSee('Previous');

    // Create 11 orders
    for ($i = 1; $i <= 11; $i++) {
        $pad = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
        Order::create([
            'user_id' => $admin->id,
            'customer_name' => "Customer {$pad}",
            'phone' => "90000000{$pad}",
            'address' => "Address {$pad}",
            'city' => 'Rajkot',
            'state' => 'Gujarat',
            'postal_code' => '360001',
            'subtotal' => 1000 * $i,
            'delivery_fee' => 0,
            'total_amount' => 1000 * $i,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'order_status' => 'delivered',
        ]);
    }

    // 3. Admin Orders Page 1
    $adminOrdersPage1 = $this->withSession(['user_id' => $admin->id])->get('/adminorders');
    $adminOrdersPage1->assertOk()
        ->assertSee('1-6')
        ->assertSee('of 11 orders recorded')
        ->assertSee('Customer 11')
        ->assertSee('Customer 06')
        ->assertDontSee('Customer 05')
        ->assertSee('page=2')
        ->assertSee('Next');

    // 4. Admin Orders Page 2
    $adminOrdersPage2 = $this->withSession(['user_id' => $admin->id])->get('/adminorders?page=2');
    $adminOrdersPage2->assertOk()
        ->assertSee('7-11')
        ->assertSee('of 11 orders recorded')
        ->assertSee('Customer 05')
        ->assertSee('Customer 01')
        ->assertDontSee('Customer 11')
        ->assertSee('page=1')
        ->assertSee('Previous');
});

test('admin pages require authentication and administrator role', function () {
    $regularUser = User::create([
        'name' => 'Regular User',
        'email' => 'regular@example.com',
        'password' => Hash::make('password123'),
        'phone' => '9111122222',
        'role' => 'user',
    ]);

    $adminUser = User::create([
        'name' => 'Super Admin',
        'email' => 'superadmin@example.com',
        'password' => Hash::make('password123'),
        'phone' => '9333344444',
        'role' => 'admin',
    ]);

    // 1. Unauthenticated guest cannot access admin pages
    $guestOrders = $this->get('/adminorders');
    $guestOrders->assertRedirect('/login')
        ->assertSessionHas('error');

    $guestProducts = $this->get('/adminproducts');
    $guestProducts->assertRedirect('/login');

    $guestAdmin = $this->get('/admin');
    $guestAdmin->assertRedirect('/login');

    // 2. Regular user (role = 'user') cannot access admin pages
    $userAccess = $this->withSession([
        'user_id' => $regularUser->id,
        'user_role' => $regularUser->role,
    ])->get('/adminorders');
    $userAccess->assertRedirect('/login')
        ->assertSessionHas('error');

    // 3. Admin user (role = 'admin') can access admin pages
    $adminAccess = $this->withSession([
        'user_id' => $adminUser->id,
        'user_role' => $adminUser->role,
    ])->get('/adminorders');
    $adminAccess->assertOk();

    // 4. Admin login redirects to intended admin page
    $loginResp = $this->post('/login', [
        'email' => 'superadmin@example.com',
        'password' => 'password123',
    ]);
    $loginResp->assertRedirect('/admin')
        ->assertSessionHas('user_id', $adminUser->id)
        ->assertSessionHas('user_role', 'admin');

    // 5. Direct login without intended URL defaults to /adminorders
    $this->flushSession();
    $directLogin = $this->post('/login', [
        'email' => 'superadmin@example.com',
        'password' => 'password123',
    ]);
    $directLogin->assertRedirect('/adminorders');
});
