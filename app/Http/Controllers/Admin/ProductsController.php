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
            ->where('is_featured', 1)
            ->orderBy('sort_order')
            ->orderBy('is_featured', 'desc')
            ->get();

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
            // Log all incoming data for debugging
            Log::info('Product Store Request Data:', [
                'all_data' => $request->all(),
                'files' => $request->allFiles(),
                'variants' => $request->input('variants', []),
                'categories' => $request->input('categories', []),
                'images' => $request->input('images', []),
                'discounts' => $request->input('discounts', [])
            ]);

            // Enhanced validation to include variant images
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
                
                // Validation for variants and their images
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

            // Handle Main Product Images
            if ($request->has('images') && is_array($request->images)) {
                $primaryImageIndex = $request->input('primary_image', 0);
                
                foreach ($request->images as $index => $imageData) {
                    if (!empty($imageData['path'])) {
                        $productMediaId = (string) Str::uuid();
                        
                        // Extract file info from path
                        $filePath = $imageData['path'];
                        $fileName = $imageData['name'] ?? basename($filePath);
                        $fileSize = 0;
                        $mimeType = 'image/jpeg';
                        
                        // Try to get file info if it exists
                        if (Storage::disk('public')->exists(str_replace('/storage/', '', $filePath))) {
                            $fileSize = Storage::disk('public')->size(str_replace('/storage/', '', $filePath));
                            $mimeType = Storage::disk('public')->mimeType(str_replace('/storage/', '', $filePath)) ?? 'image/jpeg';
                        }
                        
                        DB::table('product_media')->insert([
                            'id' => $productMediaId,
                            'product_id' => $product->id,
                            'product_variant_id' => null, // Main product images
                            'image_path' => $filePath,
                            'original_name' => $fileName,
                            'file_name' => basename($filePath),
                            'file_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                            'file_size' => $fileSize,
                            'mime_type' => $mimeType,
                            'media_type' => 'image',
                            'sort_order' => $imageData['sort_order'] ?? $index,
                            'is_primary' => ($index == $primaryImageIndex) ? 1 : 0,
                            'is_featured' => $request->boolean('is_featured'),
                            'is_temporary' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
                
                Log::info('Main product images attached:', ['images_count' => count($request->images)]);
            }

           // Handle Variants and their Images
            if ($request->has('variants') && is_array($request->variants)) {
                foreach ($request->variants as $variantIndex => $variantData) {
                    $variantId = (string) Str::uuid();
                    $productMediaId = null;

                    // Handle variant images safely
                    if (!empty($variantData['images']) && is_array($variantData['images'])) {
                        $images = array_values($variantData['images']); // pastikan numerik index

                        // ===== First image =====
                        $firstImage = $images[0] ?? null;
                        if ($firstImage && !empty($firstImage['path'])) {
                            $productMediaId = (string) Str::uuid();

                            $filePath = $firstImage['path'];
                            $fileName = $firstImage['name'] ?? basename($filePath);
                            $fileSize = 0;
                            $mimeType = 'image/jpeg';

                            // cek file kalau ada di storage
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
                                'sort_order' => $firstImage['sort_order'] ?? 0,
                                'is_primary' => 1,
                                'is_featured' => 0,
                                'is_temporary' => 0,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        // ===== Additional images =====
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
                                    'is_primary' => 0,
                                    'is_featured' => 0,
                                    'is_temporary' => 0,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    } else {
                        Log::warning('Variant has no valid images', [
                            'variant_index' => $variantIndex,
                            'variant_data'  => $variantData
                        ]);
                    }

                    // ===== Insert variant with product_media_id reference =====
                    DB::table('product_variants')->insert([
                        'id' => $variantId,
                        'product_id' => $product->id,
                        'product_media_id' => $productMediaId,
                        'store_id' => $variantData['store_id'] ?? null,
                        'type' => $variantData['type'] ?? null,
                        'attribute_name' => $variantData['color'] ?? null,
                        'attribute_value' => $variantData['value'] ?? null,
                        'sku' => $variantData['sku'] ?? null,
                        'price' => $variantData['price'] ?? $request->price,
                        'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    Log::info('Variant created:', [
                        'variant_id' => $variantId,
                        'variant_index' => $variantIndex,
                        'product_media_id' => $productMediaId,
                        'store_id' => $variantData['store_id'] ?? null,
                        'variant_data' => $variantData
                    ]);
                }

                Log::info('All variants created:', ['variants_count' => count($request->variants)]);
            }


            // Handle Discounts (uncomment if you want to use this)
            /*
            if ($request->has('discounts') && is_array($request->discounts)) {
                $discountsData = [];
                
                foreach ($request->discounts as $discountData) {
                    $discountsData[] = [
                        'id' => (string) Str::uuid(),
                        'product_id' => $product->id,
                        'customer_group_id' => $discountData['customer_group_id'] ?? null,
                        'quantity' => $discountData['quantity'] ?? 1,
                        'type' => $discountData['type'] ?? 'percentage',
                        'value' => $discountData['value'] ?? 0,
                        'start_date' => $discountData['start_date'] ?? null,
                        'end_date' => $discountData['end_date'] ?? null,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                if (!empty($discountsData)) {
                    DB::table('product_discounts')->insert($discountsData);
                }
                
                Log::info('Discounts created:', ['discounts_count' => count($request->discounts)]);
            }
            */

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

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:200',
    //         'slug' => 'required|string|max:200|unique:products,slug',
    //         'short_description' => 'nullable|string',
    //         'description' => 'nullable|string',
    //         'sku' => 'nullable|string|max:100|unique:products,sku',
    //         'brand_id' => 'nullable|uuid|exists:brands,id',
    //         'type' => 'required|in:simple,variable,grouped,external,digital',
    //         'status' => 'required|in:draft,published,archived,out_of_stock',
    //         'featured' => 'boolean',
    //         'weight' => 'nullable|numeric|min:0',
    //         'dimensions_length' => 'nullable|numeric|min:0',
    //         'dimensions_width' => 'nullable|numeric|min:0',
    //         'dimensions_height' => 'nullable|numeric|min:0',
    //         'requires_shipping' => 'boolean',
    //         'is_digital' => 'boolean',
    //         'download_limit' => 'nullable|integer|min:1',
    //         'download_expiry' => 'nullable|integer|min:1',
    //         'external_url' => 'nullable|url',
    //         'button_text' => 'nullable|string|max:100',
    //         'categories' => 'nullable|array',
    //         'categories.*' => 'uuid|exists:product_categories,id',
    //         'primary_category' => 'nullable|uuid|exists:product_categories,id',
    //         'stores' => 'nullable|array',
    //         'stores.*' => 'uuid|exists:stores,id',
    //         'tags' => 'nullable|array',
    //         'tags.*' => 'uuid|exists:tags,id',
    //         'variants' => 'nullable|array',
    //         'variants.*.sku' => 'required|string|max:100|unique:product_variants,sku',
    //         'variants.*.name' => 'nullable|string|max:200',
    //         'variants.*.attributes' => 'nullable|array',
    //         'variants.*.stores' => 'nullable|array',
    //         'images' => 'nullable|array',
    //         'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    //         'seo' => 'nullable|array',
    //     ]);

    //     DB::beginTransaction();
        
    //     try {
    //         $productId = Str::uuid();

    //         $data = [
    //             'id' => $productId,
    //             'name' => $request->name,
    //             'slug' => $request->slug,
    //             'short_description' => $request->short_description,
    //             'description' => $request->description,
    //             'sku' => $request->sku,
    //             'brand_id' => $request->brand_id,
    //             'type' => $request->type,
    //             'status' => $request->status,
    //             'featured' => $request->boolean('featured'),
    //             'weight' => $request->weight,
    //             'dimensions_length' => $request->dimensions_length,
    //             'dimensions_width' => $request->dimensions_width,
    //             'dimensions_height' => $request->dimensions_height,
    //             'requires_shipping' => $request->boolean('requires_shipping', true),
    //             'is_digital' => $request->boolean('is_digital'),
    //             'download_limit' => $request->download_limit,
    //             'download_expiry' => $request->download_expiry,
    //             'external_url' => $request->external_url,
    //             'button_text' => $request->button_text,
    //             'created_by' => session('admin_user_id'),
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ];

    //         DB::table('products')->insert($data);

    //         // Handle categories
    //         if ($request->has('categories') && is_array($request->categories)) {
    //             foreach ($request->categories as $categoryId) {
    //                 DB::table('product_category_relationships')->insert([
    //                     'id' => Str::uuid(),
    //                     'product_id' => $productId,
    //                     'category_id' => $categoryId,
    //                     'is_primary' => $categoryId === $request->primary_category,
    //                     'created_at' => now(),
    //                 ]);
    //             }
    //         }

    //         // Handle store relationships
    //         if ($request->has('stores') && is_array($request->stores)) {
    //             foreach ($request->stores as $storeId) {
    //                 DB::table('product_stores')->insert([
    //                     'id' => Str::uuid(),
    //                     'product_id' => $productId,
    //                     'store_id' => $storeId,
    //                     'is_active' => true,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             }
    //         }

    //         // Handle tags
    //         if ($request->has('tags') && is_array($request->tags)) {
    //             foreach ($request->tags as $tagId) {
    //                 DB::table('product_tags')->insert([
    //                     'id' => Str::uuid(),
    //                     'product_id' => $productId,
    //                     'tag_id' => $tagId,
    //                     'created_at' => now(),
    //                 ]);
    //             }
    //         }

    //         // Handle variants
    //         if ($request->has('variants') && is_array($request->variants)) {
    //             foreach ($request->variants as $index => $variantData) {
    //                 $variantId = Str::uuid();
                    
    //                 DB::table('product_variants')->insert([
    //                     'id' => $variantId,
    //                     'product_id' => $productId,
    //                     'sku' => $variantData['sku'],
    //                     'name' => $variantData['name'] ?? null,
    //                     'description' => $variantData['description'] ?? null,
    //                     'weight' => $variantData['weight'] ?? null,
    //                     'dimensions_length' => $variantData['dimensions_length'] ?? null,
    //                     'dimensions_width' => $variantData['dimensions_width'] ?? null,
    //                     'dimensions_height' => $variantData['dimensions_height'] ?? null,
    //                     'sort_order' => $index,
    //                     'status' => 'active',
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);

    //                 // Handle variant attributes
    //                 if (isset($variantData['attributes']) && is_array($variantData['attributes'])) {
    //                     foreach ($variantData['attributes'] as $attrName => $attrValue) {
    //                         DB::table('variant_attributes')->insert([
    //                             'id' => Str::uuid(),
    //                             'variant_id' => $variantId,
    //                             'attribute_name' => $attrName,
    //                             'attribute_value' => $attrValue,
    //                             'created_at' => now(),
    //                         ]);
    //                     }
    //                 }

    //                 // Handle variant store pricing
    //                 if (isset($variantData['stores']) && is_array($variantData['stores'])) {
    //                     foreach ($variantData['stores'] as $storeId => $storeData) {
    //                         DB::table('variant_stores')->insert([
    //                             'id' => Str::uuid(),
    //                             'variant_id' => $variantId,
    //                             'store_id' => $storeId,
    //                             'price' => $storeData['price'],
    //                             'sale_price' => $storeData['sale_price'] ?? null,
    //                             'cost_price' => $storeData['cost_price'] ?? null,
    //                             'stock_quantity' => $storeData['stock_quantity'] ?? 0,
    //                             'min_stock_level' => $storeData['min_stock_level'] ?? 0,
    //                             'max_stock_level' => $storeData['max_stock_level'] ?? null,
    //                             'manage_stock' => $storeData['manage_stock'] ?? true,
    //                             'stock_status' => $storeData['stock_status'] ?? 'in_stock',
    //                             'is_active' => true,
    //                             'created_at' => now(),
    //                             'updated_at' => now(),
    //                         ]);
    //                     }
    //                 }
    //             }
    //         }

    //         // Handle images
    //         if ($request->hasFile('images')) {
    //             foreach ($request->file('images') as $index => $image) {
    //                 $path = $image->store('products/images', 'public');
                    
    //                 DB::table('product_media')->insert([
    //                     'id' => Str::uuid(),
    //                     'product_id' => $productId,
    //                     'file_path' => $path,
    //                     'file_name' => pathinfo($path, PATHINFO_BASENAME),
    //                     'original_name' => $image->getClientOriginalName(),
    //                     'file_type' => $image->getClientOriginalExtension(),
    //                     'file_size' => $image->getSize(),
    //                     'mime_type' => $image->getMimeType(),
    //                     'media_type' => 'image',
    //                     'sort_order' => $index,
    //                     'is_featured' => $index === 0,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             }
    //         }

    //         // Handle SEO data
    //         if ($request->has('seo') && is_array($request->seo)) {
    //             foreach ($request->seo as $storeId => $seoData) {
    //                 if (empty(array_filter($seoData))) continue;

    //                 DB::table('product_seo')->insert([
    //                     'id' => Str::uuid(),
    //                     'product_id' => $productId,
    //                     'store_id' => $storeId === 'global' ? null : $storeId,
    //                     'meta_title' => $seoData['meta_title'] ?? null,
    //                     'meta_description' => $seoData['meta_description'] ?? null,
    //                     'meta_keywords' => $seoData['meta_keywords'] ?? null,
    //                     'og_title' => $seoData['og_title'] ?? null,
    //                     'og_description' => $seoData['og_description'] ?? null,
    //                     'og_image' => $seoData['og_image'] ?? null,
    //                     'canonical_url' => $seoData['canonical_url'] ?? null,
    //                     'robots' => $seoData['robots'] ?? 'index,follow',
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             }
    //         }

    //         DB::commit();

    //         return redirect()->route('admin.products.index')
    //             ->with('success', 'Product created successfully!');

    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         return back()->withInput()->with('error', 'Failed to create product: ' . $e->getMessage());
    //     }
    // }

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

            // Update main product
            $product->update([
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
            ]);

            Log::info('Product updated:', ['product_id' => $product->id]);

            // Handle Store Assignments
            if ($request->has('stores') && is_array($request->stores)) {
                // Remove all existing store assignments
                ProductStore::where('product_id', $product->id)->delete();
                
                // Add new store assignments
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
                // Remove existing category relationships
                DB::table('product_category_relationships')
                    ->where('product_id', $product->id)
                    ->delete();
                
                // Add new category relationships
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
                    if ($media) {
                        // Delete file from storage
                        if ($media->image_path && Storage::disk('public')->exists(str_replace('/storage/', '', $media->image_path))) {
                            Storage::disk('public')->delete(str_replace('/storage/', '', $media->image_path));
                        }
                        // Delete from database
                        DB::table('product_media')->where('id', $mediaId)->delete();
                    }
                }
                Log::info('Removed main media:', ['count' => count($request->removed_main_media)]);
            }

            // Handle removal of variant media
            if ($request->has('removed_variant_media') && is_array($request->removed_variant_media)) {
                foreach ($request->removed_variant_media as $mediaId) {
                    $media = DB::table('product_media')->where('id', $mediaId)->first();
                    if ($media) {
                        // Delete file from storage
                        if ($media->image_path && Storage::disk('public')->exists(str_replace('/storage/', '', $media->image_path))) {
                            Storage::disk('public')->delete(str_replace('/storage/', '', $media->image_path));
                        }
                        // Delete from database
                        DB::table('product_media')->where('id', $mediaId)->delete();
                    }
                }
                Log::info('Removed variant media:', ['count' => count($request->removed_variant_media)]);
            }

            // Handle removal of variants
            if ($request->has('removed_variants') && is_array($request->removed_variants)) {
                foreach ($request->removed_variants as $variantId) {
                    // Delete variant media first
                    $variantMedia = DB::table('product_media')
                        ->where('product_variant_id', $variantId)
                        ->get();
                    
                    foreach ($variantMedia as $media) {
                        if ($media->image_path && Storage::disk('public')->exists(str_replace('/storage/', '', $media->image_path))) {
                            Storage::disk('public')->delete(str_replace('/storage/', '', $media->image_path));
                        }
                    }
                    
                    // Delete variant media records
                    DB::table('product_media')->where('product_variant_id', $variantId)->delete();
                    
                    // Delete variant
                    DB::table('product_variants')->where('id', $variantId)->delete();
                }
                Log::info('Removed variants:', ['count' => count($request->removed_variants)]);
            }

            // Handle new main product images
            if ($request->has('images') && is_array($request->images)) {
                $primaryImageIndex = $request->input('primary_image', 0);
                
                foreach ($request->images as $index => $imageData) {
                    if (!empty($imageData['path'])) {
                        $productMediaId = (string) Str::uuid();
                        
                        // Extract file info from path
                        $filePath = $imageData['path'];
                        $fileName = $imageData['name'] ?? basename($filePath);
                        $fileSize = 0;
                        $mimeType = 'image/jpeg';
                        
                        // Try to get file info if it exists
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
                            'is_primary' => ($index == $primaryImageIndex) ? 1 : 0,
                            'is_featured' => $product->is_featured,
                            'is_temporary' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
                
                Log::info('New main product images added:', ['images_count' => count($request->images)]);
            }

            // Handle existing variants updates
            if ($request->has('existing_variants') && is_array($request->existing_variants)) {
                foreach ($request->existing_variants as $variantId => $variantData) {
                    // Update existing variant
                    DB::table('product_variants')
                        ->where('id', $variantId)
                        ->update([
                            'store_id' => $variantData['store_id'] ?? null,
                            'type' => $variantData['type'] ?? null,
                            'attribute_name' => $variantData['color'] ?? null,
                            'attribute_value' => $variantData['value'] ?? null,
                            'sku' => $variantData['sku'] ?? null,
                            'price' => $variantData['price'] ?? $product->price,
                            'sale_price' => $variantData['sale_price'] ?? null,
                            'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                            'updated_at' => now(),
                        ]);

                    // Handle new images for existing variants
                    if (!empty($variantData['new_images']) && is_array($variantData['new_images'])) {
                        foreach ($variantData['new_images'] as $imageIndex => $imageData) {
                            if (!empty($imageData['path'])) {
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
                                    'product_variant_id' => $variantId,
                                    'image_path' => $filePath,
                                    'original_name' => $fileName,
                                    'file_name' => basename($filePath),
                                    'file_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                                    'file_size' => $fileSize,
                                    'mime_type' => $mimeType,
                                    'media_type' => 'image',
                                    'sort_order' => $imageData['sort_order'] ?? $imageIndex,
                                    'is_primary' => $imageIndex === 0 ? 1 : 0,
                                    'is_featured' => 0,
                                    'is_temporary' => 0,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }

                    Log::info('Existing variant updated:', [
                        'variant_id' => $variantId,
                        'new_images_count' => count($variantData['new_images'] ?? [])
                    ]);
                }
                
                Log::info('All existing variants updated:', ['variants_count' => count($request->existing_variants)]);
            }

            // Handle new variants
            if ($request->has('variants') && is_array($request->variants)) {
                foreach ($request->variants as $variantIndex => $variantData) {
                    $variantId = (string) Str::uuid();
                    $productMediaId = null;

                    // Handle variant images
                    if (!empty($variantData['images']) && is_array($variantData['images'])) {
                        $images = array_values($variantData['images']);

                        // First image
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
                                'sort_order' => $firstImage['sort_order'] ?? 0,
                                'is_primary' => 1,
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
                                    'is_primary' => 0,
                                    'is_featured' => 0,
                                    'is_temporary' => 0,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }

                    // Insert new variant
                    DB::table('product_variants')->insert([
                        'id' => $variantId,
                        'product_id' => $product->id,
                        'product_media_id' => $productMediaId,
                        'store_id' => $variantData['store_id'] ?? null,
                        'type' => $variantData['type'] ?? null,
                        'attribute_name' => $variantData['color'] ?? null,
                        'attribute_value' => $variantData['value'] ?? null,
                        'sku' => $variantData['sku'] ?? null,
                        'price' => $variantData['price'] ?? $product->price,
                        'sale_price' => $variantData['sale_price'] ?? null,
                        'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    Log::info('New variant created:', [
                        'variant_id' => $variantId,
                        'variant_index' => $variantIndex,
                        'product_media_id' => $productMediaId,
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
                    'redirect_url' => route('admin.products.show', $product->id)
                ],
                'debug_info' => [
                    'processed_data' => [
                        'updated_product' => true,
                        'categories_count' => count($request->input('categories', [])),
                        'existing_variants_count' => count($request->input('existing_variants', [])),
                        'new_variants_count' => count($request->input('variants', [])),
                        'new_main_images_count' => count($request->input('images', [])),
                        'removed_main_media_count' => count($request->input('removed_main_media', [])),
                        'removed_variant_media_count' => count($request->input('removed_variant_media', [])),
                        'removed_variants_count' => count($request->input('removed_variants', []))
                    ]
                ]
            ]);

        } catch (Exception $e) {
            DB::rollback();
            
            Log::error('Product update failed:', [
                'product_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage(),
                'debug_info' => [
                    'error_details' => $e->getMessage(),
                    'product_id' => $id,
                    'request_summary' => [
                        'name' => $request->input('name'),
                        'sku' => $request->input('sku'),
                        'existing_variants_count' => count($request->input('existing_variants', [])),
                        'new_variants_count' => count($request->input('variants', [])),
                        'categories_count' => count($request->input('categories', [])),
                        'new_main_images_count' => count($request->input('images', [])),
                    ]
                ]
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