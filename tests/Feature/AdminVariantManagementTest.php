<?php

use App\Models\Category;
use App\Models\User;
use App\Models\VariantGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'last_login_at' => now(),
    ]);

    $this->category = Category::create([
        'name' => 'Clothing',
        'slug' => 'clothing',
        'display_order' => 1,
        'is_active' => true,
    ]);

    $this->variantGroup = VariantGroup::create([
        'name' => 'Size',
        'label' => 'Clothing Sizes',
        'is_active' => true,
    ]);

    $this->variantGroup->categories()->sync([$this->category->id]);
    $this->variantGroup->values()->createMany([
        ['value' => 'S', 'sku_suffix' => 'SM'],
        ['value' => 'M', 'sku_suffix' => 'MD'],
        ['value' => 'L', 'sku_suffix' => 'LG'],
    ]);
});

test('admin can view the 2-column variant management page on /adminvariants', function () {
    $response = $this->actingAs($this->admin)
        ->withSession(['admin_id' => $this->admin->id])
        ->get(route('admin.variants'));

    $response->assertOk();
    $response->assertSee('Manage Variants');
    $response->assertSee('Create Variant Group');
    $response->assertSee('List Variant Groups');
    $response->assertSee('Size');
    $response->assertSee('Clothing');
    $response->assertSee('btn-action-edit');
    $response->assertSee('btn-action-delete');
    $response->assertSee('select-all-checkbox');
    $response->assertSee('status-toggle-input');
});

test('admin can view the 2-column variant management page on /adminaddvariant', function () {
    $response = $this->actingAs($this->admin)
        ->withSession(['admin_id' => $this->admin->id])
        ->get('/adminaddvariant');

    $response->assertOk();
    $response->assertSee('Manage Variants');
    $response->assertSee('Create Variant Group');
    $response->assertSee('List Variant Groups');
});

test('admin can view edit mode on /admineditvariant/{id}', function () {
    $response = $this->actingAs($this->admin)
        ->withSession(['admin_id' => $this->admin->id])
        ->get('/admineditvariant/'.$this->variantGroup->id);

    $response->assertOk();
    $response->assertSee('Edit Variant Group');
    $response->assertSee('Update Variant Group');
    $response->assertSee('Editing #'.$this->variantGroup->id);
    $response->assertSee('Size');
});

test('admin can create a variant group via POST /adminaddvariant', function () {
    $response = $this->actingAs($this->admin)
        ->withSession(['admin_id' => $this->admin->id])
        ->post('/adminaddvariant', [
            'name' => 'Color',
            'label' => 'Color choices',
            'category_id' => $this->category->id,
            'is_active' => '1',
            'values' => [
                ['value' => 'Red', 'sku_suffix' => 'RED'],
                ['value' => 'Blue', 'sku_suffix' => 'BLU'],
            ],
        ]);

    $response->assertRedirect(route('admin.variants'));
    $response->assertSessionHas('success');

    $group = VariantGroup::where('name', 'Color')->first();
    expect($group)->not->toBeNull();
    expect($group->values)->toHaveCount(2);
    expect($group->categories->first()->id)->toBe($this->category->id);
});

test('admin can create a variant group via AJAX', function () {
    $response = $this->actingAs($this->admin)
        ->withSession(['admin_id' => $this->admin->id])
        ->postJson('/adminaddvariant', [
            'name' => 'Material',
            'label' => 'Fabric type',
            'category_id' => null,
            'is_active' => '1',
            'values' => [
                ['value' => 'Cotton', 'sku_suffix' => 'COT'],
            ],
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Variant group created successfully.',
    ]);

    expect(VariantGroup::where('name', 'Material')->exists())->toBeTrue();
});

test('variant creation validates unique name and required values', function () {
    $response = $this->actingAs($this->admin)
        ->withSession(['admin_id' => $this->admin->id])
        ->post('/adminaddvariant', [
            'name' => 'Size', // duplicate
            'values' => [],
        ]);

    $response->assertSessionHasErrors(['name', 'values']);
});

test('admin can update a variant group via POST /admineditvariant/{id}', function () {
    $response = $this->actingAs($this->admin)
        ->withSession(['admin_id' => $this->admin->id])
        ->post('/admineditvariant/'.$this->variantGroup->id, [
            'name' => 'Size Updated',
            'label' => 'Standard US Sizes',
            'category_id' => $this->category->id,
            'is_active' => '1',
            'values' => [
                ['value' => 'Small', 'sku_suffix' => 'S'],
                ['value' => 'Medium', 'sku_suffix' => 'M'],
            ],
        ]);

    $response->assertRedirect(route('admin.variants'));
    $this->variantGroup->refresh();

    expect($this->variantGroup->name)->toBe('Size Updated');
    expect($this->variantGroup->label)->toBe('Standard US Sizes');
    expect($this->variantGroup->values)->toHaveCount(2);
});

test('admin can delete a variant group via DELETE /adminvariants/{id}', function () {
    $response = $this->actingAs($this->admin)
        ->withSession(['admin_id' => $this->admin->id])
        ->delete('/adminvariants/'.$this->variantGroup->id);

    $response->assertRedirect(route('admin.variants'));
    expect(VariantGroup::find($this->variantGroup->id))->toBeNull();
});

test('admin can update a variant group via AJAX', function () {
    $response = $this->actingAs($this->admin)
        ->withSession(['admin_id' => $this->admin->id])
        ->postJson('/admineditvariant/'.$this->variantGroup->id, [
            'name' => 'Size AJAX Updated',
            'label' => 'Standard Sizes',
            'category_id' => $this->category->id,
            'is_active' => '1',
            'values' => [
                ['value' => 'Extra Large', 'sku_suffix' => 'XL'],
            ],
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Variant group updated successfully.',
    ]);

    expect($this->variantGroup->fresh()->name)->toBe('Size AJAX Updated');
});

test('admin can delete a variant group via AJAX', function () {
    $response = $this->actingAs($this->admin)
        ->withSession(['admin_id' => $this->admin->id])
        ->deleteJson('/adminvariants/'.$this->variantGroup->id);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
    ]);

    expect(VariantGroup::find($this->variantGroup->id))->toBeNull();
});

test('admin can fetch variant group JSON for instant edit mode', function () {
    $response = $this->actingAs($this->admin)
        ->withSession(['admin_id' => $this->admin->id])
        ->getJson(route('admin.variants.json', $this->variantGroup->id));

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'group' => [
            'id' => $this->variantGroup->id,
            'name' => 'Size',
        ],
    ]);
});
