<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('/v1/panel')->group(function () {
    //
    Route::prefix('auth')->group(function () {
        Route::post('/vendor/send-otp', [AuthController::class, 'vendorMobileSendOtp']);
        Route::post('/vendor/verify-otp', [AuthController::class, 'checkOTP']);
        Route::post('/vendor/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });
    Route::prefix('product')->middleware('auth:sanctum')->group(function () {
        Route::get('/get', [ProductController::class, 'getProduct']);
        Route::get('/get/all', [ProductController::class, 'getProducts']);
        Route::post('/add', [ProductController::class, 'createProduct']);
        Route::post('/edit', [ProductController::class, 'editProduct']);
        Route::delete('/delete', [ProductController::class, 'deleteProducts']);
    });
    Route::prefix('media')->middleware('auth:sanctum')->group(function () {
        Route::post('/upload', [MediaController::class, 'uploadMedia']);
    });
    Route::prefix('category')->middleware('auth:sanctum')->group(function () {
        Route::get('/get', [CategoryController::class, 'getCategories']);
        Route::get('/get-all', [CategoryController::class, 'getAllCategories']);
        Route::post('/add', [CategoryController::class, 'newCategory']);
        Route::post('/assign-image', [CategoryController::class, 'assignImage']);
        Route::patch('/edit', [CategoryController::class, 'editCategory']);
        Route::delete('/delete', [CategoryController::class, 'deleteCategories']);
    });
});
Route::prefix('/v1/front')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/send-otp', [AuthController::class, 'customerMobileSendOtp']);
        Route::post('/verify-otp', [AuthController::class, 'checkOTP']);
    });
    Route::prefix('layout')->group(function () {
        Route::get('/', [PageController::class, 'layout']);
    });
    Route::prefix('pages')->group(function () {
        Route::get('/main-page', [PageController::class, 'mainPage']);
        Route::get('/product-page/{uuid}', [PageController::class, 'productPage']);
    });
    Route::prefix('cart')->group(function () {
        Route::get('/get', [CartItemController::class, 'getCart'])->middleware('auth:sanctum');
        Route::post('/add/{uuid}', [CartItemController::class, 'addToCart'])->middleware('auth:sanctum');
        Route::delete('/remove/{id}', [CartItemController::class, 'removeFromCart'])->middleware('auth:sanctum');
        Route::get('/count', [CartItemController::class, 'getCartCount'])->middleware('auth:sanctum');
        Route::get('/go-up/{id}', [CartItemController::class, 'cartItemQuantityGoUp'])->middleware('auth:sanctum');
        Route::get('/go-down/{id}', [CartItemController::class, 'cartItemQuantityGoDown'])->middleware('auth:sanctum');
    });
});