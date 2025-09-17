<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductsController;

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('products/store', [ProductsController::class, 'store']);
    Route::post('products/upload-image', [ProductsController::class, 'uploadImage']);
});