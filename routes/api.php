<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ReturnExchangeController;
use App\Models\cart_items;
use App\Models\Language;
use App\Models\OrderItem;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Order;
use App\Models\VariantGroup;
use App\Models\VariantValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. Authentication APIs (Public)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| 2. Category APIs (Public)
|--------------------------------------------------------------------------
*/
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/category', [CategoryController::class, 'index']);
Route::get('/categories/{idOrSlug}', [CategoryController::class, 'show']);
Route::get('/category/{idOrSlug}', [CategoryController::class, 'show']);
Route::get('/categories/{idOrSlug}/products', [CategoryController::class, 'products']);
Route::get('/category/{idOrSlug}/products', [CategoryController::class, 'products']);

/*
|--------------------------------------------------------------------------
| 3. Product & Filter APIs (Public)
|--------------------------------------------------------------------------
| Query filters supported on /api/products:
| - category: slug or ID
| - search / q: keyword search on name, description, SKU
| - min_price, max_price: price range
| - in_stock: true/false (availability)
| - sort_by: price_asc, price_desc, newest, name_asc, name_desc
| - per_page: page size (or "all")
| - page: page number
*/
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products/{idOrSlug}', [ProductController::class, 'show']);
Route::get('/product/{idOrSlug}', [ProductController::class, 'show']);
Route::get('/products/{idOrSlug}/images', [ProductController::class, 'images']);
Route::get('/products/{idOrSlug}/specifications', [ProductController::class, 'specifications']);
Route::get('/products/{idOrSlug}/variants', [ProductController::class, 'variants']);

/*
|--------------------------------------------------------------------------
| 4. Payment Gateway APIs (Public Methods)
|--------------------------------------------------------------------------
*/
Route::get('/payment/methods', [PaymentController::class, 'methods']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Requires Laravel Sanctum Bearer Token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Authentication & Profile APIs
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::get('/user', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::put('/profile/address', [ProfileController::class, 'updateAddress']);
    Route::post('/profile/address', [ProfileController::class, 'updateAddress']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);

    // Cart APIs (Using existing cart_items table only)
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // Order APIs
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/order', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/place', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::get('/order/{id}', [OrderController::class, 'show']);
    Route::get('/orders/{id}/items', [OrderController::class, 'items']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::get('/orders/{id}/track', [OrderController::class, 'track']);
    Route::get('/orders/{id}/invoice-data', [OrderController::class, 'invoiceData']);
    Route::get('/orders/{id}/invoice', [OrderController::class, 'downloadInvoice']);

    // Return & Exchange APIs
    Route::post('/orders/{id}/return', [ReturnExchangeController::class, 'requestReturn']);
    Route::post('/orders/{id}/exchange', [ReturnExchangeController::class, 'requestExchange']);
    Route::get('/orders/{id}/return-exchange-status', [ReturnExchangeController::class, 'status']);

    // Payment APIs (Authenticated Actions)
    Route::post('/payment/create', [PaymentController::class, 'createPayment']);
    Route::post('/payment/verify', [PaymentController::class, 'verifyPayment']);

    // Admin Order Management
    Route::get('/admin/orders', [OrderController::class, 'adminOrders']);
    Route::patch('/admin/orders/{id}/status', [OrderController::class, 'updateStatus']);
});

/*
|--------------------------------------------------------------------------
| Legacy Diagnostic & Compatibility Endpoints
|--------------------------------------------------------------------------
*/
Route::get('/users', function () {
    return response()->json([
        'status' => true,
        'message' => 'Users fetched successfully',
        'data' => User::all(),
    ]);
});

Route::get('/users/{id}', function ($id) {
    $user = User::find($id);
    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not found',
        ], 404);
    }

    return response()->json([
        'status' => true,
        'message' => 'User fetched successfully',
        'data' => $user,
    ]);
});

Route::get('/language', function () {
    return response()->json([
        'status' => true,
        'message' => 'Languages fetched successfully',
        'data' => Language::all(),
    ]);
});

Route::get('/products_variants', function () {
    return response()->json([
        'status' => true,
        'message' => 'Product variants fetched successfully',
        'data' => ProductVariant::all(),
    ]);
});

Route::get('/cart_items', function () {
    return response()->json([
        'status' => true,
        'message' => 'Cart items fetched successfully',
        'data' => cart_items::all(),
    ]);
});

Route::get('/Orderitem', function () {
    return response()->json([
        'status' => true,
        'message' => 'Order items fetched successfully',
        'data' => OrderItem::all(),
    ]);
});

Route::get('/productspecification', function () {
    return response()->json([
        'status' => true,
        'message' => 'Product specifications fetched successfully',
        'data' => ProductSpecification::all(),
    ]);
});

Route::get('/variantvalue', function () {
    return response()->json([
        'status' => true,
        'message' => 'Variant values fetched successfully',
        'data' => VariantValue::all(),
    ]);
});

Route::get('/productimages', function () {
    return response()->json([
        'status' => true,
        'message' => 'Product images fetched successfully',
        'data' => ProductImage::all(),
    ]);
});

Route::get('/variantgroup', function () {
    return response()->json([
        'status' => true,
        'message' => 'Variant groups fetched successfully',
        'data' => VariantGroup::all(),
    ]);
});

Route::get('/orders', function () {
    return response()->json([
        'status' => true,
        'message' => 'Users fetched successfully',
        'data' => Order::all(),
    ]);
});

Route::get('/all-database', function () {
    $tables = [
        'users',
        'products',
        'categories',
        'cart_items',
        'orders',
        'order_items',
        'languages',
        'product_variants',
        'variant_groups',
        'variant_values',
        'product_images',
        'product_specifications',
    ];

    $data = [];
    foreach ($tables as $table) {
        $data[$table] = DB::table($table)->get();
    }

    return response()->json([
        'status' => true,
        'message' => 'All database data fetched successfully',
        'data' => $data,
    ]);
});
