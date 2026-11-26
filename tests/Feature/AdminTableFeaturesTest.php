<?php

use App\Models\Category;
use App\Models\Language;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\VariantGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::create([
        'id' => 1,
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'last_login_at' => now(),
    ]);

    $this->category = Category::create([
        'name' => 'Electronics',
        'slug' => 'electronics',
        'display_order' => 1,
        'is_active' => true,
    ]);

    $this->product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Smartphone Pro',
        'slug' => 'smartphone-pro',
        'sku' => 'PHONE-01',
        'is_active' => true,
    ]);

    $this->product->variants()->create([
        'sku' => 'PHONE-01-V',
        'actual_price' => 15000,
        'discounted_price' => 12000,
        'stock_qty' => 15,
        'is_active' => true,
    ]);

    $this->variantGroup = VariantGroup::create([
        'name' => 'Color',
        'label' => 'Product Color',
        'is_active' => true,
    ]);
    $this->variantGroup->values()->create(['value' => 'Red', 'sku_suffix' => 'RED']);

    $this->customer = User::create([
        'name' => 'John Customer',
        'email' => 'john@example.com',
        'password' => Hash::make('password'),
        'role' => 'user',
        'phone' => '9876543210',
        'total_spent' => 2500,
        'last_login_at' => now(),
    ]);

    $this->order = Order::create([
        'user_id' => $this->customer->id,
        'customer_name' => $this->customer->name,
        'phone' => '9876543210',
        'address' => '123 Main St',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'postal_code' => '380001',
        'subtotal' => 2451,
        'delivery_fee' => 49,
        'total_amount' => 2500,
        'payment_method' => 'cod',
        'payment_status' => 'pending',
        'order_status' => 'received',
    ]);

    $this->defaultLanguage = Language::create([
        'name' => 'English',
        'native_name' => 'English',
        'code' => 'en',
        'is_default' => true,
        'is_active' => true,
    ]);

    $this->extraLanguage = Language::create([
        'name' => 'Spanish',
        'native_name' => 'Español',
        'code' => 'es',
        'is_default' => false,
        'is_active' => false,
    ]);
});

// ==========================================
// FEATURE 1: COLUMN FORMATTER & RENDERING
// ==========================================

test('admin products table renders checkboxes, badges, and action buttons', function () {
    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->get(route('admin.products.index'));

    $response->assertOk();
    $response->assertSee('select-all-checkbox');
    $response->assertSee('row-checkbox');
    $response->assertSee('status-toggle-input');
    $response->assertSee('bg-success');
    $response->assertSee('Smartphone Pro');
    $response->assertSee('Delete Selected');
});

test('admin categories table renders checkboxes, badges, and action buttons', function () {
    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->get('/admincategories');

    $response->assertOk();
    $response->assertSee('select-all-checkbox');
    $response->assertSee('row-checkbox');
    $response->assertSee('status-toggle-input');
    $response->assertSee('Electronics');
    $response->assertSee('Delete Selected');
});

test('admin variants table renders checkboxes, badges, and action buttons', function () {
    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->get(route('admin.variants'));

    $response->assertOk();
    $response->assertSee('select-all-checkbox');
    $response->assertSee('row-checkbox');
    $response->assertSee('status-toggle-input');
    $response->assertSee('Color');
    $response->assertSee('Delete Selected');
});

test('admin users table renders checkboxes, formatted amounts, badges, and action buttons', function () {
    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->get('/adminusers');

    $response->assertOk();
    $response->assertSee('select-all-checkbox');
    $response->assertSee('row-checkbox');
    $response->assertSee('status-toggle-input');
    $response->assertSee('John Customer');
    $response->assertSee('₹2,500.00');
    $response->assertSee('editUserModal');
    $response->assertSee('Delete Selected');
});

test('admin orders table renders checkboxes, formatted amounts, status badges, and action buttons', function () {
    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->get(route('admin.orders.index'));

    $response->assertOk();
    $response->assertSee('select-all-checkbox');
    $response->assertSee('row-checkbox');
    $response->assertSee('#SK-'.$this->order->id);
    $response->assertSee('₹2,500.00');
    $response->assertSee('Delete Selected');
});

