<?php

use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminLanguageController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminProductImportController;
use App\Http\Controllers\AdminSettingController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminVariantController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\UserController;
use App\Models\Language;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'login']);

Route::get('/adminlogin', function () {
    if (session('admin_id')) {
        return redirect()->route('admin.orders.index');
    }

    return redirect('/login');
});
Route::get('/admin/login', function () {
    if (session('admin_id')) {
        return redirect()->route('admin.orders.index');
    }

    return redirect('/login');
});

// public (guest)
Route::get('/login', [UserController::class, 'login'])->name('login');
Route::post('/login', [UserController::class, 'userlogin']);

Route::get('/register', [UserController::class, 'register']);
Route::post('/register', [UserController::class, 'registerSubmit']);

// Password Reset (Guest)
Route::get('/forgot-password', [UserController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [UserController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [UserController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [UserController::class, 'resetPassword'])->name('password.update');

// Stripe webhook (server-to-server, no session or CSRF)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// user (login required)
Route::middleware('user.auth')->group(function () {
    // Route::view('/index', 'user.index');
    Route::get('/index', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/product/{product:slug}', [ShopController::class, 'show'])->name('shop.product');
    Route::view('/product', 'user.product');
    Route::get('/myorders/{id}/invoice', [ShopController::class, 'downloadInvoice'])->name('orders.invoice');
    Route::post('/add-to-cart', [ShopController::class, 'addToCart'])->name('cart.add');
    Route::get('/cart', [ShopController::class, 'viewCart'])->name('cart.index');
    Route::post('/cart/update/{id}', [ShopController::class, 'updateQuantity'])->name('cart.update');
    Route::post('/cart/remove/{id}', [ShopController::class, 'removeCart'])->name('cart.remove');
    Route::post('/place-order', [ShopController::class, 'placeOrder'])->name('orders.place');
    Route::get('/myorders', [ShopController::class, 'myOrders'])->name('orders.index');
    Route::get('/payment/success', [ShopController::class, 'stripeSuccess'])->name('payment.stripe.success');
    Route::get('/payment/cancel', [ShopController::class, 'stripeCancel'])->name('payment.stripe.cancel');

    Route::get('/checkout', [ShopController::class, 'viewCart'])->name('checkout');
    Route::get('/profile', [UserController::class, 'profile']);
    Route::get('/userprofile', [UserController::class, 'profile']);
    Route::get('/user/profile', [UserController::class, 'profile']);

    Route::post('/profile/update', [UserController::class, 'profileUpdate']);
    Route::post('/profile/address', [UserController::class, 'addressUpdate']);
    Route::post('/profile/security', [UserController::class, 'securityUpdate']);

    Route::get('/rozorpay', [UserController::class, 'index']);

    Route::post('/rozorpay/payment', [UserController::class, 'payment'])
        ->name('rozorpay.payment');
    // Razorpay payment verification
    Route::post('/razorpay/verify', [ShopController::class, 'verifyRazorpayPayment'])
        ->name('payment.razorpay.verify');
});

// Logout (shared for all authenticated users and admins)
Route::match(['GET', 'POST'], '/logout', [UserController::class, 'logout'])->name('logout');
Route::match(['GET', 'POST'], '/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

Route::middleware('admin.auth')->group(function () {
    // admin
    Route::get('/admin', fn () => redirect()->route('admin.orders.index'))->name('admin.dashboard');
    Route::get('/adminorders', [AdminController::class, 'index'])->name('admin.orders.index');
    Route::get('/adminorders/table-data', [AdminController::class, 'tableData'])->name('admin.orders.table-data');
    Route::get('/adminorders/data', [AdminController::class, 'tableData'])->name('admin.orders.data');

    // Route::view('/adminproducts', 'admin.admin-products')->name('admin.products');
    Route::get('/adminproducts', [AdminProductController::class, 'index'])->name('admin.products.index');
    Route::get('/adminaddproduct', [AdminProductController::class, 'create'])->name('admin.products.create');
    Route::post('/adminaddproduct', [AdminProductController::class, 'store']);
    Route::get('/adminimportproducts', [AdminProductImportController::class, 'index'])->name('admin.products.import');
    Route::post('/adminimportproducts', [AdminProductImportController::class, 'store'])->name('admin.products.import.store');
    Route::get('/adminimportproducts/sample', [AdminProductImportController::class, 'sample'])->name('admin.products.import.sample');
    // Route::view('/admineditproduct/{id}', 'admin.admin-edit-product');
    Route::get('/admineditproduct/{product}', [AdminProductController::class, 'edit'])->name('admin.edit.product');
    Route::post('/admineditproduct/{product}', [AdminProductController::class, 'update'])->name('admin.update.product');
    Route::patch('/adminproducts/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('admin.products.toggle');
    Route::post('/adminproducts/bulk-delete', [AdminProductController::class, 'bulkDelete'])->name('admin.products.bulk-delete');
    Route::delete('/adminproducts/{product}', [AdminProductController::class, 'destroy'])->name('admin.products.destroy');

    Route::delete('/adminorders/{id}', [AdminController::class, 'destroy'])->name('admin.orders.destroy');
    Route::post('/adminorders/bulk-delete', [AdminController::class, 'bulkDelete'])->name('admin.orders.bulk-delete');
    Route::get('/adminorderdetail/{id}/invoice', [AdminController::class, 'downloadInvoice'])->name('admin.orders.invoice');

    Route::get('/admincategories', [AdminCategoryController::class, 'index']);
    Route::get('/adminaddcategory', [AdminCategoryController::class, 'create']);
    Route::post('/adminaddcategory', [AdminCategoryController::class, 'store']);
    Route::get('/admineditcategory/{id}', [AdminCategoryController::class, 'edit']);
    Route::post('/admineditcategory/{id}', [AdminCategoryController::class, 'update']);
    Route::patch('/admincategories/{category}/toggle-status', [AdminCategoryController::class, 'toggleStatus'])->name('admin.categories.toggle');
    Route::post('/admincategories/bulk-delete', [AdminCategoryController::class, 'bulkDelete'])->name('admin.categories.bulk-delete');
    Route::delete('/admincategories/{id}', [AdminCategoryController::class, 'destroy']);

    Route::get('/adminvariants', [AdminVariantController::class, 'index'])->name('admin.variants');
    Route::get('/adminaddvariant', [AdminVariantController::class, 'create'])->name('admin.variants.create');
    Route::post('/adminaddvariant', [AdminVariantController::class, 'store'])->name('admin.variants.store');
    Route::get('/admineditvariant/{id}', [AdminVariantController::class, 'edit'])->name('admin.variants.edit');
    Route::post('/admineditvariant/{id}', [AdminVariantController::class, 'update'])->name('admin.variants.update');
    Route::patch('/adminvariants/{variant}/toggle-status', [AdminVariantController::class, 'toggleStatus'])->name('admin.variants.toggle');
    Route::post('/adminvariants/bulk-delete', [AdminVariantController::class, 'bulkDelete'])->name('admin.variants.bulk-delete');
    Route::delete('/adminvariants/{id}', [AdminVariantController::class, 'destroy'])->name('admin.variants.destroy');
    Route::get('/adminvariants/{id}/json', [AdminVariantController::class, 'showJson'])->name('admin.variants.json');

    Route::get('/adminorderdetail/{id}', [AdminController::class, 'show'])->name('admin.orders.show');
    Route::get('/adminusers', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::patch('/adminusers/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggle');
    Route::post('/adminusers/{user}/update', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/adminusers/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/adminusers/bulk-delete', [AdminUserController::class, 'bulkDelete'])->name('admin.users.bulk-delete');
    Route::get('/adminprofile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::post('/adminprofile', [AdminController::class, 'profileUpdate'])->name('admin.profile.update');
    Route::post('/adminpassword', [AdminController::class, 'passwordUpdate'])->name('admin.password.update');

    Route::patch('/adminorderdetail/{id}/status', [AdminController::class, 'updateStatus'])->name('admin.orders.status');

    Route::post('/adminorderdetail/{id}/send-email', [AdminController::class, 'sendEmail'])->name('admin.orders.sendEmail');

    // Public language switcher
    Route::match(['GET', 'POST'], '/switch-language/{code?}', [AdminLanguageController::class, 'switchLanguage'])->name('languages.switch');
    Route::match(['GET', 'POST'], '/admin/switch-language/{code?}', [AdminLanguageController::class, 'switchLanguage'])->name('admin.languages.switch');
    Route::get('/adminlanguages', [AdminLanguageController::class, 'index'])->name('admin.languages.index');
    Route::post('/adminlanguages', [AdminLanguageController::class, 'store'])->name('admin.languages.store');
    Route::post('/adminlanguages/{language}/update', [AdminLanguageController::class, 'update'])->name('admin.languages.update');
    Route::patch('/adminlanguages/{language}/toggle', [AdminLanguageController::class, 'toggle'])->name('admin.languages.toggle');
    Route::patch('/adminlanguages/{language}/default', [AdminLanguageController::class, 'makeDefault'])->name('admin.languages.default');
    Route::post('/adminlanguages/bulk-delete', [AdminLanguageController::class, 'bulkDelete'])->name('admin.languages.bulk-delete');
    Route::delete('/adminlanguages/{language}', [AdminLanguageController::class, 'destroy'])->name('admin.languages.destroy');

    // System Settings & Admin Theme Colors
    Route::get('/adminsettings', [AdminSettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/adminsettings', [AdminSettingController::class, 'update'])->name('admin.settings.update');
    Route::post('/adminsettings/reset-colors', [AdminSettingController::class, 'resetColors'])->name('admin.settings.reset-colors');

});
