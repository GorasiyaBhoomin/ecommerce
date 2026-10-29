<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\VariantGroup;
use App\Models\VariantValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

/**
 * Visit a storefront URL as a signed in customer.
 */
function shopVisit(string $url): TestResponse
{
    return test()->withSession(['user_id' => 1, 'user_name' => 'Test Customer'])->get($url);
}

test('the storefront lists active products and hides inactive ones', function () {
    $category = Category::create([
        'name' => 'Mobiles',
        'slug' => 'mobiles',
    ]);

    $active = Product::create([
        'category_id' => $category->id,
        'name' => 'Galaxy Phone',
        'slug' => 'galaxy-phone',
        'description' => 'An active product.',
        'is_active' => true,
    ]);

    $active->images()->create([
        'path' => 'uploads/products/galaxy.webp',
        'position' => 0,
    ]);

    $active->variants()->create([
        'sku' => 'GALAXY-128',
        'actual_price' => 12000,
        'discounted_price' => 9000,
        'stock_qty' => 8,
        'is_active' => true,
    ]);

    Product::create([
        'category_id' => $category->id,
        'name' => 'Hidden Phone',
        'slug' => 'hidden-phone',
        'is_active' => false,
    ]);

    $response = shopVisit(route('shop.index'));

    $response->assertOk();
    $response->assertSee('Galaxy Phone');
    $response->assertSee('Mobiles');
    $response->assertSee('uploads/products/galaxy.webp');
    $response->assertSee('In stock');
    $response->assertSee('&#8377;9,000', false);
    $response->assertSee('Showing 1 of 1 products');
    $response->assertDontSee('Hidden Phone');
});

test('the storefront builds its category pills from the active categories', function () {
    $mobiles = Category::create([
        'name' => 'Mobiles',
        'slug' => 'mobiles',
    ]);

    $grocery = Category::create([
        'name' => 'Grocery',
        'slug' => 'grocery',
    ]);

    Category::create([
        'name' => 'Hidden Category',
        'slug' => 'hidden-category',
        'is_active' => false,
    ]);

    Product::create([
        'category_id' => $mobiles->id,
        'name' => 'Galaxy Phone',
        'slug' => 'galaxy-phone',
        'is_active' => true,
    ]);

    $rice = Product::create([
        'category_id' => $grocery->id,
        'name' => 'Rice Bag',
        'slug' => 'rice-bag',
        'is_active' => true,
    ]);

    $rice->variants()->create([
        'sku' => 'RICE-1',
        'actual_price' => 500,
        'discounted_price' => 0,
        'stock_qty' => 2,
        'is_active' => true,
    ]);

    $response = shopVisit(route('shop.index', ['category' => $grocery->slug]));

    $response->assertOk();
    $response->assertSee('All Products (2)');
    $response->assertSee('Mobiles (1)');
    $response->assertSee('Grocery (1)');
    $response->assertDontSee('Hidden Category');
    $response->assertSee('class="filter-pill active"', false);
});

test('the storefront filters the catalog by category slug', function () {
    $mobiles = Category::create([
        'name' => 'Mobiles',
        'slug' => 'mobiles',
    ]);

    $grocery = Category::create([
        'name' => 'Grocery',
        'slug' => 'grocery',
    ]);

    Product::create([
        'category_id' => $mobiles->id,
        'name' => 'Galaxy Phone',
        'slug' => 'galaxy-phone',
        'is_active' => true,
    ]);

    Product::create([
        'category_id' => $grocery->id,
        'name' => 'Basmati Rice',
        'slug' => 'basmati-rice',
        'is_active' => true,
    ]);

    $response = shopVisit(route('shop.index', ['category' => 'grocery']));

    $response->assertOk();
    $response->assertSee('Basmati Rice');
    $response->assertSee('Showing 1 of 2 products');
    $response->assertDontSee('Galaxy Phone');
});

test('the storefront filters the catalog by search term', function () {
    $category = Category::create([
        'name' => 'Grocery',
        'slug' => 'grocery',
    ]);

    Product::create([
        'category_id' => $category->id,
        'name' => 'Basmati Rice',
        'slug' => 'basmati-rice',
        'is_active' => true,
    ]);

    Product::create([
        'category_id' => $category->id,
        'name' => 'Sunflower Oil',
        'slug' => 'sunflower-oil',
        'is_active' => true,
    ]);

    $response = shopVisit(route('shop.index', ['search' => 'Rice']));

    $response->assertOk();
    $response->assertSee('Basmati Rice');
    $response->assertSee('Showing 1 of 2 products for');
    $response->assertSee('value="Rice"', false);
    $response->assertDontSee('Sunflower Oil');
});