test('admin languages table renders checkboxes, badges, and edit modal', function () {
    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->get(route('admin.languages.index'));

    $response->assertOk();
    $response->assertSee('select-all-checkbox');
    $response->assertSee('row-checkbox');
    $response->assertSee('status-toggle-input');
    $response->assertSee('editLanguageModal');
    $response->assertSee('Delete Selected');
});

// ==========================================
// FEATURE 2: DELETE CHECKED RECORDS (BULK DELETE)
// ==========================================

test('admin can bulk delete products', function () {
    $p2 = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Tablet Pro',
        'slug' => 'tablet-pro',
        'sku' => 'TAB-01',
        'is_active' => true,
    ]);

    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->postJson(route('admin.products.bulk-delete'), [
            'ids' => [$p2->id],
        ]);

    $response->assertOk()
        ->assertJson(['success' => true, 'deleted_count' => 1]);

    expect(Product::find($p2->id))->toBeNull();
});

test('admin can bulk delete categories', function () {
    $c2 = Category::create([
        'name' => 'Furniture',
        'slug' => 'furniture',
        'is_active' => true,
    ]);

    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->postJson(route('admin.categories.bulk-delete'), [
            'ids' => [$c2->id],
        ]);

    $response->assertOk()
        ->assertJson(['success' => true, 'deleted_count' => 1]);

    expect(Category::find($c2->id))->toBeNull();
});

test('admin can bulk delete variant groups', function () {
    $v2 = VariantGroup::create([
        'name' => 'Storage Size',
        'label' => 'Storage',
        'is_active' => true,
    ]);
    $v2->values()->create(['value' => '64GB']);

    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->postJson(route('admin.variants.bulk-delete'), [
            'ids' => [$v2->id],
        ]);

    $response->assertOk()
        ->assertJson(['success' => true, 'deleted_count' => 1]);

    expect(VariantGroup::find($v2->id))->toBeNull();
});

test('admin can bulk delete users but cannot delete self', function () {
    $u2 = User::create([
        'name' => 'Alice Customer',
        'email' => 'alice@example.com',
        'password' => Hash::make('password'),
        'role' => 'user',
    ]);

    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->postJson(route('admin.users.bulk-delete'), [
            'ids' => [$u2->id, $this->admin->id],
        ]);

    $response->assertOk()
        ->assertJson(['success' => true, 'deleted_count' => 1]);

    expect(User::find($u2->id))->toBeNull();
    expect(User::find($this->admin->id))->not->toBeNull();
});

test('admin can bulk delete languages but cannot delete default language', function () {
    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->postJson(route('admin.languages.bulk-delete'), [
            'ids' => [$this->defaultLanguage->id, $this->extraLanguage->id],
        ]);

    $response->assertOk()
        ->assertJson(['success' => true, 'deleted_count' => 1]);

    expect(Language::find($this->defaultLanguage->id))->not->toBeNull();
    expect(Language::find($this->extraLanguage->id))->toBeNull();
});

test('admin can bulk delete orders', function () {
    $o2 = Order::create([
        'user_id' => $this->customer->id,
        'customer_name' => $this->customer->name,
        'phone' => '9876543210',
        'address' => '456 Second St',
        'city' => 'Surat',
        'state' => 'Gujarat',
        'postal_code' => '395001',
        'subtotal' => 1000,
        'delivery_fee' => 0,
        'total_amount' => 1000,
        'payment_method' => 'cod',
        'payment_status' => 'pending',
        'order_status' => 'received',
    ]);

    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->postJson(route('admin.orders.bulk-delete'), [
            'ids' => [$o2->id],
        ]);

    $response->assertOk()
        ->assertJson(['success' => true, 'deleted_count' => 1]);

    expect(Order::find($o2->id))->toBeNull();
});

// ==========================================
// FEATURE 3: EDIT / DELETE ACTION COLUMN
// ==========================================

test('admin can delete a single user but cannot delete self', function () {
    $u3 = User::create([
        'name' => 'Bob Customer',
        'email' => 'bob@example.com',
        'password' => Hash::make('password'),
        'role' => 'user',
    ]);

    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->delete(route('admin.users.destroy', $u3->id));

    $response->assertRedirect('/adminusers');
    expect(User::find($u3->id))->toBeNull();

    // Try deleting self
    $selfDelete = $this->withSession(['admin_id' => $this->admin->id])
        ->delete(route('admin.users.destroy', $this->admin->id));

    $selfDelete->assertSessionHas('error');
    expect(User::find($this->admin->id))->not->toBeNull();
});

