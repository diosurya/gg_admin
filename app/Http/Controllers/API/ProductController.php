<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Carbon\Carbon;

class ProductController extends Controller
{

    public function index(Request $request, $storeId)
    {
        $store = DB::table('stores')
            ->where('id', $storeId)
            ->select('id', 'name', 'slug', 'address', 'phone')
            ->first();

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found',
                'data' => null,
                'meta' => null
            ], 404);
        }

        $products = DB::table('products')
            ->join('product_stores', function ($join) use ($storeId) {
                    $join->on('products.id', '=', 'product_stores.product_id')
                        ->where('product_stores.store_id', '=', $storeId);
            })
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->select([
                'products.*',
                'brands.id as brand_id',
                'brands.name as brand_name',
            ])
            ->paginate(12);

        $productIds = collect($products->items())->pluck('id')->all();

        $variantsAll = DB::table('product_variants')
            ->leftJoin('variant_stores', function ($join) use ($storeId) {
                $join->on('product_variants.id', '=', 'variant_stores.variant_id')
                    ->where('variant_stores.store_id', '=', $storeId);
            })
            ->whereIn('product_variants.product_id', $productIds)
            ->select([
                'product_variants.*',
            ])
            ->get();

        $variantIds = $variantsAll->pluck('id')->all();

        $media = DB::table('product_media')
            ->whereIn('product_id', $productIds)
            ->orWhereIn('product_variant_id', $variantIds)
            ->select('id', 'product_id', 'product_variant_id', 'image_path')
            ->get();

        $result = [];

        foreach ($products->items() as $item) {
            $variants = $variantsAll->where('product_id', $item->id)->map(function ($v) use ($media) {
                $variantMedia = $media->where('product_variant_id', $v->id, false)->first();

                return [
                    'id' => $v->id,
                    'type' => $v->type,
                    'attribute_name' => $v->attribute_name,
                    'attribute_value' => $v->attribute_value,
                    'cover_image' => $variantMedia->image_path ?? null,
                    'weight' => $v->weight,
                    'dimensions' => [
                        'length' => $v->dimensions_length,
                        'width' => $v->dimensions_width,
                        'height' => $v->dimensions_height,
                    ],
                    'pricing' => [
                        'regular_price' => (int) $v->price,
                        'sale_price' => $v->sale_price ? (int) $v->sale_price : null,
                        'cost_price' => (int) $v->cost_price,
                        'formatted_price' => 'Rp' . number_format($v->sale_price ?? $v->price, 0, ',', '.'),
                    ],
                    'inventory' => [
                        'stock_quantity' => (int) $v->stock_quantity,
                        'in_stock' => (int) $v->stock_quantity > 0,
                    ],
                    'status' => $v->status,
                ];
            })->values();

            $productMedia = $media->where('product_id', $item->id)->first();

            $categories = DB::table('product_category_relationships')
                ->join('product_categories', 'product_category_relationships.product_category_id', '=', 'product_categories.id')
                ->where('product_category_relationships.product_id', $item->id)
                ->select('product_categories.id', 'product_categories.name as name')
                ->get();

            $tags = DB::table('product_tags')
                ->join('tags', 'product_tags.tag_id', '=', 'tags.id')
                ->where('product_tags.product_id', $item->id)
                ->select('tags.id', 'tags.name as name')
                ->get();

            $variantPrices = $variants->pluck('pricing.sale_price')->filter()->all();
            if (empty($variantPrices)) {
                $variantPrices = $variants->pluck('pricing.regular_price')->filter()->all();
            }

            $minPrice = !empty($variantPrices) ? min($variantPrices) : ($item->sale_price ?? $item->price);
            $maxPrice = !empty($variantPrices) ? max($variantPrices) : ($item->sale_price ?? $item->price);

            $result[] = [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'description' => [
                    'short' => $item->short_description,
                ],
                'sku' => $item->sku,
                'type' => $item->type,
                'status' => $item->status,
                'specifications' => [
                    'weight' => $item->weight,
                    'dimensions' => [
                        'length' => $item->dimensions_length,
                        'width' => $item->dimensions_width,
                        'height' => $item->dimensions_height,
                    ],
                ],
                'pricing' => [
                    'regular_price' => (int) $item->price,
                    'sale_price' => $item->sale_price ? (int) $item->sale_price : null,
                    'cost_price' => (int) $item->cost_price,
                    'min_variant_price' => (int) $minPrice,
                    'max_variant_price' => (int) $maxPrice,
                    'price_range' => $minPrice == $maxPrice
                        ? 'Rp' . number_format($minPrice, 0, ',', '.')
                        : 'Rp' . number_format($minPrice, 0, ',', '.') . ' - Rp' . number_format($maxPrice, 0, ',', '.'),
                ],
                'images' => [
                    'cover' => [
                        'url' => $item->cover_image,
                        'name' => $item->cover_image_name,
                        'alt' => $item->cover_image_alt,
                        'sort_order' => $item->cover_image_sort_order,
                    ],
                ],
                'brand' => $item->brand_id ? [
                    'id' => $item->brand_id,
                    'name' => $item->brand_name,
                ] : null,
                'categories' => $categories->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                    ];
                }),
                'tags' => $tags->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                    ];
                }),
                'variants' => $variants,
                'seo' => [
                    'meta_title' => $item->meta_title,
                    'meta_description' => $item->meta_description,
                    'meta_keywords' => $item->meta_keywords,
                    'robots' => $item->robots,
                    'canonical_url' => $item->canonical_url,
                ],
                'social' => [
                    'og_title' => $item->og_title,
                    'og_description' => $item->og_description,
                    'og_image' => $item->og_image,
                    'og_type' => $item->og_type,
                ],
                'structured_data' => [
                    'schema_markup' => $item->schema_markup,
                ],
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => [
                'store' => [
                    'id' => $store->id,
                    'name' => $store->name,
                    'slug' => $store->slug,
                    'contact' => [
                        'address' => $store->address,
                        'phone' => $store->phone,
                    ],
                ],
                'products' => $result,
            ],
            'meta' => [
                'pagination' => [
                    'total' => $products->total(),
                    'count' => count($result),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'total_pages' => $products->lastPage(),
                    'has_more_pages' => $products->hasMorePages(),
                    'links' => [
                        'first' => $products->url(1),
                        'last' => $products->url($products->lastPage()),
                        'prev' => $products->previousPageUrl(),
                        'next' => $products->nextPageUrl(),
                    ],
                ],
                'query' => [
                    'store_id' => (int) $storeId,
                    'filters' => $request->only(['search', 'category', 'brand', 'price_min', 'price_max']),
                ],
                'response_time' => microtime(true) - LARAVEL_START,
                'timestamp' => now()->toISOString(),
            ],
        ], 200);
    }

    public function show(Request $request, $storeId, $slug)
    {
        // ambil detail store
        $store = DB::table('stores')
            ->where('id', $storeId)
            ->select('id', 'name', 'slug', 'address', 'phone')
            ->first();

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found',
                'data' => null,
                'meta' => null
            ], 404);
        }

        // ambil detail product by slug
        $product = DB::table('products')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('product_stores', function ($join) use ($storeId) {
                $join->on('products.id', '=', 'product_stores.product_id')
                    ->where('product_stores.store_id', '=', $storeId);
            })
            ->where('products.slug', $slug)
            ->select([
                'products.*',
                'brands.id as brand_id',
                'brands.name as brand_nama',
            ])
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'data' => null,
                'meta' => null
            ], 404);
        }

        // ambil variants dari product
        $variants = DB::table('product_variants')
            ->leftJoin('variant_stores', function ($join) use ($storeId) {
                $join->on('product_variants.id', '=', 'variant_stores.variant_id')
                    ->where('variant_stores.store_id', '=', $storeId);
            })
            ->where('product_variants.product_id', $product->id)
            ->select([
                'product_variants.*',
            ])->get();

        $variantIds = $variants->pluck('id')->all();

        // ambil semua media untuk product dan variants
        $allMedia = DB::table('product_media')
            ->where(function($query) use ($product, $variantIds) {
                $query->where('product_id', $product->id)
                    ->whereNull('product_variant_id'); // Product images (no variant)
                
                if (!empty($variantIds)) {
                    $query->orWhere(function($q) use ($product, $variantIds) {
                        $q->where('product_id', $product->id)
                        ->whereIn('product_variant_id', $variantIds); // Variant images
                    });
                }
            })
            ->select('id', 'product_id', 'product_variant_id', 'image_path', 'sort_order', 'is_cover')
            ->orderBy('sort_order', 'asc')
            ->get();
        
        // format variants
        $formattedVariants = $variants->map(function ($v) use ($allMedia) {
            // Get images for this specific variant
            $variantMedia = $allMedia->where('product_variant_id', $v->id)->map(function ($m) {
                return [
                    'id' => $m->id,
                    'url' => $m->image_path,
                    'sort_order' => $m->sort_order,
                    'is_cover' => (bool) $m->is_cover,
                ];
            })->sortBy('sort_order')->values();

            return [
                'id' => $v->id,
                'type' => $v->type,
                'attribute_name' => $v->attribute_name,
                'attribute_value' => $v->attribute_value,
                'cover_image' => $v->cover_image, // Add cover_image field
                'weight' => $v->weight,
                'dimensions' => [
                    'length' => $v->dimensions_length,
                    'width' => $v->dimensions_width,
                    'height' => $v->dimensions_height,
                ],
                'pricing' => [
                    'regular_price' => (int) $v->price,
                    'sale_price' => $v->sale_price ? (int) $v->sale_price : null,
                    'cost_price' => (int) $v->cost_price,
                    'formatted_price' => 'Rp' . number_format($v->sale_price ?? $v->price, 0, ',', '.'),
                    'discount_percentage' => $v->sale_price && $v->price > $v->sale_price 
                        ? round((($v->price - $v->sale_price) / $v->price) * 100, 2)
                        : null,
                ],
                'inventory' => [
                    'stock_quantity' => (int) $v->stock_quantity,
                    'in_stock' => (int) $v->stock_quantity > 0,
                    'low_stock_threshold' => $v->low_stock_threshold ?? 10,
                    'is_low_stock' => (int) $v->stock_quantity <= ($v->low_stock_threshold ?? 10),
                ],
                'images' => $variantMedia,
                'status' => $v->status,
                'created_at' => $v->created_at,
                'updated_at' => $v->updated_at,
            ];
        })->values();

        // ambil semua gambar product (yang tidak ada variant_id)
        $productImages = $allMedia
            ->whereNull('product_variant_id')
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'url' => $m->image_path,
                    'sort_order' => $m->sort_order,
                    'is_cover' => (bool) $m->is_cover,
                ];
            })
            ->sortBy('sort_order')
            ->values();

        // Tentukan cover image
        $coverImage = $productImages->firstWhere('is_cover', true) ?? $productImages->first();

        // ambil kategori
        $categories = DB::table('product_category_relationships')
            ->join('product_categories', 'product_category_relationships.product_category_id', '=', 'product_categories.id')
            ->where('product_category_relationships.product_id', $product->id)
            ->select('product_categories.id', 'product_categories.name as nama', 'product_categories.slug')
            ->get()
            ->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->nama,
                    'slug' => $cat->slug,
                ];
            });

        // ambil tags
        $tags = DB::table('product_tags')
            ->join('tags', 'product_tags.tag_id', '=', 'tags.id')
            ->where('product_tags.product_id', $product->id)
            ->select('tags.id', 'tags.name as nama', 'tags.slug')
            ->get()
            ->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->nama,
                    'slug' => $tag->slug,
                ];
            });

        // hitung min/max price dari variants
        $variantPrices = $formattedVariants->pluck('pricing.sale_price')->filter()->all();
        if (empty($variantPrices)) {
            $variantPrices = $formattedVariants->pluck('pricing.regular_price')->filter()->all();
        }

        $minPrice = !empty($variantPrices) ? min($variantPrices) : ($product->sale_price ?? $product->price);
        $maxPrice = !empty($variantPrices) ? max($variantPrices) : ($product->sale_price ?? $product->price);

        // ambil produk terkait dari kategori yang sama
        $relatedProducts = DB::table('products')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('product_stores', function ($join) use ($storeId) {
                $join->on('products.id', '=', 'product_stores.product_id')
                    ->where('product_stores.store_id', '=', $storeId);
            })
            ->join('product_category_relationships', 'products.id', '=', 'product_category_relationships.product_id')
            ->whereIn('product_category_relationships.product_category_id', $categories->pluck('id'))
            ->where('products.id', '!=', $product->id)
            ->where('products.status', 'active')
            ->select([
                'products.id',
                'products.name',
                'products.slug',
                'products.cover_image',
                'products.price',
                'products.sale_price',
                'brands.name as brand_nama',
            ])
            ->distinct()
            ->limit(8)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'cover_image' => $item->cover_image,
                    'brand' => $item->brand_nama,
                    'pricing' => [
                        'regular_price' => (int) $item->price,
                        'sale_price' => $item->sale_price ? (int) $item->sale_price : null,
                        'formatted_price' => 'Rp' . number_format($item->sale_price ?? $item->price, 0, ',', '.'),
                    ],
                ];
            });

        $result = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => [
                'short' => $product->short_description,
                'full' => $product->description ?? null,
            ],
            'sku' => $product->sku,
            'type' => $product->type,
            'status' => $product->status,
            'specifications' => [
                'weight' => $product->weight,
                'dimensions' => [
                    'length' => $product->dimensions_length,
                    'width' => $product->dimensions_width,
                    'height' => $product->dimensions_height,
                ],
            ],
            'pricing' => [
                'regular_price' => (int) $product->price,
                'sale_price' => $product->sale_price ? (int) $product->sale_price : null,
                'cost_price' => (int) $product->cost_price,
                'min_variant_price' => (int) $minPrice,
                'max_variant_price' => (int) $maxPrice,
                'price_range' => $minPrice == $maxPrice
                    ? 'Rp' . number_format($minPrice, 0, ',', '.')
                    : 'Rp' . number_format($minPrice, 0, ',', '.') . ' - Rp' . number_format($maxPrice, 0, ',', '.'),
                'discount_percentage' => $product->sale_price && $product->price > $product->sale_price 
                    ? round((($product->price - $product->sale_price) / $product->price) * 100, 2)
                    : null,
            ],
            'images' => [
                'gallery' => $productImages,
                'cover' => $coverImage,
            ],
            'brand' => $product->brand_id ? [
                'id' => $product->brand_id,
                'name' => $product->brand_nama,
            ] : null,
            'categories' => $categories,
            'tags' => $tags,
            'variants' => $formattedVariants,
            'inventory' => [
                'total_stock' => $formattedVariants->sum('inventory.stock_quantity'),
                'in_stock' => $formattedVariants->where('inventory.in_stock', true)->isNotEmpty(),
                'variants_in_stock' => $formattedVariants->where('inventory.in_stock', true)->count(),
                'low_stock_variants' => $formattedVariants->where('inventory.is_low_stock', true)->count(),
            ],
            'seo' => [
                'meta_title' => $product->meta_title,
                'meta_description' => $product->meta_description,
                'meta_keywords' => $product->meta_keywords,
                'robots' => $product->robots,
                'canonical_url' => $product->canonical_url,
            ],
            'social' => [
                'og_title' => $product->og_title,
                'og_description' => $product->og_description,
                'og_image' => $product->og_image,
                'og_type' => $product->og_type,
            ],
            'structured_data' => [
                'schema_markup' => $product->schema_markup,
            ],
            'related_products' => $relatedProducts,
            'timestamps' => [
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data' => [
                'store' => [
                    'id' => $store->id,
                    'name' => $store->name,
                    'slug' => $store->slug,
                    'contact' => [
                        'address' => $store->address,
                        'phone' => $store->phone,
                    ],
                ],
                'product' => $result,
            ],
            'meta' => [
                'query' => [
                    'store_id' => (int) $storeId,
                    'product_slug' => $slug,
                ],
                'counts' => [
                    'variants' => $formattedVariants->count(),
                    'images' => $productImages->count(),
                    'categories' => $categories->count(),
                    'tags' => $tags->count(),
                    'related_products' => $relatedProducts->count(),
                ],
                'response_time' => microtime(true) - LARAVEL_START,
                'timestamp' => now()->toISOString(),
            ],
        ], 200);
    }

    /**
     * Search products by brand name and SKU
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'q' => 'nullable|string|max:255', // Add 'q' for general search
                'brand_name' => 'nullable|string|max:255',
                'sku' => 'nullable|string|max:100',
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1'
            ]);

            $searchTerm = $request->input('q'); // Get the 'q' parameter
            $brandName = $request->input('brand_name');
            $sku = $request->input('sku');
            $perPage = $request->input('per_page', 15);

            // Jika tidak ada parameter pencarian
            if (!$searchTerm && !$brandName && !$sku) { // Update condition to include 'q'
                return response()->json([
                    'success' => false,
                    'message' => 'At least one search parameter (q, brand_name or sku) is required',
                    'data' => [],
                    'meta' => null
                ], 400);
            }

            // Query utama untuk mencari products
            $query = DB::table('products')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
                ->select([
                    'products.id',
                    'products.name',
                    'products.slug',
                    'products.sku as product_sku',
                    'products.short_description',
                    'products.price',
                    'products.sale_price',
                    'products.cost_price',
                    'products.cover_image',
                    'products.status',
                    'products.weight',
                    'products.dimensions_length',
                    'products.dimensions_width',
                    'products.dimensions_height',
                    'products.created_at',
                    'products.updated_at',
                    'brands.id as brand_id',
                    'brands.name as brand_name',
                    'product_variants.sku as variant_sku',
                    'product_variants.id as variant_id',
                    'product_variants.attribute_name',
                    'product_variants.attribute_value',
                    'product_variants.price as variant_price',
                    'product_variants.sale_price as variant_sale_price',
                    'product_variants.stock_quantity'
                ]);

            // Filter berdasarkan search term (q)
            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('products.name', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('brands.name', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('products.sku', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('product_variants.sku', 'LIKE', '%' . $searchTerm . '%');
                });
            }

            // Filter berdasarkan brand name
            if ($brandName) {
                $query->where('brands.name', 'LIKE', '%' . $brandName . '%');
            }

            // Filter berdasarkan SKU (cari di product SKU dan variant SKU)
            if ($sku) {
                $query->where(function($q) use ($sku) {
                    $q->where('products.sku', 'LIKE', '%' . $sku . '%')
                      ->orWhere('product_variants.sku', 'LIKE', '%' . $sku . '%');
                });
            }

            // Hanya tampilkan produk yang aktif
            $query->where('products.status', '!=', 'archived')
                  ->orderBy('products.created_at', 'desc');

            // Execute query dengan pagination
            $results = $query->paginate($perPage);
            
            // Group results by product
            $productIds = collect($results->items())->pluck('id')->unique()->all();
            
            // Get product categories
            $categories = DB::table('product_category_relationships')
                ->join('product_categories', 'product_category_relationships.product_category_id', '=', 'product_categories.id')
                ->whereIn('product_category_relationships.product_id', $productIds)
                ->select(
                    'product_category_relationships.product_id',
                    'product_categories.id as category_id',
                    'product_categories.name as category_name'
                )
                ->get()
                ->groupBy('product_id');

            // Format hasil untuk response
            $formattedProducts = collect($results->items())
                ->groupBy('id')
                ->map(function($productGroup) use ($categories) {
                    $product = $productGroup->first();
                    
                    // Get variants for this product
                    $variants = $productGroup->filter(function($item) {
                        return $item->variant_id !== null;
                    })->map(function($variant) {
                        return [
                            'id' => $variant->variant_id,
                            'sku' => $variant->variant_sku,
                            'attribute_name' => $variant->attribute_name,
                            'attribute_value' => $variant->attribute_value,
                            'price' => (int) $variant->variant_price,
                            'sale_price' => $variant->variant_sale_price ? (int) $variant->variant_sale_price : null,
                            'stock_quantity' => (int) $variant->stock_quantity,
                            'formatted_price' => 'Rp' . number_format($variant->variant_sale_price ?? $variant->variant_price, 0, ',', '.')
                        ];
                    })->values();

                    // Get categories for this product
                    $productCategories = $categories->get($product->id, collect())->map(function($cat) {
                        return [
                            'id' => $cat->category_id,
                            'name' => $cat->category_name
                        ];
                    })->values();

                    // Calculate price range from variants
                    $variantPrices = $variants->pluck('sale_price')->filter()->all();
                    if (empty($variantPrices)) {
                        $variantPrices = $variants->pluck('price')->filter()->all();
                    }

                    $minPrice = !empty($variantPrices) ? min($variantPrices) : ($product->sale_price ?? $product->price);
                    $maxPrice = !empty($variantPrices) ? max($variantPrices) : ($product->sale_price ?? $product->price);

                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'sku' => $product->product_sku,
                        'description' => [
                            'short' => $product->short_description
                        ],
                        'status' => $product->status,
                        'pricing' => [
                            'regular_price' => (int) $product->price,
                            'sale_price' => $product->sale_price ? (int) $product->sale_price : null,
                            'cost_price' => (int) $product->cost_price,
                            'min_variant_price' => (int) $minPrice,
                            'max_variant_price' => (int) $maxPrice,
                            'price_range' => $minPrice == $maxPrice
                                ? 'Rp' . number_format($minPrice, 0, ',', '.')
                                : 'Rp' . number_format($minPrice, 0, ',', '.') . ' - Rp' . number_format($maxPrice, 0, ',', '.'),
                        ],
                        'specifications' => [
                            'weight' => $product->weight,
                            'dimensions' => [
                                'length' => $product->dimensions_length,
                                'width' => $product->dimensions_width,
                                'height' => $product->dimensions_height
                            ]
                        ],
                        'images' => [
                            'cover' => [
                                'url' => $product->cover_image
                            ]
                        ],
                        'brand' => $product->brand_id ? [
                            'id' => $product->brand_id,
                            'name' => $product->brand_name
                        ] : null,
                        'categories' => $productCategories,
                        'variants' => $variants,
                        'inventory' => [
                            'total_stock' => $variants->sum('stock_quantity'),
                            'variants_count' => $variants->count()
                        ],
                        'timestamps' => [
                            'created_at' => $product->created_at,
                            'updated_at' => $product->updated_at
                        ]
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Products search completed successfully',
                'data' => $formattedProducts,
                'meta' => [
                    'pagination' => [
                        'total' => $results->total(),
                        'count' => $formattedProducts->count(),
                        'per_page' => $results->perPage(),
                        'current_page' => $results->currentPage(),
                        'total_pages' => $results->lastPage(),
                        'has_more_pages' => $results->hasMorePages(),
                        'links' => [
                            'first' => $results->url(1),
                            'last' => $results->url($results->lastPage()),
                            'prev' => $results->previousPageUrl(),
                            'next' => $results->nextPageUrl()
                        ]
                    ],
                    'search_params' => [
                        'brand_name' => $brandName,
                        'sku' => $sku,
                        'per_page' => $perPage
                    ],
                    'response_time' => microtime(true) - LARAVEL_START,
                    'timestamp' => now()->toISOString()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during search',
                'error' => $e->getMessage(),
                'data' => [],
                'meta' => null
            ], 500);
        }
    }


    /**
     * Get stores where a product is available
     * 
     * @param Request $request
     * @param string $product_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductStores(Request $request, $product_id)
    {
        try {
            $request->validate([
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);

            $product = DB::table('products')
                ->where('id', $product_id)
                ->orWhere('slug', $product_id)
                ->select('id', 'name', 'slug', 'status')
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                    'data' => null,
                    'meta' => null
                ], 404);
            }

            $userLatitude = $request->input('latitude');
            $userLongitude = $request->input('longitude');

            $storesQuery = DB::table('stores')
                ->join('product_stores', 'stores.id', '=', 'product_stores.store_id')
                ->where('product_stores.product_id', $product->id)
                ->where('stores.status', 'active');

            if ($userLatitude && $userLongitude) {
                $storesQuery->selectRaw("
                    stores.id,
                    stores.id as uuid,
                    stores.name,
                    stores.address,
                    stores.phone,
                    stores.latitude,
                    stores.longitude,
                    stores.status,
                    stores.created_at,
                    stores.updated_at
                ", [$userLatitude, $userLongitude, $userLatitude])
                ->orderBy('distance_km', 'asc');
            } else {
                $storesQuery->select([
                    'stores.id',
                    'stores.id as uuid',
                    'stores.name',
                    'stores.address',
                    'stores.phone',
                    'stores.latitude',
                    'stores.longitude',
                    'stores.status',
                    'stores.created_at',
                    'stores.updated_at',
                ])->orderBy('stores.name', 'asc');
            }

            $stores = $storesQuery->get();

            // Format response
            $formattedStores = $stores->map(function ($store) {
                $data = [
                    'id' => $store->id,
                    'uuid' => $store->uuid,
                    'name' => $store->name,
                    'address' => $store->address,
                    'phone' => $store->phone,
                    'latitude' => $store->latitude ? (float) $store->latitude : null,
                    'longitude' => $store->longitude ? (float) $store->longitude : null,
                    'status' => $store->status,
                    'created_at' => $store->created_at,
                    'updated_at' => $store->updated_at,
                ];

                if (isset($store->distance_km)) {
                    $data['distance'] = [
                        'km' => round($store->distance_km, 2),
                        'formatted' => round($store->distance_km, 1) . ' km',
                    ];
                }

                return $data;
            });

            return response()->json([
                'success' => true,
                'message' => 'Store availability retrieved successfully',
                'data' => [
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'status' => $product->status,
                    ],
                    'stores' => $formattedStores->values(),
                    'availability_summary' => [
                        'total_stores' => $formattedStores->count(),
                        'has_location_filter' => !is_null($userLatitude) && !is_null($userLongitude),
                    ],
                ],
                'meta' => [
                    'query' => [
                        'product_id' => $product_id,
                        'user_location' => [
                            'latitude' => $userLatitude ? (float) $userLatitude : null,
                            'longitude' => $userLongitude ? (float) $userLongitude : null,
                        ],
                    ],
                    'counts' => [
                        'total_stores' => $formattedStores->count(),
                    ],
                    'response_time' => microtime(true) - LARAVEL_START,
                    'timestamp' => now()->toISOString(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving store availability',
                'error' => $e->getMessage(),
                'data' => null,
                'meta' => null
            ], 500);
        }
    }


}