<?php

use App\Admin\Controllers\HomeController;
use App\Admin\Controllers\OrdersController;
use App\Admin\Controllers\ProductsController;
use App\Admin\Controllers\UsersController;
use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', [HomeController::class, 'index'])->name('admin.home');
    $router->get('users', [UsersController::class, 'index'])->name('admin.users');
    $router->get('products', [ProductsController::class, 'index'])->name('admin.products');
    $router->get('products/create', [ProductsController::class, 'create']);
    $router->post('products', [ProductsController::class, 'store']);
    $router->get('products/{id}/edit', [ProductsController::class, 'edit']);
    $router->put('products/{id}', [ProductsController::class, 'update']);
    $router->get('orders', [OrdersController::class, 'index'])->name('admin.orders.index');
    $router->get('orders/{order}', [OrdersController::class, 'show'])->name('admin.orders.show');
    $router->post('orders/{order}/ship', [OrdersController::class, 'ship'])->name('admin.orders.ship');
});
