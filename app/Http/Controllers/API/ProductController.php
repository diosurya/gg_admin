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
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('product_stores', function ($join) use ($storeId) {
                $join->on('products.id', '=', 'product_stores.product_id')
                    ->where('product_stores.store_id', '=', $storeId);
            })
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
        // dd($variantIds);

        // ambil semua media untuk product dan variants
        $media = DB::table('product_media')
            ->where('product_id', $product->id)
            ->WhereIn('product_variant_id', $variantIds)
            ->select('id', 'product_id', 'product_variant_id', 'image_path', 'sort_order')
            ->orderBy('sort_order', 'asc')
            ->get();
        
        // dd($media);
        // format variants
        $formattedVariants = $variants->map(function ($v) use ($media) {
            $variantMedia = $media->where('product_variant_id', $v->id)->map(function ($m) {
                return [
                    'id' => $m->id,
                    'url' => $m->image_path,
                    'sort_order' => $m->sort_order,
                ];
            })->values();

            return [
                'id' => $v->id,
                'type' => $v->type,
                'attribute_name' => $v->attribute_name,
                'attribute_value' => $v->attribute_value,
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

        // ambil semua gambar product
        $productImages = $media->where('product_id', $product->id)->map(function ($m) {
            return [
                'id' => $m->id,
                'url' => $m->image_path,
                'sort_order' => $m->sort_order,
            ];
        })->sortBy('sort_order')->values();

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
                'cover' => $productImages->first() ?? $productImages->first(),
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
}