test('admin can update a user via modal edit', function () {
    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->postJson(route('admin.users.update', $this->customer->id), [
            'name' => 'John Updated',
            'email' => 'john.updated@example.com',
            'phone' => '1122334455',
            'role' => 'user',
            'address' => '789 New St',
            'city' => 'Vadodara',
            'state' => 'Gujarat',
            'postal_code' => '390001',
        ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $updated = $this->customer->fresh();
    expect($updated->name)->toBe('John Updated')
        ->and($updated->email)->toBe('john.updated@example.com')
        ->and($updated->city)->toBe('Vadodara');
});

test('admin can update a language via modal edit', function () {
    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->postJson(route('admin.languages.update', $this->extraLanguage->id), [
            'name' => 'Castilian Spanish',
            'native_name' => 'Castellano',
        ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $updated = $this->extraLanguage->fresh();
    expect($updated->name)->toBe('Castilian Spanish')
        ->and($updated->native_name)->toBe('Castellano');
});

test('admin can delete a single order', function () {
    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->delete(route('admin.orders.destroy', $this->order->id));

    $response->assertRedirect('/adminorders');
    expect(Order::find($this->order->id))->toBeNull();
});

// ==========================================
// FEATURE 4: TOGGLE STATUS (ENABLE / DISABLE)
// ==========================================

test('admin can toggle product status via AJAX', function () {
    expect($this->product->is_active)->toBeTrue();

    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->patchJson(route('admin.products.toggle', $this->product->id));

    $response->assertOk()
        ->assertJson(['success' => true, 'is_active' => false]);

    expect($this->product->fresh()->is_active)->toBeFalse();

    // Toggle back
    $response2 = $this->withSession(['admin_id' => $this->admin->id])
        ->patchJson(route('admin.products.toggle', $this->product->id));

    $response2->assertOk()
        ->assertJson(['success' => true, 'is_active' => true]);

    expect($this->product->fresh()->is_active)->toBeTrue();
});

test('admin can toggle category status via AJAX', function () {
    expect($this->category->is_active)->toBeTrue();

    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->patchJson(route('admin.categories.toggle', $this->category->id));

    $response->assertOk()
        ->assertJson(['success' => true, 'is_active' => false]);

    expect($this->category->fresh()->is_active)->toBeFalse();
});

test('admin can toggle variant group status via AJAX', function () {
    expect($this->variantGroup->is_active)->toBeTrue();

    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->patchJson(route('admin.variants.toggle', $this->variantGroup->id));

    $response->assertOk()
        ->assertJson(['success' => true, 'is_active' => false]);

    expect($this->variantGroup->fresh()->is_active)->toBeFalse();
});

test('admin can toggle user status via AJAX but cannot deactivate self', function () {
    expect($this->customer->last_login_at)->not->toBeNull();

    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->patchJson(route('admin.users.toggle', $this->customer->id));

    $response->assertOk()
        ->assertJson(['success' => true, 'is_active' => false]);

    expect($this->customer->fresh()->last_login_at)->toBeNull();

    // Cannot toggle self
    $selfToggle = $this->withSession(['admin_id' => $this->admin->id])
        ->patchJson(route('admin.users.toggle', $this->admin->id));

    $selfToggle->assertStatus(422)
        ->assertJson(['success' => false]);
});

test('admin can toggle language status via AJAX but cannot disable default', function () {
    expect($this->extraLanguage->is_active)->toBeFalse();

    $response = $this->withSession(['admin_id' => $this->admin->id])
        ->patchJson(route('admin.languages.toggle', $this->extraLanguage->id));

    $response->assertOk()
        ->assertJson(['success' => true, 'is_active' => true]);

    expect($this->extraLanguage->fresh()->is_active)->toBeTrue();

    // Default language cannot be disabled
    $defaultToggle = $this->withSession(['admin_id' => $this->admin->id])
        ->patchJson(route('admin.languages.toggle', $this->defaultLanguage->id));

    $defaultToggle->assertStatus(422)
        ->assertJson(['success' => false]);
});
