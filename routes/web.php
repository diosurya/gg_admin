<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\BlogCategoriesController;
use App\Http\Controllers\Admin\BlogsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\StoresController;
use App\Http\Controllers\Admin\BrandsController;
use App\Http\Controllers\Admin\BulkOperationsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\ProductCategoriesController;
use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\ProductVariantsController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TagsController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\RecommendedProductController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest routes (login & register)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');

        Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::resource('sliders', SliderController::class);
        Route::resource('recommended-products', RecommendedProductController::class);
    });
    
    // Authenticated routes
    Route::middleware('auth')->group(function () {
        // Dashboard
         Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/', [AuthController::class, 'dashboard'])->name('home');
        
        // Logout
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        // Users Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UsersController::class, 'index'])->name('index');
            Route::get('create', [UsersController::class, 'create'])->name('create');
            Route::post('/', [UsersController::class, 'store'])->name('store');
            Route::get('{id}', [UsersController::class, 'show'])->name('show');
            Route::get('{id}/edit', [UsersController::class, 'edit'])->name('edit');
            Route::put('{id}', [UsersController::class, 'update'])->name('update');
            Route::delete('{id}', [UsersController::class, 'destroy'])->name('destroy');
            Route::post('{id}/restore', [UsersController::class, 'restore'])->name('restore');
            Route::delete('{id}/force-delete', [UsersController::class, 'forceDelete'])->name('force-delete');
        });

        // Stores Management
        Route::prefix('stores')->name('stores.')->group(function () {
            Route::get('/', [StoresController::class, 'index'])->name('index');
            Route::get('create', [StoresController::class, 'create'])->name('create');
            Route::post('/', [StoresController::class, 'store'])->name('store');
            Route::get('{id}', [StoresController::class, 'show'])->name('show');
            Route::get('{id}/edit', [StoresController::class, 'edit'])->name('edit');
            Route::put('{id}', [StoresController::class, 'update'])->name('update');
            Route::delete('{id}', [StoresController::class, 'destroy'])->name('destroy');
            
            // AJAX routes
            Route::get('owner/{ownerId}/stores', [StoresController::class, 'getStoresByOwner'])->name('by-owner');
        });

        // Brands Management
        Route::prefix('brands')->name('brands.')->group(function () {
            Route::get('/', [BrandsController::class, 'index'])->name('index');
            Route::get('create', [BrandsController::class, 'create'])->name('create');
            Route::post('/', [BrandsController::class, 'store'])->name('store');
            Route::get('{id}', [BrandsController::class, 'show'])->name('show');
            Route::get('{id}/edit', [BrandsController::class, 'edit'])->name('edit');
            Route::put('{id}', [BrandsController::class, 'update'])->name('update');
            Route::delete('{id}', [BrandsController::class, 'destroy'])->name('destroy');
            
            // AJAX routes
            Route::post('reorder', [BrandsController::class, 'reorder'])->name('reorder');
            Route::post('{id}/toggle-featured', [BrandsController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::get('list/all', [BrandsController::class, 'getBrandsList'])->name('list');
        });

        // Product Categories Management
        Route::prefix('product-categories')->name('product-categories.')->group(function () {
            Route::get('/', [ProductCategoriesController::class, 'index'])->name('index');
            Route::get('create', [ProductCategoriesController::class, 'create'])->name('create');
            Route::post('/', [ProductCategoriesController::class, 'store'])->name('store');
            Route::get('{id}', [ProductCategoriesController::class, 'show'])->name('show');
            Route::get('{id}/edit', [ProductCategoriesController::class, 'edit'])->name('edit');
            Route::put('{id}', [ProductCategoriesController::class, 'update'])->name('update');
            Route::delete('{id}', [ProductCategoriesController::class, 'destroy'])->name('destroy');
            
            // AJAX routes
            Route::get('tree/data', [ProductCategoriesController::class, 'getCategoryTree'])->name('tree');
            Route::post('reorder', [ProductCategoriesController::class, 'reorder'])->name('reorder');
            Route::post('{id}/upload-media', [ProductCategoriesController::class, 'uploadMedia'])->name('upload-media');
            Route::delete('{categoryId}/media/{mediaId}', [ProductCategoriesController::class, 'deleteMedia'])->name('delete-media');

            Route::post('/reorder', [ProductCategoriesController::class, 'reorder'])->name('reorder');
        });

        // Products Management
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductsController::class, 'index'])->name('index');
            Route::get('create', [ProductsController::class, 'create'])->name('create');
            Route::post('/', [ProductsController::class, 'store'])->name('store');
            Route::get('{id}', [ProductsController::class, 'show'])->name('show');
            Route::get('{id}/edit', [ProductsController::class, 'edit'])->name('edit');
            Route::put('{id}', [ProductsController::class, 'update'])->name('update');
            Route::delete('{id}', [ProductsController::class, 'destroy'])->name('destroy');

            Route::post('/upload-image', [ProductsController::class, 'uploadImage'])->name('upload-image');
            
            // Images management
            Route::post('{id}/upload-images', [ProductsController::class, 'uploadImages'])->name('upload-images');
            Route::delete('{productId}/images/{mediaId}', [ProductsController::class, 'deleteImage'])->name('delete-image');
            Route::post('{id}/reorder-images', [ProductsController::class, 'reorderImages'])->name('reorder-images');
            
            // Variants
            Route::get('{id}/variants', [ProductsController::class, 'getVariantsByProduct'])->name('variants');
        });

        // Blog Categories Management  
        Route::prefix('blog-categories')->name('blog-categories.')->group(function () {
            Route::get('/', [BlogCategoriesController::class, 'index'])->name('index');
            Route::get('create', [BlogCategoriesController::class, 'create'])->name('create');
            Route::post('/', [BlogCategoriesController::class, 'store'])->name('store');
            Route::get('{id}', [BlogCategoriesController::class, 'show'])->name('show');
            Route::get('{id}/edit', [BlogCategoriesController::class, 'edit'])->name('edit');
            Route::put('{id}', [BlogCategoriesController::class, 'update'])->name('update');
            Route::delete('{id}', [BlogCategoriesController::class, 'destroy'])->name('destroy');
            
            // AJAX routes
            Route::get('tree/data', [BlogCategoriesController::class, 'getCategoryTree'])->name('tree');
            Route::post('reorder', [BlogCategoriesController::class, 'reorder'])->name('reorder');
            Route::post('{id}/upload-media', [BlogCategoriesController::class, 'uploadMedia'])->name('upload-media');
            Route::delete('{categoryId}/media/{mediaId}', [BlogCategoriesController::class, 'deleteMedia'])->name('delete-media');
        });

        // Blogs Management
        Route::prefix('blogs')->name('blogs.')->group(function () {
            Route::get('/', [BlogsController::class, 'index'])->name('index');
            Route::get('create', [BlogsController::class, 'create'])->name('create');
            Route::post('/', [BlogsController::class, 'store'])->name('store');
            Route::get('{blog}/edit', [BlogsController::class, 'edit'])->name('edit');
            Route::put('{blog}', [BlogsController::class, 'update'])->name('update');
            Route::get('{blog}', [BlogsController::class, 'show'])->name('show');
             Route::delete('{id}', [BlogsController::class, 'destroy'])->name('destroy');
            
            // Media management
            Route::post('{id}/upload-media', [BlogsController::class, 'uploadMedia'])->name('upload-media');
            Route::delete('{blogId}/media/{mediaId}', [BlogsController::class, 'deleteMedia'])->name('delete-media');
        });

        // Tags Management
        Route::prefix('tags')->name('tags.')->group(function () {
            Route::get('/', [TagsController::class, 'index'])->name('index');
            Route::get('create', [TagsController::class, 'create'])->name('create');
            Route::post('/', [TagsController::class, 'store'])->name('store');
            Route::get('{id}', [TagsController::class, 'show'])->name('show');
            Route::get('{id}/edit', [TagsController::class, 'edit'])->name('edit');
            Route::put('{id}', [TagsController::class, 'update'])->name('update');
            Route::delete('{id}', [TagsController::class, 'destroy'])->name('destroy');
            
            // AJAX routes
            Route::get('search', [TagsController::class, 'search'])->name('search');
            Route::get('type/{type}', [TagsController::class, 'getByType'])->name('by-type');
        });

        // Product Variants Management
        Route::prefix('variants')->name('variants.')->group(function () {
            Route::post('/', [ProductVariantsController::class, 'store'])->name('store');
            Route::get('{id}', [ProductVariantsController::class, 'show'])->name('show');
            Route::put('{id}', [ProductVariantsController::class, 'update'])->name('update');
            Route::delete('{id}', [ProductVariantsController::class, 'destroy'])->name('destroy');
            
            // Store pricing
            Route::post('{id}/store-pricing', [ProductVariantsController::class, 'updateStorePricing'])->name('store-pricing');
            Route::get('{id}/store-pricing/{storeId}', [ProductVariantsController::class, 'getStorePricing'])->name('get-store-pricing');
        });

        // Media Management (Global)
        Route::prefix('media')->name('media.')->group(function () {
            Route::post('upload', [MediaController::class, 'upload'])->name('upload');
            Route::delete('{id}', [MediaController::class, 'destroy'])->name('destroy');
            Route::post('{id}/update-details', [MediaController::class, 'updateDetails'])->name('update-details');
        });

        // SEO Management
        Route::prefix('seo')->name('seo.')->group(function () {
            Route::get('products/{id}', [SeoController::class, 'showProduct'])->name('product');
            Route::post('products/{id}', [SeoController::class, 'updateProduct'])->name('product.update');
            Route::get('categories/{id}', [SeoController::class, 'showCategory'])->name('category');
            Route::post('categories/{id}', [SeoController::class, 'updateCategory'])->name('category.update');
            Route::get('blogs/{id}', [SeoController::class, 'showBlog'])->name('blog');
            Route::post('blogs/{id}', [SeoController::class, 'updateBlog'])->name('blog.update');
        });

        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::post('/', [SettingsController::class, 'update'])->name('update');
            Route::get('cache', [SettingsController::class, 'cache'])->name('cache');
            Route::post('cache/clear', [SettingsController::class, 'clearCache'])->name('cache.clear');

             // Profile
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
        });

        // Reports & Analytics
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportsController::class, 'index'])->name('index');
            Route::get('products', [ReportsController::class, 'products'])->name('products');
            Route::get('sales', [ReportsController::class, 'sales'])->name('sales');
            Route::get('inventory', [ReportsController::class, 'inventory'])->name('inventory');
            Route::get('users', [ReportsController::class, 'users'])->name('users');
            
            // Export routes
            Route::post('export/products', [ReportsController::class, 'exportProducts'])->name('export.products');
            Route::post('export/inventory', [ReportsController::class, 'exportInventory'])->name('export.inventory');
        });

        // Bulk Operations
        Route::prefix('bulk')->name('bulk.')->group(function () {
            Route::post('products/update-status', [BulkOperationsController::class, 'updateProductStatus'])->name('products.update-status');
            Route::post('products/assign-category', [BulkOperationsController::class, 'assignCategory'])->name('products.assign-category');
            Route::post('products/update-pricing', [BulkOperationsController::class, 'updatePricing'])->name('products.update-pricing');
            Route::post('users/update-status', [BulkOperationsController::class, 'updateUserStatus'])->name('users.update-status');
        });

        Route::resource('pages', PageController::class)->except(['show']);
        // Custom Pages Routes
        Route::get('pages/{page}', [PageController::class, 'show'])->name('pages.show');
        // Quick Action Routes
        Route::patch('pages/{page}/publish', [PageController::class, 'publish'])->name('pages.publish');
        Route::patch('pages/{page}/unpublish', [PageController::class, 'unpublish'])->name('pages.unpublish');
        Route::patch('pages/{page}/archive', [PageController::class, 'archive'])->name('pages.archive');
        Route::post('pages/{page}/duplicate', [PageController::class, 'duplicate'])->name('pages.duplicate');
        
        // Bulk Actions Route
        Route::post('pages/bulk-action', [PageController::class, 'bulkAction'])->name('pages.bulk-action');

       
        
    });
});

Route::get('/', function () {
    return redirect()->route('admin.login');
});