<?php

use App\Models\cart_items;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Admin Boss',
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->user = User::create([
        'name' => 'Customer Joe',
        'email' => 'joe@example.com',
        'password' => Hash::make('password123'),
        'role' => 'user',
    ]);
});

test('guests are redirected away from admin settings', function () {
    $response = $this->get(route('admin.settings.index'));
    $response->assertRedirect('/login');
});

test('regular users cannot access admin settings', function () {
    $response = $this->withSession(['user_id' => $this->user->id])
        ->get(route('admin.settings.index'));

    $response->assertRedirect('/login');
});

test('admin can access settings page and see current store settings and color presets', function () {
    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->get(route('admin.settings.index'));

    $response->assertOk()
        ->assertSee('System Settings')
        ->assertSee('Store Identity & Branding')
        ->assertSee('Admin Panel Colors')
        ->assertSee('Emerald Teal (Default)')
        ->assertSee('Royal Indigo');
});

test('admin can update store name, tagline, and localization settings', function () {
    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->post(route('admin.settings.update'), [
            'store_name' => 'MegaShop Super',
            'store_tagline' => 'The Best Online Deals',
            'contact_email' => 'contact@megashop.test',
            'contact_phone' => '+91 99999 88888',
            'contact_address' => '42 Commerce Boulevard, Mumbai',
            'currency_symbol' => '₹',
            'currency_code' => 'INR',
            'free_delivery_threshold' => 699,
            'delivery_fee' => 59,
            'admin_primary_color' => '#4338CA',
            'admin_sidebar_bg' => '#1E1B4B',
        ]);

    $response->assertRedirect(route('admin.settings.index'))
        ->assertSessionHas('success');

    expect(setting('store_name'))->toBe('MegaShop Super')
        ->and(setting('store_tagline'))->toBe('The Best Online Deals')
        ->and(setting('contact_email'))->toBe('contact@megashop.test')
        ->and(setting('contact_phone'))->toBe('+91 99999 88888')
        ->and(setting('contact_address'))->toBe('42 Commerce Boulevard, Mumbai')
        ->and((float) setting('free_delivery_threshold'))->toBe(699.0)
        ->and((float) setting('delivery_fee'))->toBe(59.0)
        ->and(setting('admin_primary_color'))->toBe('#4338CA')
        ->and(setting('admin_sidebar_bg'))->toBe('#1E1B4B');
});

test('admin can upload store logo and remove it', function () {
    $file = UploadedFile::fake()->image('custom_store_logo.png', 300, 100);

    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->post(route('admin.settings.update'), [
            'store_name' => 'Logo Test Store',
            'store_logo' => $file,
        ]);

    $response->assertRedirect(route('admin.settings.index'));

    $savedLogo = setting('store_logo');
    expect($savedLogo)->not->toBeNull()
        ->and(str_starts_with($savedLogo, 'uploads/settings/'))->toBeTrue()
        ->and(File::exists(public_path($savedLogo)))->toBeTrue();

    // Now remove the logo
    $removeResponse = $this->withSession(['admin_id' => $this->admin->id])
        ->post(route('admin.settings.update'), [
            'store_name' => 'Logo Test Store',
            'remove_store_logo' => '1',
        ]);

    $removeResponse->assertRedirect(route('admin.settings.index'));
    expect(setting('store_logo'))->toBeNull();
});

test('admin can customize admin panel colors and reset them to default', function () {
    // 1. Customize to purple theme
    $this->withSession(['admin_id' => $this->admin->id])
        ->post(route('admin.settings.update'), [
            'store_name' => 'Shopkart',
            'admin_primary_color' => '#BE123C',
            'admin_sidebar_bg' => '#4C0519',
        ]);

    expect(setting('admin_primary_color'))->toBe('#BE123C')
        ->and(setting('admin_sidebar_bg'))->toBe('#4C0519');

    // 2. Reset colors
    $resetResponse = $this->withSession(['admin_id' => $this->admin->id])
        ->post(route('admin.settings.reset-colors'));

    $resetResponse->assertRedirect(route('admin.settings.index'))
        ->assertSessionHas('success');

    expect(setting('admin_primary_color'))->toBe('#0F766E')
        ->and(setting('admin_sidebar_bg'))->toBe('#0F2A27');
});

test('setting helper returns default values when setting is not stored in database', function () {
    Setting::truncate();
    Setting::forgetCache();

    expect(setting('store_name'))->toBe('Shopkart')
        ->and(setting('admin_primary_color'))->toBe('#0F766E')
        ->and(setting('non_existent_key', 'fallback_val'))->toBe('fallback_val');
});

test('storefront footer renders dynamic store name, tagline, email, and address from settings', function () {
    Setting::set('store_name', 'TechVault Hub', 'identity');
    Setting::set('store_tagline', 'Your Premier Gadget Destination', 'identity');
    Setting::set('contact_email', 'help@techvaulthub.com', 'contact');
    Setting::set('contact_phone', '+91 88888 77777', 'contact');
    Setting::set('contact_address', '99 Cyber City, Gurugram', 'contact');

    $response = $this->withSession(['user_id' => $this->user->id])->get('/index');

    $response->assertOk()
        ->assertSee('TechVault Hub')
        ->assertSee('Your Premier Gadget Destination')
        ->assertSee('help@techvaulthub.com')
        ->assertSee('+91 88888 77777')
        ->assertSee('99 Cyber City, Gurugram');
});

test('checkout delivery fee dynamically uses configured standard delivery fee and threshold', function () {
    $cat = Category::create(['name' => 'Testing', 'slug' => 'testing', 'is_active' => true]);
    $prod = Product::create(['category_id' => $cat->id, 'name' => 'Demo Item', 'slug' => 'demo-item', 'is_active' => true]);
    $variant = $prod->variants()->create([
        'sku' => 'DEMO-1',
        'actual_price' => 1000,
        'discounted_price' => 756,
        'stock_qty' => 20,
        'is_active' => true,
    ]);

    // Customer adds item with price 756 to cart
    cart_items::create([
        'user_id' => $this->user->id,
        'product_id' => $prod->id,
        'variant_id' => $variant->id,
        'quantity' => 1,
    ]);

    // Configure Free Delivery Over 1000 and Standard Delivery Fee 33 (like user screenshot!)
    Setting::set('free_delivery_threshold', '1000', 'shipping');
    Setting::set('delivery_fee', '33', 'shipping');

    $response = $this->withSession(['user_id' => $this->user->id])->get('/cart');

    $response->assertOk()
        ->assertSee('33.00') // Delivery fee is charged because 756 < 1000
        ->assertSee('789.00'); // 756 + 33 = 789
});
