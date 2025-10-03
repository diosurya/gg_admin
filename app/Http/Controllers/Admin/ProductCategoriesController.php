<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductCategoriesController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('product_categories as pc')
            ->leftJoin('product_categories as parent', 'pc.parent_id', '=', 'parent.id')
            ->whereNull('pc.deleted_at')
            ->select(
                'pc.*',
                'parent.name as parent_name'
            );

            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('pc.name', 'like', "%{$request->search}%")
                    ->orWhere('pc.slug', 'like', "%{$request->search}%");
                });
            }

            if ($request->status) {
                $query->where('pc.status', $request->status);
            }

            $perPage = $request->get('per_page', 10);

            $categories = $query
                ->orderBy('pc.path')
                ->paginate($perPage)
                ->appends($request->query());
            
            // dd($categories);

            return view('admin.product_categories.index', compact('categories'));
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

    public function create()
    {
        $parentCategories = DB::table('product_categories')
            ->whereNull('deleted_at')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.product_categories.create', compact('parentCategories'));
    }

    public function reorder(Request $request)
    {
        $items = $request->input('items'); 

        foreach ($items as $item) {
            $level = $item['parent_id'] 
                ? DB::table('product_categories')->where('id', $item['parent_id'])->value('level') + 1 
                : 0;

            DB::table('product_categories')->where('id', $item['id'])->update([
                'parent_id' => $item['parent_id'],
                'level'     => $level,
                'sort_order'=> $item['order'],
                'updated_at'=> Carbon::now()
            ]);

            // update path juga
            $path = $item['parent_id'] 
                ? DB::table('product_categories')->where('id', $item['parent_id'])->value('path') . '/' . $item['id']
                : $item['id'];

            DB::table('product_categories')->where('id', $item['id'])->update(['path' => $path]);
        }

        return response()->json(['success' => true]);
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
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_categories,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
            'sort_order' => 'nullable|integer',
            'color' => 'nullable|string|max:7',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'icon' => 'nullable|image|max:1024',
            'media.*' => 'nullable|image|max:2048'
        ]);

        DB::beginTransaction();
        try {
            // Generate slug if empty
            $slug = $request->slug ?: Str::slug($request->name);

            // Calculate level and path
            $level = 0;
            $path = $slug;
            if ($request->parent_id) {
                $parent = DB::table('product_categories')->find($request->parent_id);
                $level = $parent->level + 1;
                $path = $parent->path . '/' . $slug;
            }

            // Upload main images
            $imagePath = null;
            $bannerPath = null;
            $iconPath = null;

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('categories/images', 'public');
            }
            if ($request->hasFile('banner')) {
                $bannerPath = $request->file('banner')->store('categories/banners', 'public');
            }
            if ($request->hasFile('icon')) {
                $iconPath = $request->file('icon')->store('categories/icons', 'public');
            }

            // Generate UUID for category ID
            $categoryId = (string) Str::uuid();

            // Insert category
            DB::table('product_categories')->insert([
                'id' => $categoryId,
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'image' => $imagePath,
                'banner' => $bannerPath,
                'icon' => $iconPath,
                'color' => $request->color,
                'parent_id' => $request->parent_id,
                'sort_order' => $request->sort_order ?? 0,
                'level' => $level,
                'path' => $path,
                'is_featured' => $request->has('is_featured') ? 1 : 0,
                'show_in_menu' => $request->has('show_in_menu') ? 1 : 0,
                'products_count' => 0,
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Upload media gallery
            if ($request->hasFile('media')) {
                $sortOrder = 1;
                foreach ($request->file('media') as $file) {
                    $filePath = $file->store('categories/media', 'public');

                    DB::table('product_category_media')->insert([
                        'id' => (string) Str::uuid(), // kalau pakai UUID juga
                        'category_id' => $categoryId,
                        'file_path' => $filePath,
                        'file_name' => $file->hashName(),
                        'original_name' => $file->getClientOriginalName(),
                        'file_type' => $file->extension(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'media_type' => 'image',
                        'sort_order' => $sortOrder++,
                        'is_featured' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.product-categories.index')
                ->with('success', 'Category created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create category: ' . $e->getMessage());
        }
    }


    public function edit($id)
    {
        $category = ProductCategory::where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$category) {
            return redirect()->route('admin.product-categories.index')
                ->with('error', 'Category not found!');
        }

        $parentCategories = ProductCategory::whereNull('deleted_at')
            ->where('id', '!=', $id)
            ->orderBy('name')
            ->get();

        $seoData = DB::table('product_category_seo')
            ->where('category_id', $id)
            ->get()
            ->keyBy(function ($item) {
                return $item->store_id ?? 'global';
            });

        $media = DB::table('product_category_media')
            ->where('category_id', $id)
            ->get();

        return view('admin.product_categories.edit', compact(
            'category',
            'parentCategories',
            'seoData',
            'media'
        ));
    }



    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_categories,slug,' . $id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
            'sort_order' => 'nullable|integer',
            'color' => 'nullable|string|max:7',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'icon' => 'nullable|image|max:1024',
            'media.*' => 'nullable|image|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $category = DB::table('product_categories')->where('id',$id)->first();
            if (!$category) return redirect()->back()->with('error','Category not found');

            $slug = $request->slug ?: Str::slug($request->name);

            // level & path
            $level = 0;
            $path = $slug;
            if ($request->parent_id) {
                $parent = DB::table('product_categories')->find($request->parent_id);
                $level = $parent->level + 1;
                $path = $parent->path . '/' . $slug;
            }

            $data = [
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'color' => $request->color,
                'parent_id' => $request->parent_id,
                'sort_order' => $request->sort_order ?? 0,
                'level' => $level,
                'path' => $path,
                'is_featured' => $request->has('is_featured') ? 1 : 0,
                'show_in_menu' => $request->has('show_in_menu') ? 1 : 0,
                'status' => $request->status,
                'updated_at' => now()
            ];

            // Replace/remove image
            if ($request->has('remove_image') && $category->image) {
                Storage::disk('public')->delete($category->image);
                $data['image'] = null;
            } elseif ($request->hasFile('image')) {
                if ($category->image) Storage::disk('public')->delete($category->image);
                $data['image'] = $request->file('image')->store('categories/images','public');
            }

            if ($request->has('remove_banner') && $category->banner) {
                Storage::disk('public')->delete($category->banner);
                $data['banner'] = null;
            } elseif ($request->hasFile('banner')) {
                if ($category->banner) Storage::disk('public')->delete($category->banner);
                $data['banner'] = $request->file('banner')->store('categories/banners','public');
            }

            if ($request->has('remove_icon') && $category->icon) {
                Storage::disk('public')->delete($category->icon);
                $data['icon'] = null;
            } elseif ($request->hasFile('icon')) {
                if ($category->icon) Storage::disk('public')->delete($category->icon);
                $data['icon'] = $request->file('icon')->store('categories/icons','public');
            }

            DB::table('product_categories')->where('id',$id)->update($data);

            // Remove selected media
            if ($request->filled('remove_media')) {
                $removeIds = $request->remove_media;
                $mediaFiles = DB::table('product_category_media')->whereIn('id',$removeIds)->get();
                foreach ($mediaFiles as $m) {
                    Storage::disk('public')->delete($m->file_path);
                }
                DB::table('product_category_media')->whereIn('id',$removeIds)->delete();
            }

            // Add new media
            if ($request->hasFile('media')) {
                $sortOrder = DB::table('product_category_media')->where('category_id',$id)->max('sort_order') + 1;
                foreach ($request->file('media') as $file) {
                    $filePath = $file->store('categories/media','public');
                    DB::table('product_category_media')->insert([
                        'id' => (string) Str::uuid(),
                        'category_id' => $id,
                        'file_path' => $filePath,
                        'file_name' => $file->hashName(),
                        'original_name' => $file->getClientOriginalName(),
                        'file_type' => $file->extension(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'media_type' => 'image',
                        'sort_order' => $sortOrder++,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.product-categories.index')->with('success','Category updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error','Failed to update category: '.$e->getMessage());
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

    // public function reorder(Request $request)
    // {
    //     $request->validate([
    //         'categories' => 'required|array',
    //         'categories.*.id' => 'required|uuid|exists:product_categories,id',
    //         'categories.*.sort_order' => 'required|integer|min:0',
    //         'categories.*.parent_id' => 'nullable|uuid|exists:product_categories,id',
    //     ]);

    //     DB::beginTransaction();
        
    //     try {
    //         foreach ($request->categories as $categoryData) {
    //             $data = [
    //                 'sort_order' => $categoryData['sort_order'],
    //                 'updated_at' => now()
    //             ];

    //             if (isset($categoryData['parent_id'])) {
    //                 $data['parent_id'] = $categoryData['parent_id'];
                    
    //                 // Recalculate level and path
    //                 if ($categoryData['parent_id']) {
    //                     $parent = DB::table('product_categories')
    //                         ->where('id', $categoryData['parent_id'])
    //                         ->first();
                        
    //                     if ($parent) {
    //                         $data['level'] = $parent->level + 1;
    //                         $data['path'] = $parent->path ? $parent->path . '/' . $categoryData['parent_id'] : $categoryData['parent_id'];
    //                     }
    //                 } else {
    //                     $data['level'] = 0;
    //                     $data['path'] = null;
    //                 }
    //             }

    //             DB::table('product_categories')
    //                 ->where('id', $categoryData['id'])
    //                 ->update($data);

    //             // Update children paths if parent changed
    //             if (isset($categoryData['parent_id'])) {
    //                 $this->updateChildrenPaths($categoryData['id']);
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['success' => 'Categories reordered successfully!']);

    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         return response()->json(['error' => 'Failed to reorder categories: ' . $e->getMessage()], 500);
    //     }
    // }

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