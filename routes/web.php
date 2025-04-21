<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ProductsController;
use App\Http\Controllers\Web\UsersController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('products', [ProductsController::class, 'list'])->name('products_list');
Route::get('products/edit/{product?}', [ProductsController::class, 'edit'])->name('products_edit');
Route::post('products/save/{product?}', [ProductsController::class, 'save'])->name('products_save');
Route::get('products/delete/{product}', [ProductsController::class, 'delete'])->name('products_delete');
Route::post('/products/{product}/buy', [ProductsController::class, 'buy'])->name('products.buy')->middleware('auth');
Route::get('products/insufficient-funds/{product}', [ProductsController::class, 'insufficientFunds'])->name('products.insufficient-funds')->middleware('auth');

Route::get('register', [UsersController::class, 'register'])->name('register');
Route::post('do_register', [UsersController::class, 'doRegister'])->name('do_register');
Route::get('login', [UsersController::class, 'login'])->name('login');
Route::post('do_login', [UsersController::class, 'doLogin'])->name('do_login');
Route::get('logout', [UsersController::class, 'doLogout'])->name('logout');

// Email verification route
Route::get('verify', [UsersController::class, 'verify'])->name('verify');

Auth::routes([
    'verify' => true,
    'register' => false,
    'login' => false,
    'logout' => false,
    'reset' => false,
]);

Route::get('users', [UsersController::class, 'list'])->name('users');
Route::post('users/create_employee', [UsersController::class, 'createEmployee'])->name('users.create_employee');
Route::get('profile/{user?}', [UsersController::class, 'profile'])->name('profile')->middleware('verified');
Route::get('users/edit/{user?}', [UsersController::class, 'edit'])->name('users_edit');
Route::post('users/save/{user}', [UsersController::class, 'save'])->name('users_save');
Route::get('users/delete/{user}', [UsersController::class, 'delete'])->name('users_delete');
Route::get('users/edit_password/{user?}', [UsersController::class, 'editPassword'])->name('edit_password');
Route::post('users/save_password/{user}', [UsersController::class, 'savePassword'])->name('save_password');
Route::post('users/{user}/add_credit', [UsersController::class, 'addCredit'])->name('users.add_credit');
Route::post('users/{user}/reset_credit', [UsersController::class, 'resetCredit'])->name('users.reset_credit');