test('the storefront shows an empty state when no product matches', function () {
    $category = Category::create([
        'name' => 'Grocery',
        'slug' => 'grocery',
    ]);

    Product::create([
        'category_id' => $category->id,
        'name' => 'Basmati Rice',
        'slug' => 'basmati-rice',
        'is_active' => true,
    ]);

    $response = shopVisit(route('shop.index', ['search' => 'nothing-matches']));

    $response->assertOk();
    $response->assertSee('No products found.');
    $response->assertSee('Showing 0 of 1 products for');
    $response->assertDontSee('class="product-card"', false);
});

test('the storefront replaces the product link with a disabled label when everything is sold out', function () {
    $category = Category::create([
        'name' => 'Mobiles',
        'slug' => 'mobiles',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Sold Out Phone',
        'slug' => 'sold-out-phone',
        'is_active' => true,
    ]);

    $product->variants()->create([
        'sku' => 'SOLD-OUT-1',
        'actual_price' => 1000,
        'discounted_price' => 0,
        'stock_qty' => 0,
        'is_active' => true,
    ]);

    $response = shopVisit(route('shop.index'));

    $response->assertOk();
    $response->assertSee('Out of stock');
    $response->assertSee('btn-view-product disabled', false);
});

test('the product detail page renders the gallery, variants and specifications', function () {
    $category = Category::create([
        'name' => 'Mobiles',
        'slug' => 'mobiles',
    ]);

    $group = VariantGroup::create([
        'name' => 'Storage',
        'label' => 'Storage',
        'is_active' => true,
    ]);

    $base = VariantValue::create([
        'variant_group_id' => $group->id,
        'value' => '128 GB',
        'sku_suffix' => '128',
    ]);

    $plus = VariantValue::create([
        'variant_group_id' => $group->id,
        'value' => '256 GB',
        'sku_suffix' => '256',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Nova Phone',
        'slug' => 'nova-phone',
        'sku' => 'NOVA-PHONE',
        'description' => 'A phone built by the test suite.',
        'is_active' => true,
    ]);

    $product->images()->create([
        'path' => 'uploads/products/nova-1.webp',
        'position' => 0,
    ]);

    $product->images()->create([
        'path' => 'uploads/products/nova-2.webp',
        'position' => 1,
    ]);

    $product->specifications()->create([
        'label' => 'Battery',
        'value' => '5000 mAh',
        'position' => 0,
    ]);

    $soldOut = $product->variants()->create([
        'sku' => 'NOVA-128',
        'actual_price' => 20000,
        'discounted_price' => 18000,
        'stock_qty' => 0,
        'is_active' => true,
    ]);
    $soldOut->values()->attach($base);

    $inStock = $product->variants()->create([
        'sku' => 'NOVA-256',
        'actual_price' => 25000,
        'discounted_price' => 23000,
        'stock_qty' => 5,
        'is_active' => true,
    ]);
    $inStock->values()->attach($plus);

    $response = shopVisit(route('shop.product', $product));

    $response->assertOk();
    $response->assertSee('Nova Phone');
    $response->assertSee('NOVA-PHONE');
    $response->assertSee('A phone built by the test suite.');
    $response->assertSee('Battery');
    $response->assertSee('5000 mAh');
    $response->assertSee('uploads/products/nova-1.webp');
    $response->assertSee('uploads/products/nova-2.webp');
    $response->assertSee('Save 8%');
    $response->assertSee('Storage');
    $response->assertSee('128 GB');
    $response->assertSee('256 GB');

    // The 128 GB variant is sold out, so the sellable 256 GB one is the default selection.
    $response->assertSee('"selected":['.$plus->id.']', false);
    $response->assertSee('"value_ids":['.$base->id.']', false);
    $response->assertSee('"value_ids":['.$plus->id.']', false);

    $response->assertSee('&#8377;23,000', false);
    $response->assertSee('Only 5 left in stock - Order soon');
});

test('the product detail page is not found for an inactive product', function () {
    $category = Category::create([
        'name' => 'Mobiles',
        'slug' => 'mobiles',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Retired Phone',
        'slug' => 'retired-phone',
        'is_active' => false,
    ]);

    shopVisit(route('shop.product', $product))->assertNotFound();
});

test('the storefront requires the customer to be signed in', function () {
    $this->get(route('shop.index'))->assertRedirect('/login');
});
