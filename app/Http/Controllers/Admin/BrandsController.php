<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BrandsController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('brands')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name');

        // Search
        if ($request->has('search') && $request->search) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('slug', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by featured
        if ($request->has('is_featured') && $request->is_featured !== '') {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        $brands = $query->paginate(20);

        return view('admin.brands.index', compact('brands'));
    }

    public function show($id)
    {
        $brand = DB::table('brands')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$brand) {
            return redirect()->route('admin.brands.index')
                ->with('error', 'Brand not found!');
        }

        // Get brand statistics
        $stats = [
            'products_count' => DB::table('products')
                ->where('brand_id', $id)
                ->whereNull('deleted_at')
                ->count(),
            'active_products' => DB::table('products')
                ->where('brand_id', $id)
                ->where('status', 'published')
                ->whereNull('deleted_at')
                ->count(),
            'total_sales' => DB::table('products')
                ->where('brand_id', $id)
                ->whereNull('deleted_at')
                ->sum('sales_count'),
            'total_views' => DB::table('products')
                ->where('brand_id', $id)
                ->whereNull('deleted_at')
                ->sum('views_count')
        ];

        // Get recent products
        $recentProducts = DB::table('products')
            ->select('id', 'name', 'slug', 'status', 'created_at')
            ->where('brand_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.brands.show', compact('brand', 'stats', 'recentProducts'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:brands,slug',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
            'is_featured' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        $data = [
            'id' => Str::uuid(),
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'website' => $request->website,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'country' => $request->country,
            'status' => $request->status,
            'sort_order' => $request->sort_order ?: 0,
            'is_featured' => $request->boolean('is_featured'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Handle file uploads
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('brands/banners', 'public');
        }

        DB::table('brands')->insert($data);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand created successfully!');
    }

    public function edit($id)
    {
        $brand = DB::table('brands')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$brand) {
            return redirect()->route('admin.brands.index')
                ->with('error', 'Brand not found!');
        }

        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $brand = DB::table('brands')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$brand) {
            return redirect()->route('admin.brands.index')
                ->with('error', 'Brand not found!');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:brands,slug,' . $id . ',id',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
            'is_featured' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        $data = [
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'website' => $request->website,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'country' => $request->country,
            'status' => $request->status,
            'sort_order' => $request->sort_order ?: 0,
            'is_featured' => $request->boolean('is_featured'),
            'updated_at' => now(),
        ];

        // Handle file uploads with deletion of old files
        if ($request->hasFile('logo')) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $data['logo'] = $request->file('logo')->store('brands/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            if ($brand->banner) {
                Storage::disk('public')->delete($brand->banner);
            }
            $data['banner'] = $request->file('banner')->store('brands/banners', 'public');
        }

        DB::table('brands')
            ->where('id', $id)
            ->update($data);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand updated successfully!');
    }

    public function destroy($id)
    {
        $brand = DB::table('brands')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$brand) {
            return response()->json(['error' => 'Brand not found!'], 404);
        }

        // Check if brand has products
        $hasProducts = DB::table('products')
            ->where('brand_id', $id)
            ->whereNull('deleted_at')
            ->exists();

        if ($hasProducts) {
            return response()->json(['error' => 'Cannot delete brand with products!'], 400);
        }

        // Soft delete
        DB::table('brands')
            ->where('id', $id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now()
            ]);

        return response()->json(['success' => 'Brand deleted successfully!']);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'brands' => 'required|array',
            'brands.*.id' => 'required|uuid|exists:brands,id',
            'brands.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            foreach ($request->brands as $brandData) {
                DB::table('brands')
                    ->where('id', $brandData['id'])
                    ->update([
                        'sort_order' => $brandData['sort_order'],
                        'updated_at' => now()
                    ]);
            }

            DB::commit();
            return response()->json(['success' => 'Brands reordered successfully!']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to reorder brands: ' . $e->getMessage()], 500);
        }
    }

    public function toggleFeatured($id)
    {
        $brand = DB::table('brands')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$brand) {
            return response()->json(['error' => 'Brand not found!'], 404);
        }

        $newFeaturedStatus = !$brand->is_featured;

        DB::table('brands')
            ->where('id', $id)
            ->update([
                'is_featured' => $newFeaturedStatus,
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => 'Brand featured status updated successfully!',
            'is_featured' => $newFeaturedStatus
        ]);
    }

    public function getBrandsList()
    {
        $brands = DB::table('brands')
            ->select('id', 'name', 'slug', 'logo')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return response()->json($brands);
    }
}