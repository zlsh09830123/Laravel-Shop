<?php

use App\Admin\Controllers\HomeController;
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
});
