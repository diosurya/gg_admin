<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $adminUser = Auth::user();

        $stats = [
            'users_count' => DB::table('users')->whereNull('deleted_at')->count(),
            'stores_count' => DB::table('stores')->whereNull('deleted_at')->count(),
            'products_count' => DB::table('products')->whereNull('deleted_at')->count(),
            'blogs_count' => DB::table('blogs')->whereNull('deleted_at')->count(),
            'brands_count' => DB::table('brands')->whereNull('deleted_at')->count(),
            'categories_count' => DB::table('product_categories')->whereNull('deleted_at')->count(),
        ];

        $recentProducts = DB::table('products')
            ->select('id', 'name', 'status', 'created_at')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentBlogs = DB::table('blogs')
            ->select('id', 'title', 'status', 'created_at')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();


        return view('admin.dashboard', compact('stats', 'recentProducts', 'recentBlogs'));
    }
}