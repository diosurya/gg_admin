<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\TagController;
use App\Http\Controllers\API\SliderController;
use App\Http\Controllers\API\ProductController;

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('products/store', [ProductsController::class, 'store']);
    Route::post('products/upload-image', [ProductsController::class, 'uploadImage']);
});

Route::prefix('v1')->group(function () {
    Route::get('/blogs', [BlogController::class, 'index']);
    Route::get('/blogs/{slug}', [BlogController::class, 'show']);
    Route::get('/blogs-popular', [BlogController::class, 'popular']);
    Route::get('/blog-categories', [BlogController::class, 'categories']);
    Route::get('/blog-tags', [BlogController::class, 'tags']);
    Route::post('/blogs/{slug}/share', [BlogController::class, 'share']);
    Route::get('/blogs/tag/{slug}', [BlogController::class, 'blogsByTag']);


    Route::get('/tags', [TagController::class, 'index']);
    Route::get('/sliders', [SliderController::class, 'index']);


    Route::get('/stores/{storeId}/products', [ProductController::class, 'index']);
    Route::get('/stores/{storeId}/products/{slug}', [ProductController::class, 'show']);
});