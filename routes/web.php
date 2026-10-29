    <?php

use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminLanguageController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminProductImportController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminVariantController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\UserController;
use App\Models\Language;
use Illuminate\Support\Facades\Route;

Route::view('/', 'user.login');

// public (guest)
Route::get('/login', [UserController::class, 'login']);
Route::post('/login', [UserController::class, 'userlogin']);

Route::get('/register', [UserController::class, 'register']);
Route::post('/register', [UserController::class, 'registerSubmit']);

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

    Route::post('/logout', [UserController::class, 'logout']);
});

Route::middleware('admin.auth')->group(function () {
    // admin
    Route::get('/adminorders', [AdminController::class, 'index'])->name('admin.orders.index');
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
    Route::delete('/adminproducts/{product}', [AdminProductController::class, 'destroy'])->name('admin.products.destroy');

    Route::get('/adminorderdetail/{id}/invoice', [AdminController::class, 'downloadInvoice'])->name('admin.orders.invoice');

    Route::get('/admincategories', [AdminCategoryController::class, 'index']);
    Route::get('/adminaddcategory', [AdminCategoryController::class, 'create']);
    Route::post('/adminaddcategory', [AdminCategoryController::class, 'store']);
    Route::get('/admineditcategory/{id}', [AdminCategoryController::class, 'edit']);
    Route::post('/admineditcategory/{id}', [AdminCategoryController::class, 'update']);
    Route::delete('/admincategories/{id}', [AdminCategoryController::class, 'destroy']);

    Route::get('/adminvariants', [AdminVariantController::class, 'index'])->name('admin.variants');
    Route::get('/adminaddvariant', [AdminVariantController::class, 'create']);
    Route::post('/adminaddvariant', [AdminVariantController::class, 'store']);
    Route::get('/admineditvariant/{id}', [AdminVariantController::class, 'edit']);
    Route::post('/admineditvariant/{id}', [AdminVariantController::class, 'update']);
    Route::delete('/adminvariants/{id}', [AdminVariantController::class, 'destroy']);

    Route::get('/adminorderdetail/{id}', [AdminController::class, 'show'])->name('admin.orders.show');
    Route::get('/adminusers', [AdminUserController::class, 'index']);
    Route::get('/adminprofile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::post('/adminprofile', [AdminController::class, 'profileUpdate'])->name('admin.profile.update');
    Route::post('/adminpassword', [AdminController::class, 'passwordUpdate'])->name('admin.password.update');

    Route::get('/adminorderdetail/{id}', [AdminController::class, 'show'])->name('admin.orders.show');

    Route::patch('/adminorderdetail/{id}/status', [AdminController::class, 'updateStatus'])->name('admin.orders.status');

    Route::post('/adminorderdetail/{id}/send-email', [AdminController::class, 'sendEmail'])->name('admin.orders.sendEmail');
    Route::post('/logout', [AdminController::class, 'logout']);

    // Public language switcher
    Route::match(['GET', 'POST'], '/switch-language/{code?}', [AdminLanguageController::class, 'switchLanguage'])->name('languages.switch');
    Route::match(['GET', 'POST'], '/admin/switch-language/{code?}', [AdminLanguageController::class, 'switchLanguage'])->name('admin.languages.switch');
    Route::get('/adminlanguages', [AdminLanguageController::class, 'index'])->name('admin.languages.index');
    Route::post('/adminlanguages', [AdminLanguageController::class, 'store'])->name('admin.languages.store');
    Route::patch('/adminlanguages/{language}/toggle', [AdminLanguageController::class, 'toggle'])->name('admin.languages.toggle');
    Route::patch('/adminlanguages/{language}/default', [AdminLanguageController::class, 'makeDefault'])->name('admin.languages.default');
    Route::delete('/adminlanguages/{language}', [AdminLanguageController::class, 'destroy'])->name('admin.languages.destroy');

});
