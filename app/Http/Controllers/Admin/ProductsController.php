<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\ProductCategory;
use App\Models\ProductMedia;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('products')
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                'products.short_description',
                'products.status',
                'products.created_at',
                'brands.name as brand_name',
                'users.first_name as creator_first_name',
                'users.last_name as creator_last_name',
                'stores.id as store_id',
                'stores.name as store_name',
                'product_stores.display_name as store_display_name',
                'product_stores.short_description as store_short_description',
                'product_stores.custom_description as store_custom_description',
                'product_stores.is_active as store_is_active',
                'product_stores.featured_in_store',
                'product_stores.sort_order',
            ])
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('users', 'products.created_by', '=', 'users.id')
            ->leftJoin('product_stores', 'products.id', '=', 'product_stores.product_id')
            ->leftJoin('stores', 'product_stores.store_id', '=', 'stores.id')
            ->whereNull('products.deleted_at');

        // Apply filters
        $this->applyFilters($query, $request);

        // Ambil jumlah per halaman dari request (default 10)
        $perPage = $request->input('per_page', 10);

        // Paginate dengan query string agar filter ikut
        $products = $query->orderBy('products.created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        // Brands untuk dropdown filter
        $brands = DB::table('brands')
            ->select('id', 'name')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        // Stores untuk dropdown filter
        $stores = DB::table('stores')
            ->select('id', 'name')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('admin.products.index', compact('products', 'brands', 'stores'));
    }

    private function applyFilters($query, Request $request)
    {
        // Search filter
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', $search)
                    ->orWhere('products.sku', 'like', $search)
                    ->orWhere('products.short_description', 'like', $search)
                    ->orWhere('product_stores.display_name', 'like', $search)
                    ->orWhere('stores.name', 'like', $search);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('products.status', $request->status);
        }

        // Store filter
        if ($request->filled('store_id')) {
            $query->where('stores.id', $request->store_id);
        }

        // Brand filter
        if ($request->filled('brand_id')) {
            $query->where('products.brand_id', $request->brand_id);
        }
    }


    public function show($id)
    {
      
        $product = DB::table('products')
            ->select([
                'products.*',
                'brands.name as brand_name',
                'users.first_name as creator_first_name',
                'users.last_name as creator_last_name'
            ])
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('users', 'products.created_by', '=', 'users.id')
            ->where('products.id', $id)
            ->whereNull('products.deleted_at')
            ->first();

        if (!$product) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Product not found!');
        }

        // Get product categories
        $categories = DB::table('product_category_relationships as pcr')
            ->join('product_categories as pc', 'pcr.product_category_id', '=', 'pc.id')
            ->where('pcr.product_id', $id)
            ->select('pc.id', 'pc.name', 'pc.slug', 'pcr.is_primary')
            ->get();

        // Get product variants
        $variants = DB::table('product_variants')
            ->where('product_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->get();

        // Get variant attributes for each variant
        foreach ($variants as $variant) {
            $variant->attributes = DB::table('variant_attributes')
                ->where('variant_id', $variant->id)
                ->get();

            // Get variant store pricing
            $variant->stores = DB::table('variant_stores as vs')
                ->join('stores as s', 'vs.store_id', '=', 's.id')
                ->where('vs.variant_id', $variant->id)
                ->select('s.name as store_name', 'vs.*')
                ->get();
        }

        // Get product media
        $media = DB::table('product_media')
            ->where('product_id', $id)
            ->orderBy('sort_order')
            ->orderBy('is_featured', 'desc')
            ->get();
        
        $coverProduct = DB::table('product_media')
            ->where('product_id', $id)
            ->where('is_cover', 1)
            ->orderBy('sort_order')
            ->orderBy('is_cover', 'desc')
            ->first();

            // dd($coverProduct);

        // Get product stores
        $productStores = DB::table('product_stores as ps')
            ->join('stores as s', 'ps.store_id', '=', 's.id')
            ->where('ps.product_id', $id)
            ->select('s.name as store_name', 'ps.*')
            ->get();

        // Get SEO data
        $seoData = DB::table('product_seo')
            ->where('product_id', $id)
            ->get()
            ->keyBy('store_id');

        // Get tags
        $tags = DB::table('product_tags as pt')
            ->join('tags as t', 'pt.tag_id', '=', 't.id')
            ->where('pt.product_id', $id)
            ->select('t.id', 't.name', 't.slug')
            ->get();

        return view('admin.products.show', compact('product', 'coverProduct', 'categories', 'variants', 'media', 'productStores', 'seoData', 'tags'));
    }

    public function create()
    {
        // Get brands
        $brands = DB::table('brands')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $stores = DB::table('stores')
            ->orderBy('name')
            ->get();

        $categoryTree = $this->buildCategoryTree();

        return view('admin.products.create', compact(
            'stores',
            'brands',
            'categoryTree'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $user_id = (string) $user->id;
        try {

            Log::info('Product Store Request Data:', [
                'all_data' => $request->all(),
                'files' => $request->allFiles(),
                'variants' => $request->input('variants', []),
                'categories' => $request->input('categories', []),
                'images' => $request->input('images', []),
                'discounts' => $request->input('discounts', [])
            ]);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'sku' => 'required|string|unique:products,sku',
                'price' => 'required|numeric|min:0',
                'status' => 'required|in:draft,published,archived',
                'short_description' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'brand_id' => 'nullable|exists:brands,id',
                'type' => 'nullable|string|max:100',
                'barcode' => 'nullable|string|max:100',
                'model' => 'nullable|string|max:100',
                'minimum_quantity' => 'nullable|integer|min:1',
                'sort_order' => 'nullable|integer',
                'track_stock' => 'nullable|boolean',
                'is_featured' => 'nullable|boolean',
                'sale_price' => 'nullable|numeric|min:0',
                'cost_price' => 'nullable|numeric|min:0',
                'weight' => 'nullable|numeric|min:0',
                'length' => 'nullable|numeric|min:0',
                'width' => 'nullable|numeric|min:0',
                'height' => 'nullable|numeric|min:0',
                'tax_status' => 'nullable|in:taxable,none',

                'meta_title' => 'nullable|string|max:60',
                'meta_description' => 'nullable|string|max:160',
                'meta_keywords' => 'nullable|string',
                'slug' => 'nullable|string|unique:products,slug',
                
                'variants' => 'nullable|array',
                'variants.*.type' => 'nullable|string|max:100',
                'variants.*.color' => 'nullable|string|max:100',
                'variants.*.value' => 'nullable|string|max:100',
                'variants.*.sku' => 'nullable|string',
                'variants.*.price' => 'nullable|numeric|min:0',
                'variants.*.stock_quantity' => 'nullable|integer|min:0',
                'variants.*.images' => 'nullable|array',
                'variants.*.images.*.id' => 'nullable',
                'variants.*.images.*.name' => 'nullable|string',
                'variants.*.images.*.alt_text' => 'nullable|string',
                'variants.*.images.*.sort_order' => 'nullable|integer',
                'variants.*.images.*.path' => 'nullable|string',
                
                'categories' => 'nullable|array',
                'categories.*' => 'exists:product_categories,id',
                
                'images' => 'nullable|array',
                'images.*.id' => 'nullable',
                'images.*.name' => 'nullable|string',
                'images.*.alt_text' => 'nullable|string',
                'images.*.sort_order' => 'nullable|integer',
                
                'discounts' => 'nullable|array',
                'discounts.*.quantity' => 'nullable|integer|min:1',
                'discounts.*.type' => 'nullable|in:percentage,fixed',
                'discounts.*.value' => 'nullable|numeric|min:0',
                'discounts.*.start_date' => 'nullable|date',
                'discounts.*.end_date' => 'nullable|date|after_or_equal:discounts.*.start_date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'debug_data' => [
                        'request_data' => $request->all(),
                        'validation_rules' => $validator->getRules()
                    ]
                ], 422);
            }

            DB::beginTransaction();

            // Generate slug if not provided
            $slug = $request->slug ?: Str::slug($request->name);
            $originalSlug = $slug;
            $counter = 1;
            
            while (DB::table('products')->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            // Create main product
            $product = Product::create([
                'name' => $request->name,
                'sku' => $request->sku,
                'slug' => $slug,
                'short_description' => $request->short_description,
                'description' => $request->description,
                'price' => $request->price,
                'sale_price' => $request->sale_price,
                'cost_price' => $request->cost_price,
                'brand_id' => $request->brand_id,
                'type' => $request->type,
                'barcode' => $request->barcode,
                'model' => $request->model,
                'minimum_quantity' => $request->minimum_quantity ?? 1,
                'track_stock' => $request->boolean('track_stock'),
                'is_featured' => $request->boolean('is_featured'),
                'status' => $request->status,
                'weight' => $request->weight,
                'length' => $request->length,
                'width' => $request->width,
                'height' => $request->height,
                'tax_status' => $request->tax_status ?? 'taxable',
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'created_by' => $user_id
            ]);

           if ($request->has('stores') && is_array($request->stores)) {
                foreach ($request->stores as $storeUuid => $storeData) {
                    // cek kalau ada key store_id dan tidak kosong
                    if (!empty($storeData['store_id'])) {
                        ProductStore::create([
                            'product_id' => $product->id,
                            'store_id'   => $storeData['store_id'],
                            'is_active'  => !empty($storeData['selected']) ? true : false,
                        ]);
                    }
                }
            }

            Log::info('Product created:', ['product_id' => $product->id]);

            // Handle Categories
            if ($request->has('categories') && is_array($request->categories)) {
                $categoriesData = [];
                foreach (array_unique($request->categories) as $categoryId) {
                    $categoriesData[] = [
                        'id' => (string) Str::uuid(),
                        'product_id' => $product->id,
                        'product_category_id' => $categoryId,
                        'is_primary' => 0,
                        'created_at' => now(),
                    ];
                }
                
                if (!empty($categoriesData)) {
                    DB::table('product_category_relationships')->insert($categoriesData);
                }
                
                Log::info('Categories attached:', ['categories' => $request->categories]);
            }

            // Handle Main Product Images (TANPA VARIANT)
            if ($request->has('images') && is_array($request->images)) {
                $coverImageIndex = $request->input('cover_image', 1); // Default index 1
                
                foreach ($request->images as $index => $imageData) {
                    if (!empty($imageData['path'])) {
                        $productMediaId = (string) Str::uuid();
                        $isCover = ($index == $coverImageIndex) ? 1 : 0;
                        
                        // Extract file info from path
                        $filePath = $imageData['path'];
                        $fileName = $imageData['name'] ?? basename($filePath);
                        $altText = $imageData['alt_text'] ?? '';
                        $sortOrder = $imageData['sort_order'] ?? $index;
                        $fileSize = 0;
                        $mimeType = 'image/jpeg';
                        
                        // Try to get file info if it exists
                        if (Storage::disk('public')->exists(str_replace('/storage/', '', $filePath))) {
                            $fileSize = Storage::disk('public')->size(str_replace('/storage/', '', $filePath));
                            $mimeType = Storage::disk('public')->mimeType(str_replace('/storage/', '', $filePath)) ?? 'image/jpeg';
                        }
                        
                        // Insert to product_media
                        DB::table('product_media')->insert([
                            'id' => $productMediaId,
                            'product_id' => $product->id,
                            'product_variant_id' => null,
                            'image_path' => $filePath,
                            'original_name' => $fileName,
                            'file_name' => basename($filePath),
                            'file_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                            'file_size' => $fileSize,
                            'mime_type' => $mimeType,
                            'media_type' => 'image',
                            'sort_order' => $sortOrder,
                            'is_cover' => $isCover, // Changed from is_primary
                            'is_temporary' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        // If this is the cover image, also save to products table
                        if ($isCover) {
                            DB::table('products')
                                ->where('id', $product->id)
                                ->update([
                                    'cover_image' => $filePath,
                                    'cover_image_name' => $fileName,
                                    'cover_image_alt' => $altText,
                                    'cover_image_sort_order' => $sortOrder,
                                    'updated_at' => now(),
                                ]);
                                
                            Log::info('Main product cover image set:', [
                                'product_id' => $product->id,
                                'cover_image' => $filePath
                            ]);
                        }
                    }
                }
                
                Log::info('Main product images attached:', ['images_count' => count($request->images)]);
            }

           // Handle Variants and their Images
            if ($request->has('variants') && is_array($request->variants)) {
                foreach ($request->variants as $variantIndex => $variantData) {
                    $variantId = (string) Str::uuid();
                    $coverImagePath = null;
                    $coverImageName = null;
                    $coverImageAlt = null;
                    $coverImageSortOrder = null;

                    // Handle variant images
                    if (!empty($variantData['images']) && is_array($variantData['images'])) {
                        $images = array_values($variantData['images']);

                        // First image is the cover for this variant
                        $firstImage = $images[0] ?? null;
                        if ($firstImage && !empty($firstImage['path'])) {
                            $productMediaId = (string) Str::uuid();

                            $filePath = $firstImage['path'];
                            $fileName = $firstImage['name'] ?? basename($filePath);
                            $altText = $firstImage['alt_text'] ?? '';
                            $sortOrder = $firstImage['sort_order'] ?? 0;
                            $fileSize = 0;
                            $mimeType = 'image/jpeg';

                            // Check file in storage
                            if (Storage::disk('public')->exists(str_replace('/storage/', '', $filePath))) {
                                $fileSize = Storage::disk('public')->size(str_replace('/storage/', '', $filePath));
                                $mimeType = Storage::disk('public')->mimeType(str_replace('/storage/', '', $filePath)) ?? 'image/jpeg';
                            }

                            // Insert to product_media with variant_id
                            DB::table('product_media')->insert([
                                'id' => $productMediaId,
                                'product_id' => $product->id,
                                'product_variant_id' => $variantId,
                                'image_path' => $filePath,
                                'original_name' => $fileName,
                                'file_name' => basename($filePath),
                                'file_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                                'file_size' => $fileSize,
                                'mime_type' => $mimeType,
                                'media_type' => 'image',
                                'sort_order' => $sortOrder,
                                'is_cover' => 0, // First image is cover
                                'is_featured' => 0,
                                'is_temporary' => 0,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            
                            // Store cover image data for variant table
                            $coverImagePath = $filePath;
                            $coverImageName = $fileName;
                            $coverImageAlt = $altText;
                            $coverImageSortOrder = $sortOrder;
                        }

                        // Additional images (not cover)
                        foreach (array_slice($images, 1) as $imageIndex => $imageData) {
                            if (!empty($imageData['path'])) {
                                $additionalMediaId = (string) Str::uuid();

                                $filePath = $imageData['path'];
                                $fileName = $imageData['name'] ?? basename($filePath);
                                $fileSize = 0;
                                $mimeType = 'image/jpeg';

                                if (Storage::disk('public')->exists(str_replace('/storage/', '', $filePath))) {
                                    $fileSize = Storage::disk('public')->size(str_replace('/storage/', '', $filePath));
                                    $mimeType = Storage::disk('public')->mimeType(str_replace('/storage/', '', $filePath)) ?? 'image/jpeg';
                                }

                                DB::table('product_media')->insert([
                                    'id' => $additionalMediaId,
                                    'product_id' => $product->id,
                                    'product_variant_id' => $variantId,
                                    'image_path' => $filePath,
                                    'original_name' => $fileName,
                                    'file_name' => basename($filePath),
                                    'file_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                                    'file_size' => $fileSize,
                                    'mime_type' => $mimeType,
                                    'media_type' => 'image',
                                    'sort_order' => $imageData['sort_order'] ?? ($imageIndex + 1),
                                    'is_cover' => 0, // Not a cover image
                                    'is_featured' => 0,
                                    'is_temporary' => 0,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }

                    // Insert variant with cover image data
                    DB::table('product_variants')->insert([
                        'id' => $variantId,
                        'product_id' => $product->id,
                        'store_id' => $variantData['store_id'] ?? null,
                        'type' => $variantData['type'] ?? null,
                        'attribute_name' => $variantData['color'] ?? null,
                        'attribute_value' => $variantData['value'] ?? null,
                        'sku' => $variantData['sku'] ?? null,
                        'price' => $variantData['price'] ?? $request->price,
                        'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                        'cover_image' => $coverImagePath,
                        'cover_image_name' => $coverImageName,
                        'cover_image_alt' => $coverImageAlt,
                        'cover_image_sort_order' => $coverImageSortOrder,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    Log::info('Variant created with cover:', [
                        'variant_id' => $variantId,
                        'cover_image' => $coverImagePath
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully with variants and images!',
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'redirect_url' => route('admin.products.show', $product->id)
                ],
                'debug_info' => [
                    'processed_data' => [
                        'categories_count' => count($request->input('categories', [])),
                        'variants_count' => count($request->input('variants', [])),
                        'main_images_count' => count($request->input('images', [])),
                        'variant_images_count' => $this->countVariantImages($request->input('variants', [])),
                        'discounts_count' => count($request->input('discounts', []))
                    ]
                ]
            ]);

        } catch (Exception $e) {
            DB::rollback();
            
            Log::error('Product creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage(),
                'debug_info' => [
                    'error_details' => $e->getMessage(),
                    'request_summary' => [
                        'name' => $request->input('name'),
                        'sku' => $request->input('sku'),
                        'variants_count' => count($request->input('variants', [])),
                        'categories_count' => count($request->input('categories', [])),
                        'main_images_count' => count($request->input('images', [])),
                        'variant_images_total' => $this->countVariantImages($request->input('variants', []))
                    ]
                ]
            ], 500);
        }
    }

    /**
     * Count total variant images across all variants
     */
     private function countVariantImages($variants)
    {
        $totalImages = 0;
        foreach ($variants as $variant) {
            if (isset($variant['images']) && is_array($variant['images'])) {
                $totalImages += count($variant['images']);
            }
        }
        return $totalImages;
    }
    /**
     * Handle image upload for dropzone
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:webp,jpeg,png,jpg,gif|max:5120'
        ]);

        try {
            $file = $request->file('file');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('products', $filename, 'public');
            $fullUrl = Storage::url($path);

            return response()->json([
                'id' => Str::uuid(),
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'path' => $fullUrl,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Build category tree for jsTree
     */
    private function buildCategoryTree($parentId = null)
    {
        $categories = DB::table('product_categories')
            ->where('parent_id', $parentId)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $tree = [];
        foreach ($categories as $category) {
            $children = $this->buildCategoryTree($category->id);
            
            $node = [
                'id' => $category->id,
                'text' => $category->name,
                'state' => [
                    'opened' => $parentId === null
                ]
            ];

            if (!empty($children)) {
                $node['children'] = $children;
            }

            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * Get uploaded image path from temp uploads
     */
    private function getUploadedImagePath($uploadId)
    {
        $tempUpload = DB::table('temp_uploads')->where('id', $uploadId)->first();
        
        if ($tempUpload) {
            // Move from temp to permanent location
            $tempPath = str_replace('/storage/', '', $tempUpload->path);
            $permanentPath = 'products/' . $tempUpload->filename;
            
            Storage::disk('public')->move($tempPath, $permanentPath);
            
            // Clean up temp record
            DB::table('temp_uploads')->where('id', $uploadId)->delete();
            
            return Storage::url($permanentPath);
        }

        return null;
    }

    private function getFlatCategoriesForDropdown()
    {
        $categories = DB::table('product_categories')
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->orderBy('path')
            ->orderBy('sort_order')
            ->get();

        $options = [];
        foreach ($categories as $category) {
            $prefix = str_repeat('— ', $category->level);
            $options[] = (object) [
                'id' => $category->id,
                'name' => $prefix . $category->name,
                'level' => $category->level
            ];
        }

        return $options;
    }

    public function edit($id)
    {
        $product = DB::table('products')
            ->select([
                'products.*',
                'brands.name as brand_name',
                'users.first_name as creator_first_name',
                'users.last_name as creator_last_name'
            ])
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('users', 'products.created_by', '=', 'users.id')
            ->where('products.id', $id)
            ->whereNull('products.deleted_at')
            ->first();

        if (!$product) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Product not found!');
        }

        // Product categories
        $categories = DB::table('product_category_relationships as pcr')
            ->join('product_categories as pc', 'pcr.product_category_id', '=', 'pc.id')
            ->where('pcr.product_id', $id)
            ->select('pc.id', 'pc.name', 'pc.slug', 'pcr.is_primary')
            ->get();

        // Variants
        $variants = DB::table('product_variants')
            ->where('product_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->get();

        $variantIds = $variants->pluck('id');

        // Variant attributes (1 query)
        $variantAttributes = DB::table('variant_attributes')
            ->whereIn('variant_id', $variantIds)
            ->get()
            ->groupBy('variant_id');

        // Variant store pricing (1 query)
        $variantStores = DB::table('variant_stores as vs')
            ->join('stores as s', 'vs.store_id', '=', 's.id')
            ->whereIn('vs.variant_id', $variantIds)
            ->select('s.name as store_name', 'vs.*')
            ->get()
            ->groupBy('variant_id');

        // Variant media (1 query)
        $variantMedia = DB::table('product_media')
            ->whereIn('product_variant_id', $variantIds)
            ->orderBy('sort_order')
            ->orderBy('is_primary', 'desc')
            ->orderBy('is_featured', 'desc')
            ->get()
            ->groupBy('product_variant_id');

        // Mapping data ke setiap variant
        foreach ($variants as $variant) {
            $variant->attributes = $variantAttributes->get($variant->id, collect());
            $variant->stores     = $variantStores->get($variant->id, collect());
            $variant->media      = $variantMedia->get($variant->id, collect());
        }


        // All stores
        $stores = DB::table('stores')
            ->orderBy('name')
            ->get();

        // Product media (umum, tanpa variant)
        $media = DB::table('product_media')
            ->where('product_id', $id)
            ->whereNull('product_variant_id')
            ->orderBy('sort_order')
            ->orderBy('is_featured', 'desc')
            ->get();

        // Cover product (1 media featured)
        $coverProduct = DB::table('product_media')
            ->where('product_id', $id)
            ->whereNull('product_variant_id')
            ->where('is_featured', 1)
            ->orderBy('sort_order')
            ->orderBy('is_featured', 'desc')
            ->get();


        // Product stores
        $productStores = DB::table('product_stores as ps')
            ->join('stores as s', 'ps.store_id', '=', 's.id')
            ->where('ps.product_id', $id)
            ->select('s.name as store_name', 'ps.*')
            ->get();

        // SEO data
        $seoData = DB::table('product_seo')
            ->where('product_id', $id)
            ->get()
            ->keyBy('store_id');

        // Tags
        $tags = DB::table('product_tags as pt')
            ->join('tags as t', 'pt.tag_id', '=', 't.id')
            ->where('pt.product_id', $id)
            ->select('t.id', 't.name', 't.slug')
            ->get();

        // Brands
        $brands = DB::table('brands')->get();

        // Category tree
        $categoryTree = $this->buildCategoryTree();
   
        return view('admin.products.edit', compact(
            'coverProduct',
            'variants',
            'stores',
            'product',
            'categoryTree',
            'brands',
            'categories',
            'media',
            'productStores',
            'seoData',
            'tags'
        ));
    }


    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $user_id = (string) $user->id;
        
        try {
            // Find the product
            $product = Product::findOrFail($id);
            
            // Log all incoming data for debugging
            Log::info('Product Update Request Data:', [
                'product_id' => $id,
                'all_data' => $request->all(),
                'files' => $request->allFiles(),
                'existing_variants' => $request->input('existing_variants', []),
                'new_variants' => $request->input('variants', []),
                'categories' => $request->input('categories', []),
                'images' => $request->input('images', []),
                'primary_image' => $request->input('primary_image'),
                'removed_items' => [
                    'removed_main_media' => $request->input('removed_main_media', []),
                    'removed_variant_media' => $request->input('removed_variant_media', []),
                    'removed_variants' => $request->input('removed_variants', [])
                ]
            ]);

            // Enhanced validation
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'sku' => 'required|string|unique:products,sku,' . $id,
                'price' => 'required|numeric|min:0',
                'status' => 'required|in:draft,published,archived',
                'short_description' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'brand_id' => 'nullable|exists:brands,id',
                'type' => 'nullable|string|max:100',
                'barcode' => 'nullable|string|max:100',
                'model' => 'nullable|string|max:100',
                'minimum_quantity' => 'nullable|integer|min:1',
                'sort_order' => 'nullable|integer',
                'track_stock' => 'nullable|boolean',
                'is_featured' => 'nullable|boolean',
                'sale_price' => 'nullable|numeric|min:0',
                'cost_price' => 'nullable|numeric|min:0',
                'weight' => 'nullable|numeric|min:0',
                'length' => 'nullable|numeric|min:0',
                'width' => 'nullable|numeric|min:0',
                'height' => 'nullable|numeric|min:0',
                'tax_status' => 'nullable|in:taxable,none',
                'meta_title' => 'nullable|string|max:60',
                'meta_description' => 'nullable|string|max:160',
                'meta_keywords' => 'nullable|string',
                'slug' => 'nullable|string|unique:products,slug,' . $id,
                
                // Existing variants validation
                'existing_variants' => 'nullable|array',
                'existing_variants.*.id' => 'required|exists:product_variants,id',
                'existing_variants.*.type' => 'nullable|string|max:100',
                'existing_variants.*.color' => 'nullable|string|max:100',
                'existing_variants.*.value' => 'nullable|string|max:100',
                'existing_variants.*.sku' => 'nullable|string',
                'existing_variants.*.price' => 'nullable|numeric|min:0',
                'existing_variants.*.stock_quantity' => 'nullable|integer|min:0',
                'existing_variants.*.new_images' => 'nullable|array',
                'existing_variants.*.keep_media' => 'nullable|array',
                
                // New variants validation
                'variants' => 'nullable|array',
                'variants.*.type' => 'nullable|string|max:100',
                'variants.*.color' => 'nullable|string|max:100',
                'variants.*.value' => 'nullable|string|max:100',
                'variants.*.sku' => 'nullable|string',
                'variants.*.price' => 'nullable|numeric|min:0',
                'variants.*.stock_quantity' => 'nullable|integer|min:0',
                'variants.*.images' => 'nullable|array',
                
                'categories' => 'nullable|array',
                'categories.*' => 'exists:product_categories,id',
                
                'images' => 'nullable|array',
                'primary_image' => 'nullable|string',
                'removed_main_media' => 'nullable|array',
                'removed_variant_media' => 'nullable|array',
                'removed_variants' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'debug_data' => [
                        'request_data' => $request->all(),
                        'validation_rules' => $validator->getRules()
                    ]
                ], 422);
            }

            DB::beginTransaction();

            // Generate slug if not provided or changed
            $slug = $request->slug ?: Str::slug($request->name);
            if ($slug !== $product->slug) {
                $originalSlug = $slug;
                $counter = 1;
                
                while (DB::table('products')->where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            // Prepare product update data
            $updateData = [
                'name' => $request->name,
                'sku' => $request->sku,
                'slug' => $slug,
                'short_description' => $request->short_description,
                'description' => $request->description,
                'price' => $request->price,
                'sale_price' => $request->sale_price,
                'cost_price' => $request->cost_price,
                'brand_id' => $request->brand_id,
                'type' => $request->type,
                'barcode' => $request->barcode,
                'model' => $request->model,
                'minimum_quantity' => $request->minimum_quantity ?? 1,
                'track_stock' => $request->boolean('track_stock'),
                'is_featured' => $request->boolean('is_featured'),
                'status' => $request->status,
                'weight' => $request->weight,
                'length' => $request->length,
                'width' => $request->width,
                'height' => $request->height,
                'tax_status' => $request->tax_status ?? 'taxable',
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'updated_at' => now(),
            ];

            // Handle main product images (TANPA VARIANT)
            if ($request->has('images') && is_array($request->images)) {
                $primaryImageIndex = $request->input('primary_image', 1);
                
                // Get the primary/cover image data
                $coverImage = null;
                foreach ($request->images as $index => $imageData) {
                    if ($index == $primaryImageIndex && !empty($imageData['path'])) {
                        $coverImage = $imageData;
                        break;
                    }
                }
                
                // If no cover found, use first image
                if (!$coverImage && !empty($request->images[1]['path'])) {
                    $coverImage = $request->images[1];
                    $primaryImageIndex = 1;
                }
                
                // Update cover image in products table
                if ($coverImage) {
                    $updateData['cover_image'] = $coverImage['path'];
                    $updateData['cover_image_name'] = $coverImage['name'] ?? basename($coverImage['path']);
                    $updateData['cover_image_alt'] = $coverImage['alt_text'] ?? $request->name;
                    $updateData['cover_image_sort_order'] = $coverImage['sort_order'] ?? 0;
                    
                    Log::info('Main product cover image updated:', [
                        'product_id' => $product->id,
                        'cover_image' => $coverImage['path']
                    ]);
                }
                
                // Delete old main product images (without variant_id)
                $oldMainMedia = DB::table('product_media')
                    ->where('product_id', $product->id)
                    ->whereNull('product_variant_id')
                    ->get();
                
                foreach ($oldMainMedia as $media) {
                    if ($media->image_path && Storage::disk('public')->exists(str_replace('/storage/', '', $media->image_path))) {
                        Storage::disk('public')->delete(str_replace('/storage/', '', $media->image_path));
                    }
                }
                
                DB::table('product_media')
                    ->where('product_id', $product->id)
                    ->whereNull('product_variant_id')
                    ->delete();
                
                // Process all main product images for product_media table
                foreach ($request->images as $index => $imageData) {
                    if (!empty($imageData['path'])) {
                        $isCover = ($index == $primaryImageIndex) ? 1 : 0;
                        
                        $productMediaId = (string) Str::uuid();
                        $filePath = $imageData['path'];
                        $fileName = $imageData['name'] ?? basename($filePath);
                        $fileSize = 0;
                        $mimeType = 'image/jpeg';
                        
                        if (Storage::disk('public')->exists(str_replace('/storage/', '', $filePath))) {
                            $fileSize = Storage::disk('public')->size(str_replace('/storage/', '', $filePath));
                            $mimeType = Storage::disk('public')->mimeType(str_replace('/storage/', '', $filePath)) ?? 'image/jpeg';
                        }
                        
                        DB::table('product_media')->insert([
                            'id' => $productMediaId,
                            'product_id' => $product->id,
                            'product_variant_id' => null,
                            'image_path' => $filePath,
                            'original_name' => $fileName,
                            'file_name' => basename($filePath),
                            'file_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                            'file_size' => $fileSize,
                            'mime_type' => $mimeType,
                            'media_type' => 'image',
                            'sort_order' => $imageData['sort_order'] ?? $index,
                            'is_cover' => $isCover,
                            'is_featured' => 0,
                            'is_temporary' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        Log::info('Main product image inserted:', [
                            'media_id' => $productMediaId,
                            'is_cover' => $isCover,
                            'index' => $index
                        ]);
                    }
                }
                
                Log::info('Main product images processed:', ['images_count' => count($request->images)]);
            }

            // Update main product
            $product->update($updateData);
            Log::info('Product updated:', ['product_id' => $product->id]);

            // Handle Store Assignments
            if ($request->has('stores') && is_array($request->stores)) {
                ProductStore::where('product_id', $product->id)->delete();
                
                foreach ($request->stores as $storeUuid => $storeData) {
                    if (!empty($storeData['store_id'])) {
                        ProductStore::create([
                            'product_id' => $product->id,
                            'store_id'   => $storeData['store_id'],
                            'is_active'  => !empty($storeData['selected']) ? true : false,
                        ]);
                    }
                }
            }

            // Handle Categories
            if ($request->has('categories')) {
                DB::table('product_category_relationships')
                    ->where('product_id', $product->id)
                    ->delete();
                
                if (is_array($request->categories) && !empty($request->categories)) {
                    $categoriesData = [];
                    foreach (array_unique($request->categories) as $categoryId) {
                        $categoriesData[] = [
                            'id' => (string) Str::uuid(),
                            'product_id' => $product->id,
                            'product_category_id' => $categoryId,
                            'is_primary' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    
                    if (!empty($categoriesData)) {
                        DB::table('product_category_relationships')->insert($categoriesData);
                    }
                }
                
                Log::info('Categories updated:', ['categories' => $request->categories ?? []]);
            }

            // Handle removal of main media
            if ($request->has('removed_main_media') && is_array($request->removed_main_media)) {
                foreach ($request->removed_main_media as $mediaId) {
                    $media = DB::table('product_media')->where('id', $mediaId)->first();
                    if ($media && $media->image_path) {
                        if (Storage::disk('public')->exists(str_replace('/storage/', '', $media->image_path))) {
                            Storage::disk('public')->delete(str_replace('/storage/', '', $media->image_path));
                        }
                        DB::table('product_media')->where('id', $mediaId)->delete();
                    }
                }
                Log::info('Removed main media from storage');
            }

            // Handle removal of variant media
            if ($request->has('removed_variant_media') && is_array($request->removed_variant_media)) {
                foreach ($request->removed_variant_media as $mediaId) {
                    $media = DB::table('product_media')->where('id', $mediaId)->first();
                    if ($media) {
                        if ($media->image_path && Storage::disk('public')->exists(str_replace('/storage/', '', $media->image_path))) {
                            Storage::disk('public')->delete(str_replace('/storage/', '', $media->image_path));
                        }
                        DB::table('product_media')->where('id', $mediaId)->delete();
                    }
                }
                Log::info('Removed variant media:', ['count' => count($request->removed_variant_media)]);
            }

            // Handle removal of variants
            if ($request->has('removed_variants') && is_array($request->removed_variants)) {
                foreach ($request->removed_variants as $variantId) {
                    $variantMedia = DB::table('product_media')
                        ->where('product_variant_id', $variantId)
                        ->get();
                    
                    foreach ($variantMedia as $media) {
                        if ($media->image_path && Storage::disk('public')->exists(str_replace('/storage/', '', $media->image_path))) {
                            Storage::disk('public')->delete(str_replace('/storage/', '', $media->image_path));
                        }
                    }
                    
                    DB::table('product_media')->where('product_variant_id', $variantId)->delete();
                    DB::table('product_variants')->where('id', $variantId)->delete();
                }
                Log::info('Removed variants:', ['count' => count($request->removed_variants)]);
            }

            // Handle existing variants updates (DENGAN COVER IMAGE)
            if ($request->has('existing_variants') && is_array($request->existing_variants)) {
                foreach ($request->existing_variants as $variantId => $variantData) {
                    $coverImagePath = null;
                    $coverImageName = null;
                    $coverImageAlt = null;
                    $coverImageSortOrder = null;
                    
                    // Check if variant has new images
                    if (!empty($variantData['new_images']) && is_array($variantData['new_images'])) {
                        // Delete old variant images first
                        $oldVariantMedia = DB::table('product_media')
                            ->where('product_variant_id', $variantId)
                            ->get();
                        
                        foreach ($oldVariantMedia as $media) {
                            if ($media->image_path && Storage::disk('public')->exists(str_replace('/storage/', '', $media->image_path))) {
                                Storage::disk('public')->delete(str_replace('/storage/', '', $media->image_path));
                            }
                        }
                        
                        DB::table('product_media')
                            ->where('product_variant_id', $variantId)
                            ->delete();
                        
                        $images = array_values($variantData['new_images']);
                        
                        // First new image becomes the cover
                        $firstImage = $images[0] ?? null;
                        if ($firstImage && !empty($firstImage['path'])) {
                            $coverImagePath = $firstImage['path'];
                            $coverImageName = $firstImage['name'] ?? basename($firstImage['path']);
                            $coverImageAlt = $firstImage['alt_text'] ?? '';
                            $coverImageSortOrder = $firstImage['sort_order'] ?? 0;
                            
                            // Insert first image as cover
                            $productMediaId = (string) Str::uuid();
                            $filePath = $firstImage['path'];
                            $fileName = $firstImage['name'] ?? basename($filePath);
                            $fileSize = 0;
                            $mimeType = 'image/jpeg';
                            
                            if (Storage::disk('public')->exists(str_replace('/storage/', '', $filePath))) {
                                $fileSize = Storage::disk('public')->size(str_replace('/storage/', '', $filePath));
                                $mimeType = Storage::disk('public')->mimeType(str_replace('/storage/', '', $filePath)) ?? 'image/jpeg';
                            }
                            
                            DB::table('product_media')->insert([
                                'id' => $productMediaId,
                                'product_id' => $product->id,
                                'product_variant_id' => $variantId,
                                'image_path' => $filePath,
                                'original_name' => $fileName,
                                'file_name' => basename($filePath),
                                'file_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                                'file_size' => $fileSize,
                                'mime_type' => $mimeType,
                                'media_type' => 'image',
                                'sort_order' => $coverImageSortOrder,
                                'is_cover' => 1, // First image is cover
                                'is_featured' => 0,
                                'is_temporary' => 0,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                        
                        // Process additional images (not cover)
                        foreach (array_slice($images, 1) as $imageIndex => $imageData) {
                            if (!empty($imageData['path'])) {
                                $additionalMediaId = (string) Str::uuid();
                                $filePath = $imageData['path'];
                                $fileName = $imageData['name'] ?? basename($filePath);
                                $fileSize = 0;
                                $mimeType = 'image/jpeg';
                                
                                if (Storage::disk('public')->exists(str_replace('/storage/', '', $filePath))) {
                                    $fileSize = Storage::disk('public')->size(str_replace('/storage/', '', $filePath));
                                    $mimeType = Storage::disk('public')->mimeType(str_replace('/storage/', '', $filePath)) ?? 'image/jpeg';
                                }
                                
                                DB::table('product_media')->insert([
                                    'id' => $additionalMediaId,
                                    'product_id' => $product->id,
                                    'product_variant_id' => $variantId,
                                    'image_path' => $filePath,
                                    'original_name' => $fileName,
                                    'file_name' => basename($filePath),
                                    'file_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                                    'file_size' => $fileSize,
                                    'mime_type' => $mimeType,
                                    'media_type' => 'image',
                                    'sort_order' => $imageData['sort_order'] ?? ($imageIndex + 1),
                                    'is_cover' => 0, // Not a cover
                                    'is_featured' => 0,
                                    'is_temporary' => 0,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                    
                    // Update variant with cover image data
                    $variantUpdate = [
                        'store_id' => $variantData['store_id'] ?? null,
                        'type' => $variantData['type'] ?? null,
                        'attribute_name' => $variantData['color'] ?? null,
                        'attribute_value' => $variantData['value'] ?? null,
                        'sku' => $variantData['sku'] ?? null,
                        'price' => $variantData['price'] ?? null,
                        'sale_price' => $variantData['sale_price'] ?? null,
                        'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                        'updated_at' => now(),
                    ];
                    
                    // Only update cover if we have new cover image
                    if ($coverImagePath) {
                        $variantUpdate['cover_image'] = $coverImagePath;
                        $variantUpdate['cover_image_name'] = $coverImageName;
                        $variantUpdate['cover_image_alt'] = $coverImageAlt;
                        $variantUpdate['cover_image_sort_order'] = $coverImageSortOrder;
                    }
                    
                    DB::table('product_variants')
                        ->where('id', $variantId)
                        ->update($variantUpdate);

                    Log::info('Existing variant updated:', [
                        'variant_id' => $variantId,
                        'new_images_count' => count($variantData['new_images'] ?? []),
                        'cover_updated' => !empty($coverImagePath)
                    ]);
                }
                
                Log::info('All existing variants updated:', ['variants_count' => count($request->existing_variants)]);
            }

            // Handle new variants (DENGAN COVER IMAGE)
            if ($request->has('variants') && is_array($request->variants)) {
                foreach ($request->variants as $variantIndex => $variantData) {
                    $variantId = (string) Str::uuid();
                    $coverImagePath = null;
                    $coverImageName = null;
                    $coverImageAlt = null;
                    $coverImageSortOrder = null;

                    // Handle variant images
                    if (!empty($variantData['images']) && is_array($variantData['images'])) {
                        $images = array_values($variantData['images']);

                        // First image is the cover
                        $firstImage = $images[0] ?? null;
                        if ($firstImage && !empty($firstImage['path'])) {
                            $productMediaId = (string) Str::uuid();
                            $filePath = $firstImage['path'];
                            $fileName = $firstImage['name'] ?? basename($filePath);
                            $fileSize = 0;
                            $mimeType = 'image/jpeg';

                            if (Storage::disk('public')->exists(str_replace('/storage/', '', $filePath))) {
                                $fileSize = Storage::disk('public')->size(str_replace('/storage/', '', $filePath));
                                $mimeType = Storage::disk('public')->mimeType(str_replace('/storage/', '', $filePath)) ?? 'image/jpeg';
                            }

                            // Store cover data
                            $coverImagePath = $filePath;
                            $coverImageName = $fileName;
                            $coverImageAlt = $firstImage['alt_text'] ?? '';
                            $coverImageSortOrder = $firstImage['sort_order'] ?? 0;

                            DB::table('product_media')->insert([
                                'id' => $productMediaId,
                                'product_id' => $product->id,
                                'product_variant_id' => $variantId,
                                'image_path' => $filePath,
                                'original_name' => $fileName,
                                'file_name' => basename($filePath),
                                'file_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                                'file_size' => $fileSize,
                                'mime_type' => $mimeType,
                                'media_type' => 'image',
                                'sort_order' => $coverImageSortOrder,
                                'is_cover' => 0, // First image is cover
                                'is_featured' => 0,
                                'is_temporary' => 0,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        // Additional images
                        foreach (array_slice($images, 1) as $imageIndex => $imageData) {
                            if (!empty($imageData['path'])) {
                                $additionalMediaId = (string) Str::uuid();
                                $filePath = $imageData['path'];
                                $fileName = $imageData['name'] ?? basename($filePath);
                                $fileSize = 0;
                                $mimeType = 'image/jpeg';

                                if (Storage::disk('public')->exists(str_replace('/storage/', '', $filePath))) {
                                    $fileSize = Storage::disk('public')->size(str_replace('/storage/', '', $filePath));
                                    $mimeType = Storage::disk('public')->mimeType(str_replace('/storage/', '', $filePath)) ?? 'image/jpeg';
                                }

                                DB::table('product_media')->insert([
                                    'id' => $additionalMediaId,
                                    'product_id' => $product->id,
                                    'product_variant_id' => $variantId,
                                    'image_path' => $filePath,
                                    'original_name' => $fileName,
                                    'file_name' => basename($filePath),
                                    'file_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                                    'file_size' => $fileSize,
                                    'mime_type' => $mimeType,
                                    'media_type' => 'image',
                                    'sort_order' => $imageData['sort_order'] ?? ($imageIndex + 1),
                                    'is_cover' => 0,
                                    'is_featured' => 0,
                                    'is_temporary' => 0,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }

                    // Insert new variant with cover image data
                    DB::table('product_variants')->insert([
                        'id' => $variantId,
                        'product_id' => $product->id,
                        'store_id' => $variantData['store_id'] ?? null,
                        'type' => $variantData['type'] ?? null,
                        'attribute_name' => $variantData['color'] ?? null,
                        'attribute_value' => $variantData['value'] ?? null,
                        'sku' => $variantData['sku'] ?? null,
                        'price' => $variantData['price'] ?? $product->price,
                        'sale_price' => $variantData['sale_price'] ?? null,
                        'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                        'cover_image' => $coverImagePath,
                        'cover_image_name' => $coverImageName,
                        'cover_image_alt' => $coverImageAlt,
                        'cover_image_sort_order' => $coverImageSortOrder,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    Log::info('New variant created:', [
                        'variant_id' => $variantId,
                        'variant_index' => $variantIndex,
                        'cover_image' => $coverImagePath,
                        'images_count' => count($variantData['images'] ?? [])
                    ]);
                }

                Log::info('All new variants created:', ['variants_count' => count($request->variants)]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully!',
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'cover_image' => $product->cover_image,
                    'redirect_url' => route('admin.products.show', $product->id)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Product Update Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $product = DB::table('products')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found!'], 404);
        }

        DB::beginTransaction();
        try {

            DB::table('product_variants')
                ->where('product_id', $id)
                ->delete();

             DB::table('product_stores')
                ->where('product_id', $id)
                ->delete();

            DB::table('product_category_relationships')
                ->where('product_id', $id)
                ->delete();

            DB::table('product_tags')
                ->where('product_id', $id)
                ->delete();

            DB::table('products')
                ->where('id', $id)
                ->delete();

            DB::commit();

             return redirect()
                ->route('admin.products.index')
                ->with('success', 'Data deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
        return response()->json(['error' => 'Failed to delete product: ' . $e->getMessage()], 500);
        }

        // Soft delete
        DB::table('products')
            ->where('id', $id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now()
            ]);

        // Soft delete variants
        DB::table('product_variants')
            ->where('product_id', $id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now()
            ]);

        return response()->json(['success' => 'Product deleted successfully!']);
    }

    // public function uploadImages(Request $request, $id)
    // {
    //     $request->validate([
    //         'images' => 'required|array',
    //         'images.*' => 'required|image|mimes:webp,jpeg,png,jpg,gif|max:2048',
    //     ]);

    //     $product = DB::table('products')
    //         ->where('id', $id)
    //         ->whereNull('deleted_at')
    //         ->first();

    //     if (!$product) {
    //         return response()->json(['error' => 'Product not found!'], 404);
    //     }

    //     $uploadedFiles = [];
    //     $maxSortOrder = DB::table('product_media')
    //         ->where('product_id', $id)
    //         ->max('sort_order') ?? -1;

    //     foreach ($request->file('images') as $index => $image) {
    //         $path = $image->store('products/images', 'public');
            
    //         $mediaId = Str::uuid();
            
    //         DB::table('product_media')->insert([
    //             'id' => $mediaId,
    //             'product_id' => $id,
    //             'file_path' => $path,
    //             'file_name' => pathinfo($path, PATHINFO_BASENAME),
    //             'original_name' => $image->getClientOriginalName(),
    //             'file_type' => $image->getClientOriginalExtension(),
    //             'file_size' => $image->getSize(),
    //             'mime_type' => $image->getMimeType(),
    //             'media_type' => 'image',
    //             'sort_order' => $maxSortOrder + $index + 1,
    //             'is_featured' => false,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //         $uploadedFiles[] = [
    //             'id' => $mediaId,
    //             'url' => Storage::url($path),
    //             'original_name' => $image->getClientOriginalName(),
    //             'sort_order' => $maxSortOrder + $index + 1,
    //         ];
    //     }

    //     return response()->json([
    //         'success' => 'Images uploaded successfully!',
    //         'files' => $uploadedFiles
    //     ]);
    // }

    public function deleteImage($productId, $mediaId)
    {
        $media = DB::table('product_media')
            ->where('id', $mediaId)
            ->where('product_id', $productId)
            ->first();

        if (!$media) {
            return response()->json(['error' => 'Image not found!'], 404);
        }

        // Delete file
        Storage::disk('public')->delete($media->file_path);

        // Delete record
        DB::table('product_media')
            ->where('id', $mediaId)
            ->delete();

        return response()->json(['success' => 'Image deleted successfully!']);
    }

    public function reorderImages(Request $request, $id)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*.id' => 'required|uuid|exists:product_media,id',
            'images.*.sort_order' => 'required|integer|min:0',
            'images.*.is_featured' => 'boolean',
        ]);

        DB::beginTransaction();
        
        try {
            foreach ($request->images as $imageData) {
                DB::table('product_media')
                    ->where('id', $imageData['id'])
                    ->where('product_id', $id)
                    ->update([
                        'sort_order' => $imageData['sort_order'],
                        'is_featured' => $imageData['is_featured'] ?? false,
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();
            return response()->json(['success' => 'Images reordered successfully!']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to reorder images: ' . $e->getMessage()], 500);
        }
    }

    public function getVariantsByProduct($id)
    {
        $variants = DB::table('product_variants')
            ->where('product_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->get();

        foreach ($variants as $variant) {
            $variant->attributes = DB::table('variant_attributes')
                ->where('variant_id', $variant->id)
                ->get();

            $variant->stores = DB::table('variant_stores as vs')
                ->join('stores as s', 'vs.store_id', '=', 's.id')
                ->where('vs.variant_id', $variant->id)
                ->select('s.name as store_name', 'vs.*')
                ->get();
        }

        return response()->json($variants);
    }
}