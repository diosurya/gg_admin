<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductCategoriesController extends Controller
{
    public function index(Request $request)
    {
        // Get categories in tree structure
        $categories = $this->buildCategoryTree();

        return view('admin.product-categories.index', compact('categories'));
    }

    private function buildCategoryTree($parentId = null)
    {
        $query = DB::table('product_categories')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }

        $categories = $query->get();

        foreach ($categories as $category) {
            $category->children = $this->buildCategoryTree($category->id);
        }

        return $categories;
    }

    public function show($id)
    {
        $category = DB::table('product_categories')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$category) {
            return redirect()->route('admin.product-categories.index')
                ->with('error', 'Category not found!');
        }

        // Get parent category
        $parent = null;
        if ($category->parent_id) {
            $parent = DB::table('product_categories')
                ->where('id', $category->parent_id)
                ->first();
        }

        // Get children categories
        $children = DB::table('product_categories')
            ->where('parent_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->get();

        // Get category media
        $media = DB::table('product_category_media')
            ->where('category_id', $id)
            ->orderBy('sort_order')
            ->get();

        // Get category SEO data
        $seoData = DB::table('product_category_seo')
            ->where('category_id', $id)
            ->get()
            ->keyBy('store_id');

        // Get products count
        $productsCount = DB::table('product_category_relationships')
            ->where('category_id', $id)
            ->count();

        return view('admin.product-categories.show', compact('category', 'parent', 'children', 'media', 'seoData', 'productsCount'));
    }

    public function create(Request $request)
    {
        // Get parent categories for dropdown
        $parentCategories = $this->getFlatCategoriesForDropdown();

        $parentId = $request->get('parent_id');
        $parent = null;
        
        if ($parentId) {
            $parent = DB::table('product_categories')
                ->where('id', $parentId)
                ->whereNull('deleted_at')
                ->first();
        }

        return view('admin.product-categories.create', compact('parentCategories', 'parent', 'parentId'));
    }

    private function getFlatCategoriesForDropdown($excludeId = null)
    {
        $categories = DB::table('product_categories')
            ->whereNull('deleted_at')
            ->orderBy('path')
            ->orderBy('sort_order')
            ->get();

        $options = [];
        foreach ($categories as $category) {
            if ($excludeId && $category->id === $excludeId) continue;
            
            $prefix = str_repeat('— ', $category->level);
            $options[] = (object) [
                'id' => $category->id,
                'name' => $prefix . $category->name,
                'level' => $category->level
            ];
        }

        return $options;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:product_categories,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|uuid|exists:product_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'color' => 'nullable|string|size:7|regex:/^#[a-fA-F0-9]{6}$/',
            'icon' => 'nullable|string|max:100',
            'is_featured' => 'boolean',
            'show_in_menu' => 'boolean',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'seo' => 'nullable|array',
            'seo.*.meta_title' => 'nullable|string|max:70',
            'seo.*.meta_description' => 'nullable|string|max:160',
            'seo.*.meta_keywords' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        
        try {
            // Calculate level and path
            $level = 0;
            $path = '';
            
            if ($request->parent_id) {
                $parent = DB::table('product_categories')
                    ->where('id', $request->parent_id)
                    ->first();
                
                if ($parent) {
                    $level = $parent->level + 1;
                    $path = $parent->path ? $parent->path . '/' . $request->parent_id : $request->parent_id;
                }
            }

            $categoryId = Str::uuid();

            $data = [
                'id' => $categoryId,
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'sort_order' => $request->sort_order ?: 0,
                'level' => $level,
                'path' => $path,
                'color' => $request->color,
                'icon' => $request->icon,
                'is_featured' => $request->boolean('is_featured'),
                'show_in_menu' => $request->boolean('show_in_menu', true),
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Handle file uploads
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('categories/images', 'public');
            }

            if ($request->hasFile('banner')) {
                $data['banner'] = $request->file('banner')->store('categories/banners', 'public');
            }

            DB::table('product_categories')->insert($data);

            // Handle SEO data
            if ($request->has('seo') && is_array($request->seo)) {
                foreach ($request->seo as $storeId => $seoData) {
                    if (empty(array_filter($seoData))) continue;

                    DB::table('product_category_seo')->insert([
                        'id' => Str::uuid(),
                        'category_id' => $categoryId,
                        'store_id' => $storeId === 'global' ? null : $storeId,
                        'meta_title' => $seoData['meta_title'] ?? null,
                        'meta_description' => $seoData['meta_description'] ?? null,
                        'meta_keywords' => $seoData['meta_keywords'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.product-categories.index')
                ->with('success', 'Category created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Failed to create category: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $category = DB::table('product_categories')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$category) {
            return redirect()->route('admin.product-categories.index')
                ->with('error', 'Category not found!');
        }

        // Get parent categories (exclude self and children)
        $parentCategories = $this->getFlatCategoriesForDropdown($id);

        // Get SEO data
        $seoData = DB::table('product_category_seo')
            ->where('category_id', $id)
            ->get()
            ->keyBy('store_id');

        // Get available stores for SEO
        $stores = DB::table('stores')
            ->select('id', 'name')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->get();

        return view('admin.product-categories.edit', compact('category', 'parentCategories', 'seoData', 'stores'));
    }

    public function update(Request $request, $id)
    {
        $category = DB::table('product_categories')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$category) {
            return redirect()->route('admin.product-categories.index')
                ->with('error', 'Category not found!');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:product_categories,slug,' . $id . ',id',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|uuid|exists:product_categories,id|not_in:' . $id,
            'sort_order' => 'nullable|integer|min:0',
            'color' => 'nullable|string|size:7|regex:/^#[a-fA-F0-9]{6}$/',
            'icon' => 'nullable|string|max:100',
            'is_featured' => 'boolean',
            'show_in_menu' => 'boolean',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'seo' => 'nullable|array',
            'seo.*.meta_title' => 'nullable|string|max:70',
            'seo.*.meta_description' => 'nullable|string|max:160',
            'seo.*.meta_keywords' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        
        try {
            // Calculate level and path
            $level = 0;
            $path = '';
            
            if ($request->parent_id) {
                $parent = DB::table('product_categories')
                    ->where('id', $request->parent_id)
                    ->first();
                
                if ($parent) {
                    $level = $parent->level + 1;
                    $path = $parent->path ? $parent->path . '/' . $request->parent_id : $request->parent_id;
                }
            }

            $data = [
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'sort_order' => $request->sort_order ?: 0,
                'level' => $level,
                'path' => $path,
                'color' => $request->color,
                'icon' => $request->icon,
                'is_featured' => $request->boolean('is_featured'),
                'show_in_menu' => $request->boolean('show_in_menu', true),
                'status' => $request->status,
                'updated_at' => now(),
            ];

            // Handle file uploads
            if ($request->hasFile('image')) {
                if ($category->image) {
                    Storage::disk('public')->delete($category->image);
                }
                $data['image'] = $request->file('image')->store('categories/images', 'public');
            }

            if ($request->hasFile('banner')) {
                if ($category->banner) {
                    Storage::disk('public')->delete($category->banner);
                }
                $data['banner'] = $request->file('banner')->store('categories/banners', 'public');
            }

            DB::table('product_categories')
                ->where('id', $id)
                ->update($data);

            // Update SEO data
            if ($request->has('seo') && is_array($request->seo)) {
                // Delete existing SEO data
                DB::table('product_category_seo')
                    ->where('category_id', $id)
                    ->delete();

                // Insert new SEO data
                foreach ($request->seo as $storeId => $seoData) {
                    if (empty(array_filter($seoData))) continue;

                    DB::table('product_category_seo')->insert([
                        'id' => Str::uuid(),
                        'category_id' => $id,
                        'store_id' => $storeId === 'global' ? null : $storeId,
                        'meta_title' => $seoData['meta_title'] ?? null,
                        'meta_description' => $seoData['meta_description'] ?? null,
                        'meta_keywords' => $seoData['meta_keywords'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Update path for all children if parent changed
            if ($request->parent_id !== $category->parent_id) {
                $this->updateChildrenPaths($id);
            }

            DB::commit();

            return redirect()->route('admin.product-categories.index')
                ->with('success', 'Category updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    private function updateChildrenPaths($categoryId)
    {
        $category = DB::table('product_categories')->where('id', $categoryId)->first();
        if (!$category) return;

        $children = DB::table('product_categories')
            ->where('parent_id', $categoryId)
            ->get();

        foreach ($children as $child) {
            $newPath = $category->path ? $category->path . '/' . $categoryId : $categoryId;
            $newLevel = $category->level + 1;

            DB::table('product_categories')
                ->where('id', $child->id)
                ->update([
                    'level' => $newLevel,
                    'path' => $newPath,
                    'updated_at' => now()
                ]);

            // Recursively update grandchildren
            $this->updateChildrenPaths($child->id);
        }
    }

    public function destroy($id)
    {
        $category = DB::table('product_categories')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$category) {
            return response()->json(['error' => 'Category not found!'], 404);
        }

        // Check if category has children
        $hasChildren = DB::table('product_categories')
            ->where('parent_id', $id)
            ->whereNull('deleted_at')
            ->exists();

        if ($hasChildren) {
            return response()->json(['error' => 'Cannot delete category with children!'], 400);
        }

        // Check if category has products
        $hasProducts = DB::table('product_category_relationships')
            ->where('category_id', $id)
            ->exists();

        if ($hasProducts) {
            return response()->json(['error' => 'Cannot delete category with products!'], 400);
        }

        // Soft delete
        DB::table('product_categories')
            ->where('id', $id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now()
            ]);

        return response()->json(['success' => 'Category deleted successfully!']);
    }

    public function getCategoryTree()
    {
        $categories = $this->buildCategoryTreeForAPI();
        return response()->json($categories);
    }

    private function buildCategoryTreeForAPI($parentId = null)
    {
        $query = DB::table('product_categories')
            ->select('id', 'name', 'slug', 'parent_id', 'level', 'sort_order', 'status', 'is_featured', 'products_count')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }

        $categories = $query->get();

        foreach ($categories as $category) {
            $category->children = $this->buildCategoryTreeForAPI($category->id);
            $category->has_children = count($category->children) > 0;
        }

        return $categories;
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|uuid|exists:product_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
            'categories.*.parent_id' => 'nullable|uuid|exists:product_categories,id',
        ]);

        DB::beginTransaction();
        
        try {
            foreach ($request->categories as $categoryData) {
                $data = [
                    'sort_order' => $categoryData['sort_order'],
                    'updated_at' => now()
                ];

                if (isset($categoryData['parent_id'])) {
                    $data['parent_id'] = $categoryData['parent_id'];
                    
                    // Recalculate level and path
                    if ($categoryData['parent_id']) {
                        $parent = DB::table('product_categories')
                            ->where('id', $categoryData['parent_id'])
                            ->first();
                        
                        if ($parent) {
                            $data['level'] = $parent->level + 1;
                            $data['path'] = $parent->path ? $parent->path . '/' . $categoryData['parent_id'] : $categoryData['parent_id'];
                        }
                    } else {
                        $data['level'] = 0;
                        $data['path'] = null;
                    }
                }

                DB::table('product_categories')
                    ->where('id', $categoryData['id'])
                    ->update($data);

                // Update children paths if parent changed
                if (isset($categoryData['parent_id'])) {
                    $this->updateChildrenPaths($categoryData['id']);
                }
            }

            DB::commit();
            return response()->json(['success' => 'Categories reordered successfully!']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to reorder categories: ' . $e->getMessage()], 500);
        }
    }

    public function uploadMedia(Request $request, $id)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'alt_text.*' => 'nullable|string|max:255',
            'title.*' => 'nullable|string|max:255',
            'description.*' => 'nullable|string',
        ]);

        $category = DB::table('product_categories')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$category) {
            return response()->json(['error' => 'Category not found!'], 404);
        }

        $uploadedFiles = [];

        foreach ($request->file('files') as $index => $file) {
            $path = $file->store('categories/media', 'public');
            
            $mediaId = Str::uuid();
            
            DB::table('product_category_media')->insert([
                'id' => $mediaId,
                'category_id' => $id,
                'file_path' => $path,
                'file_name' => pathinfo($path, PATHINFO_BASENAME),
                'original_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'alt_text' => $request->alt_text[$index] ?? null,
                'title' => $request->title[$index] ?? null,
                'description' => $request->description[$index] ?? null,
                'media_type' => 'image',
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $uploadedFiles[] = [
                'id' => $mediaId,
                'url' => Storage::url($path),
                'original_name' => $file->getClientOriginalName(),
            ];
        }

        return response()->json([
            'success' => 'Media uploaded successfully!',
            'files' => $uploadedFiles
        ]);
    }

    public function deleteMedia($categoryId, $mediaId)
    {
        $media = DB::table('product_category_media')
            ->where('id', $mediaId)
            ->where('category_id', $categoryId)
            ->first();

        if (!$media) {
            return response()->json(['error' => 'Media not found!'], 404);
        }

        // Delete file
        Storage::disk('public')->delete($media->file_path);

        // Delete record
        DB::table('product_category_media')
            ->where('id', $mediaId)
            ->delete();

        return response()->json(['success' => 'Media deleted successfully!']);
    }
}