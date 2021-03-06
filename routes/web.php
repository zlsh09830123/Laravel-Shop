<?php

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CouponCodesController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\UserAddressesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', '/products')->name('root');
Route::get('products', [ProductsController::class, 'index'])->name('products.index');

Auth::routes(['verify' => true]);

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('user_addresses', [UserAddressesController::class, 'index'])->name('user_addresses.index');
    Route::get('user_addresses/create', [UserAddressesController::class, 'create'])->name('user_addresses.create');
    Route::post('user_addresses', [UserAddressesController::class, 'store'])->name('user_addresses.store');
    Route::get('user_addresses/{user_address}', [UserAddressesController::class, 'edit'])->name('user_addresses.edit');
    Route::put('user_addresses/{user_address}', [UserAddressesController::class, 'update'])->name('user_addresses.update');
    Route::delete('user_addresses/{user_address}', [UserAddressesController::class, 'destroy'])->name('user_addresses.destroy');
    Route::post('products/{product}/favorite', [ProductsController::class, 'favor'])->name('products.favor');
    Route::delete('products/{product}/favorite', [ProductsController::class, 'disfavor'])->name('products.disfavor');
    Route::get('products/favorites', [ProductsController::class, 'favorites'])->name('products.favorites');
    Route::post('cart', [CartController::class, 'add'])->name('cart.add');
    Route::get('cart', [CartController::class, 'index'])->name('cart.index');
    Route::delete('cart/{sku}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('orders', [OrdersController::class, 'store'])->name('orders.store');
    Route::get('orders', [OrdersController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrdersController::class, 'show'])->name('orders.show');
    Route::get('payment/{order}/ecpay', [PaymentController::class, 'payByECPay'])->name('payment.ecpay');
    Route::get('success', [PaymentController::class, 'redirectFromECPay']);
    Route::post('orders/{order}/received', [OrdersController::class, 'received'])->name('orders.received');
    Route::get('orders/{order}/review', [OrdersController::class, 'review'])->name('orders.review.show');
    Route::post('orders/{order}/review', [OrdersController::class, 'sendReview'])->name('orders.review.store');
    Route::post('orders/{order}/apply_refund', [OrdersController::class, 'applyRefund'])->name('orders.apply_refund');
    Route::get('coupon_codes/{code}', [CouponCodesController::class, 'show'])->name('coupon_codes.show');
});

Route::post('callback', [PaymentController::class, 'callback']);
Route::get('products/{product}', [ProductsController::class, 'show'])->name('products.show');
