<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReportsController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        // Get summary statistics
        $stats = [
            'total_products' => DB::table('products')->whereNull('deleted_at')->count(),
            'published_products' => DB::table('products')->where('status', 'published')->whereNull('deleted_at')->count(),
            'total_users' => DB::table('users')->whereNull('deleted_at')->count(),
            'active_users' => DB::table('users')->where('status', 'active')->whereNull('deleted_at')->count(),
            'total_stores' => DB::table('stores')->whereNull('deleted_at')->count(),
            'active_stores' => DB::table('stores')->where('status', 'active')->whereNull('deleted_at')->count(),
            'total_blogs' => DB::table('blogs')->whereNull('deleted_at')->count(),
            'published_blogs' => DB::table('blogs')->where('status', 'published')->whereNull('deleted_at')->count(),
        ];

        // Get recent activity
        $recentProducts = DB::table('products')
            ->select('name', 'status', 'created_at')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentBlogs = DB::table('blogs')
            ->select('title', 'status', 'created_at')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get monthly data for charts
        $monthlyProducts = DB::table('products')
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count'))
            ->whereNull('deleted_at')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy('month')
            ->get();

        return view('admin.reports.index', compact('stats', 'recentProducts', 'recentBlogs', 'monthlyProducts'));
    }

    /**
     * Products report
     */
    public function products(Request $request)
    {
        $query = DB::table('products as p')
            ->leftJoin('brands as b', 'p.brand_id', '=', 'b.id')
            ->leftJoin('users as u', 'p.created_by', '=', 'u.id')
            ->select(
                'p.id',
                'p.name',
                'p.sku',
                'p.type',
                'p.status',
                'p.featured',
                'p.views_count',
                'p.sales_count',
                'p.created_at',
                'b.name as brand_name',
                'u.first_name',
                'u.last_name'
            )
            ->whereNull('p.deleted_at');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('p.status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('p.type', $request->type);
        }

        if ($request->filled('brand_id')) {
            $query->where('p.brand_id', $request->brand_id);
        }

        if ($request->filled('featured')) {
            $query->where('p.featured', $request->featured);
        }

        if ($request->filled('date_from')) {
            $query->where('p.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('p.created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $products = $query->orderBy('p.created_at', 'desc')->paginate(50);

        // Get filter options
        $brands = DB::table('brands')->where('status', 'active')->get(['id', 'name']);
        $productTypes = ['simple', 'variable', 'grouped', 'external', 'digital'];
        $productStatuses = ['draft', 'published', 'archived', 'out_of_stock'];

        return view('admin.reports.products', compact('products', 'brands', 'productTypes', 'productStatuses'));
    }

    /**
     * Sales report
     */
    public function sales(Request $request)
    {
        // Note: This assumes you have orders/sales tables
        // Since they're not in the provided schema, I'll create a mock structure
        $salesData = [
            'total_sales' => 0,
            'total_orders' => 0,
            'average_order_value' => 0,
            'top_products' => [],
            'monthly_sales' => []
        ];

        // Mock data for demonstration
        $topProducts = DB::table('products')
            ->select('name', 'sales_count')
            ->whereNull('deleted_at')
            ->orderBy('sales_count', 'desc')
            ->limit(10)
            ->get();

        $salesData['top_products'] = $topProducts;

        return view('admin.reports.sales', compact('salesData'));
    }

    /**
     * Inventory report
     */
    public function inventory(Request $request)
    {
        $query = DB::table('variant_stores as vs')
            ->join('product_variants as pv', 'vs.variant_id', '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->join('stores as s', 'vs.store_id', '=', 's.id')
            ->select(
                'p.name as product_name',
                'pv.sku',
                's.name as store_name',
                'vs.stock_quantity',
                'vs.min_stock_level',
                'vs.max_stock_level',
                'vs.stock_status',
                'vs.price',
                'vs.cost_price'
            )
            ->whereNull('pv.deleted_at')
            ->where('vs.is_active', true);

        // Apply filters
        if ($request->filled('store_id')) {
            $query->where('vs.store_id', $request->store_id);
        }

        if ($request->filled('stock_status')) {
            $query->where('vs.stock_status', $request->stock_status);
        }

        if ($request->filled('low_stock')) {
            $query->whereRaw('vs.stock_quantity <= vs.min_stock_level');
        }

        $inventory = $query->orderBy('p.name')->paginate(50);

        // Get filter options
        $stores = DB::table('stores')->where('status', 'active')->get(['id', 'name']);
        $stockStatuses = ['in_stock', 'out_of_stock', 'on_backorder'];

        // Get summary statistics
        $stats = [
            'total_variants' => DB::table('variant_stores')->where('is_active', true)->count(),
            'out_of_stock' => DB::table('variant_stores')->where('stock_status', 'out_of_stock')->count(),
            'low_stock' => DB::table('variant_stores')->whereRaw('stock_quantity <= min_stock_level')->count(),
            'total_value' => DB::table('variant_stores')->sum(DB::raw('stock_quantity * cost_price'))
        ];

        return view('admin.reports.inventory', compact('inventory', 'stores', 'stockStatuses', 'stats'));
    }

    /**
     * Users report
     */
    public function users(Request $request)
    {
        $query = DB::table('users')
            ->select(
                'id',
                'username',
                'email',
                'first_name',
                'last_name',
                'role',
                'status',
                'last_login_at',
                'created_at'
            )
            ->whereNull('deleted_at');

        // Apply filters
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get summary statistics
        $stats = [
            'total_users' => DB::table('users')->whereNull('deleted_at')->count(),
            'active_users' => DB::table('users')->where('status', 'active')->whereNull('deleted_at')->count(),
            'admins' => DB::table('users')->where('role', 'admin')->whereNull('deleted_at')->count(),
            'customers' => DB::table('users')->where('role', 'customer')->whereNull('deleted_at')->count(),
            'new_this_month' => DB::table('users')->where('created_at', '>=', now()->startOfMonth())->whereNull('deleted_at')->count()
        ];

        $roles = ['admin', 'manager', 'customer', 'author'];
        $statuses = ['active', 'inactive', 'suspended'];

        return view('admin.reports.users', compact('users', 'stats', 'roles', 'statuses'));
    }

    /**
     * Export products to CSV
     */
    public function exportProducts(Request $request)
    {
        $query = DB::table('products as p')
            ->leftJoin('brands as b', 'p.brand_id', '=', 'b.id')
            ->select(
                'p.name',
                'p.sku',
                'p.type',
                'p.status',
                'p.featured',
                'p.views_count',
                'p.sales_count',
                'p.created_at',
                'b.name as brand_name'
            )
            ->whereNull('p.deleted_at');

        // Apply same filters as products report
        if ($request->filled('status')) {
            $query->where('p.status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('p.type', $request->type);
        }

        if ($request->filled('brand_id')) {
            $query->where('p.brand_id', $request->brand_id);
        }

        $products = $query->orderBy('p.created_at', 'desc')->get();

        $filename = 'products_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, ['Name', 'SKU', 'Type', 'Status', 'Featured', 'Views', 'Sales', 'Brand', 'Created At']);
            
            // Add data rows
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->name,
                    $product->sku,
                    $product->type,
                    $product->status,
                    $product->featured ? 'Yes' : 'No',
                    $product->views_count,
                    $product->sales_count,
                    $product->brand_name,
                    $product->created_at
                ]);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export inventory to CSV
     */
    public function exportInventory(Request $request)
    {
        $query = DB::table('variant_stores as vs')
            ->join('product_variants as pv', 'vs.variant_id', '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->join('stores as s', 'vs.store_id', '=', 's.id')
            ->select(
                'p.name as product_name',
                'pv.sku',
                's.name as store_name',
                'vs.stock_quantity',
                'vs.min_stock_level',
                'vs.stock_status',
                'vs.price',
                'vs.cost_price'
            )
            ->whereNull('pv.deleted_at')
            ->where('vs.is_active', true);

        // Apply filters
        if ($request->filled('store_id')) {
            $query->where('vs.store_id', $request->store_id);
        }

        if ($request->filled('stock_status')) {
            $query->where('vs.stock_status', $request->stock_status);
        }

        $inventory = $query->orderBy('p.name')->get();

        $filename = 'inventory_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($inventory) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, ['Product', 'SKU', 'Store', 'Stock Qty', 'Min Level', 'Status', 'Price', 'Cost Price']);
            
            // Add data rows
            foreach ($inventory as $item) {
                fputcsv($file, [
                    $item->product_name,
                    $item->sku,
                    $item->store_name,
                    $item->stock_quantity,
                    $item->min_stock_level,
                    $item->stock_status,
                    $item->price,
                    $item->cost_price
                ]);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}