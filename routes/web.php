<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductTableController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\BigCommerceController;
use App\Http\Controllers\PaymentController;

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


Route::middleware(['cors'])->group(function () {
    Route::any('/bc-api/{endpoint}', [BigCommerceController::class,'proxy_BigCommerce_API_Request'])->where('endpoint', 'v2\/.*|v3\/.*');
    Route::get('/product-table', [ProductTableController::class, 'index']);
    Route::match( ['get', 'post'], '/get_products', [ProductTableController::class, 'get_products']); 
});

Route::get('/{url?}', [SettingController::class, 'store_config'] );
Route::post('/admin-setting', [SettingController::class, 'save_config'] )->name('admin-setting');
Route::match(['get', 'post'], '/stripe/stores/{store_hash}', [PaymentController::class, 'index'] )->name('payment');
Route::get('/error', [BigCommerceController::class,'show_error']);

//Stripe Endpoints
Route::post('subscription-created', [PaymentController::class, 'subscription_created'])->middleware('cors');
Route::post('subscription-failed', [PaymentController::class, 'subscription_failed'])->middleware('cors');


Route::group(['prefix' => 'auth'], function () {
    Route::get('install', [BigCommerceController::class,'install']);
    Route::get('load', [BigCommerceController::class,'load']);
    Route::get('uninstall', [BigCommerceController::class,'uninstall']);
    Route::get('remove-user', [BigCommerceController::class,'remove_user']);
});


