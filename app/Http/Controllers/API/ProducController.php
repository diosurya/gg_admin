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
    public function index() {
        $query = DB::table('products')
            ->select([
                'products.*',
            ])
            ->leftJoin('product_category_relationships', 'products.id', '=', 'product_category_relationships.product_id')
            ->leftJoin('product_categories', 'product_category_relationships.product_category_id', '=', 'product_categories.id')
            ->leftJoin('product_categories', 'product_category_relationships.product_category_id', '=', 'product_categories.id')
            ->get();
    }
}