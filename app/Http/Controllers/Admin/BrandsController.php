<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandsController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('brands')->whereNull('deleted_at');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $brands = $query->orderBy('created_at', 'desc')->paginate(10);
        $brands->appends($request->only('search'));

        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:brands,name',
            'slug'        => 'nullable|string|max:100|unique:brands,slug',
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|mimes:webp,png,jpg,jpeg,gif|max:2048',
            'banner'      => 'nullable|image|mimes:webp,png,jpg,jpeg,gif|max:4096',
            'website'     => 'nullable|string|max:255',
            'email'       => 'nullable|string|max:100',
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string',
            'country'     => 'nullable|string|max:50',
            'status'      => 'required|in:active,inactive',
            'sort_order'  => 'nullable|integer',
            'is_featured' => 'nullable|boolean',
        ]);

        $data = [
            'id'          => (string) Str::uuid(),
            'name'        => $request->name,
            'slug'        => Str::slug($request->slug ?? $request->name), // auto isi slug
            'description' => $request->description,
            'website'     => $request->website,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'address'     => $request->address,
            'country'     => $request->country,
            'status'      => $request->status,
            'sort_order'  => $request->sort_order ?? 0,
            'is_featured' => $request->is_featured ?? 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ];

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $fileName = time() . '_logo.' . $file->getClientOriginalExtension();
            $data['logo'] = $file->storeAs('brands/logo', $fileName, 'public');
        }

        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $fileName = time() . '_banner.' . $file->getClientOriginalExtension();
            $data['banner'] = $file->storeAs('brands/banner', $fileName, 'public');
        }

        DB::table('brands')->insert($data);

        return redirect()->route('admin.brands.index')->with('success', 'Brand created.');
    }

    public function edit($id)
    {
        $brand = DB::table('brands')->where('id', $id)->whereNull('deleted_at')->first();
        abort_if(!$brand, 404);

        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $brand = DB::table('brands')->where('id', $id)->whereNull('deleted_at')->first();
        abort_if(!$brand, 404);

        $request->validate([
            'name'        => 'required|string|max:100|unique:brands,name,' . $id . ',id',
            'slug'        => 'nullable|string|max:100|unique:brands,slug,' . $id . ',id',
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|mimes:webp,png,jpg,jpeg,gif|max:2048',
            'banner'      => 'nullable|image|mimes:webp,png,jpg,jpeg,gif|max:4096',
            'website'     => 'nullable|string|max:255',
            'email'       => 'nullable|string|max:100',
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string',
            'country'     => 'nullable|string|max:50',
            'status'      => 'required|in:active,inactive',
            'sort_order'  => 'nullable|integer',
            'is_featured' => 'nullable|boolean',
        ]);

        $data = [
            'name'        => $request->name,
            'slug'        => Str::slug($request->slug ?? $request->name),
            'description' => $request->description,
            'website'     => $request->website,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'address'     => $request->address,
            'country'     => $request->country,
            'status'      => $request->status,
            'sort_order'  => $request->sort_order ?? 0,
            'is_featured' => $request->is_featured ?? 0,
            'updated_at'  => now(),
        ];

        // Update logo
        if ($request->hasFile('logo')) {
            if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                Storage::disk('public')->delete($brand->logo);
            }
            $file = $request->file('logo');
            $fileName = time() . '_logo.' . $file->getClientOriginalExtension();
            $data['logo'] = $file->storeAs('brands/logo', $fileName, 'public');
        }

        // Update banner
        if ($request->hasFile('banner')) {
            if ($brand->banner && Storage::disk('public')->exists($brand->banner)) {
                Storage::disk('public')->delete($brand->banner);
            }
            $file = $request->file('banner');
            $fileName = time() . '_banner.' . $file->getClientOriginalExtension();
            $data['banner'] = $file->storeAs('brands/banner', $fileName, 'public');
        }

        DB::table('brands')->where('id', $id)->update($data);

        return redirect()->route('admin.brands.index')->with('success', 'Brand updated.');
    }

    public function show($id)
    {
        $brand = DB::table('brands')->where('id', $id)->whereNull('deleted_at')->first();
        abort_if(!$brand, 404);

        return view('admin.brands.show', compact('brand'));
    }

    public function destroy($id)
    {
        $brand = DB::table('brands')->where('id', $id)->first();
        abort_if(!$brand, 404);

        if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
            Storage::disk('public')->delete($brand->logo);
        }
        if ($brand->banner && Storage::disk('public')->exists($brand->banner)) {
            Storage::disk('public')->delete($brand->banner);
        }

        DB::table('brands')->where('id', $id)->update([
            'deleted_at' => now()
        ]);

        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted.');
    }
}
