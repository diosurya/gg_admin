<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class StoresController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('stores')
            ->select('stores.*', 'users.first_name', 'users.last_name', 'users.email as owner_email')
            ->leftJoin('users', 'stores.owner_id', '=', 'users.id')
            ->whereNull('stores.deleted_at')
            ->orderBy('stores.created_at', 'desc');

        // Search
        if ($request->has('search') && $request->search) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('stores.name', 'like', $search)
                  ->orWhere('stores.slug', 'like', $search)
                  ->orWhere('stores.domain', 'like', $search);
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('stores.status', $request->status);
        }

        // Filter by country
        if ($request->has('country') && $request->country) {
            $query->where('stores.country', $request->country);
        }

        $stores = $query->paginate(20);

        return view('admin.stores.index', compact('stores'));
    }

    public function show($id)
    {
        $store = DB::table('stores')
            ->select('stores.*', 'users.first_name', 'users.last_name', 'users.email as owner_email')
            ->leftJoin('users', 'stores.owner_id', '=', 'users.id')
            ->where('stores.id', $id)
            ->whereNull('stores.deleted_at')
            ->first();

        if (!$store) {
            return redirect()->route('admin.stores.index')
                ->with('error', 'Store not found!');
        }

        // Get store statistics
        $stats = [
            'products_count' => DB::table('product_stores')
                ->where('store_id', $id)
                ->where('is_active', true)
                ->count(),
            'blogs_count' => DB::table('blogs')
                ->where('store_id', $id)
                ->whereNull('deleted_at')
                ->count(),
            'total_variants' => DB::table('variant_stores')
                ->where('store_id', $id)
                ->where('is_active', true)
                ->count(),
            'total_stock' => DB::table('variant_stores')
                ->where('store_id', $id)
                ->where('is_active', true)
                ->sum('stock_quantity')
        ];

        return view('admin.stores.show', compact('store', 'stats'));
    }

    public function create()
    {
        // Get available owners (managers and admins)
        $owners = DB::table('users')
            ->whereIn('role', ['admin', 'manager'])
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->get();

        return view('admin.stores.create', compact('owners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:stores,slug',
            'domain' => 'nullable|string|max:100|unique:stores,domain',
            'description' => 'nullable|string',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'timezone' => 'required|string|max:50',
            'currency' => 'required|string|size:3',
            'language' => 'required|string|size:2',
            'tax_rate' => 'nullable|numeric|between:0,100',
            'shipping_fee' => 'nullable|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,maintenance',
            'owner_id' => 'nullable|uuid|exists:users,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'favicon' => 'nullable|image|mimes:png,ico,x-icon|max:512',
        ]);

        $data = [
            'id' => Str::uuid(),
            'name' => $request->name,
            'slug' => $request->slug,
            'domain' => $request->domain,
            'description' => $request->description,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'timezone' => $request->timezone,
            'currency' => strtoupper($request->currency),
            'language' => strtolower($request->language),
            'tax_rate' => $request->tax_rate,
            'shipping_fee' => $request->shipping_fee,
            'free_shipping_threshold' => $request->free_shipping_threshold,
            'status' => $request->status,
            'owner_id' => $request->owner_id,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Handle file uploads
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('stores/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('stores/banners', 'public');
        }

        if ($request->hasFile('favicon')) {
            $data['favicon'] = $request->file('favicon')->store('stores/favicons', 'public');
        }

        DB::table('stores')->insert($data);

        return redirect()->route('admin.stores.index')
            ->with('success', 'Store created successfully!');
    }

    public function edit($id)
    {
        $store = DB::table('stores')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$store) {
            return redirect()->route('admin.stores.index')
                ->with('error', 'Store not found!');
        }

        // Get available owners (managers and admins)
        $owners = DB::table('users')
            ->whereIn('role', ['admin', 'manager'])
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->get();

        return view('admin.stores.edit', compact('store', 'owners'));
    }

    public function update(Request $request, $id)
    {
        $store = DB::table('stores')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$store) {
            return redirect()->route('admin.stores.index')
                ->with('error', 'Store not found!');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:stores,slug,' . $id . ',id',
            'domain' => 'nullable|string|max:100|unique:stores,domain,' . $id . ',id',
            'description' => 'nullable|string',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'timezone' => 'required|string|max:50',
            'currency' => 'required|string|size:3',
            'language' => 'required|string|size:2',
            'tax_rate' => 'nullable|numeric|between:0,100',
            'shipping_fee' => 'nullable|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,maintenance',
            'owner_id' => 'nullable|uuid|exists:users,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'favicon' => 'nullable|image|mimes:png,ico,x-icon|max:512',
        ]);

        $data = [
            'name' => $request->name,
            'slug' => $request->slug,
            'domain' => $request->domain,
            'description' => $request->description,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'timezone' => $request->timezone,
            'currency' => strtoupper($request->currency),
            'language' => strtolower($request->language),
            'tax_rate' => $request->tax_rate,
            'shipping_fee' => $request->shipping_fee,
            'free_shipping_threshold' => $request->free_shipping_threshold,
            'status' => $request->status,
            'owner_id' => $request->owner_id,
            'updated_at' => now(),
        ];

        // Handle file uploads with deletion of old files
        if ($request->hasFile('logo')) {
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }
            $data['logo'] = $request->file('logo')->store('stores/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            if ($store->banner) {
                Storage::disk('public')->delete($store->banner);
            }
            $data['banner'] = $request->file('banner')->store('stores/banners', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($store->favicon) {
                Storage::disk('public')->delete($store->favicon);
            }
            $data['favicon'] = $request->file('favicon')->store('stores/favicons', 'public');
        }

        DB::table('stores')
            ->where('id', $id)
            ->update($data);

        return redirect()->route('admin.stores.index')
            ->with('success', 'Store updated successfully!');
    }

    public function destroy($id)
    {
        $store = DB::table('stores')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$store) {
            return redirect()->route('admin.stores.index')
                ->with('error', 'Store not found!');
        }

        // Soft delete
        DB::table('stores')
            ->where('id', $id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now()
            ]);

        return redirect()->route('admin.stores.index')
            ->with('success', 'Store deleted successfully!');
    }

    public function getStoresByOwner($ownerId)
    {
        $stores = DB::table('stores')
            ->select('id', 'name')
            ->where('owner_id', $ownerId)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->get();

        return response()->json($stores);
    }
}