<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogCategoriesController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('blog_categories')
            ->select('*')
            ->whereNull('deleted_at');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('slug', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('admin.blog-categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = DB::table('blog_categories')
            ->select('id', 'name', 'level', 'path')
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->orderBy('path')
            ->get();

        return view('admin.blog-categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:blog_categories,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:blog_categories,id',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer',
            'color' => 'nullable|string|max:7',
            'is_featured' => 'nullable|boolean',
            'show_in_menu' => 'nullable|boolean'
        ]);

        $slug = $request->slug ?: Str::slug($request->name);
        $id = Str::uuid();

        // Calculate level and path
        $level = 0;
        $path = $id;
        if ($request->parent_id) {
            $parent = DB::table('blog_categories')->where('id', $request->parent_id)->first();
            $level = $parent->level + 1;
            $path = $parent->path . '/' . $id;
        }

        DB::table('blog_categories')->insert([
            'id' => $id,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'level' => $level,
            'path' => $path,
            'sort_order' => $request->sort_order ?? 0,
            'color' => $request->color,
            'status' => $request->status,
            'is_featured' => $request->boolean('is_featured'),
            'show_in_menu' => $request->boolean('show_in_menu', true),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'Blog category created successfully');
    }

    public function show($id)
    {
        $category = DB::table('blog_categories')->where('id', $id)->first();
        
        if (!$category) {
            abort(404);
        }

        $blogs = DB::table('blogs as b')
            ->join('blog_category_relationships as bcr', 'b.id', '=', 'bcr.blog_id')
            ->join('users as u', 'b.author_id', '=', 'u.id')
            ->select('b.*', 'u.first_name', 'u.last_name')
            ->where('bcr.category_id', $id)
            ->where('b.status', 'published')
            ->orderBy('b.created_at', 'desc')
            ->paginate(10);

        return view('admin.blog-categories.show', compact('category', 'blogs'));
    }

    public function edit($id)
    {
        $category = DB::table('blog_categories')->where('id', $id)->first();
        
        if (!$category) {
            abort(404);
        }

        $parentCategories = DB::table('blog_categories')
            ->select('id', 'name', 'level', 'path')
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->where('id', '!=', $id) // Exclude current category
            ->whereNotLike('path', '%' . $id . '%') // Exclude descendants
            ->orderBy('path')
            ->get();

        return view('admin.blog-categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, $id)
    {
        $category = DB::table('blog_categories')->where('id', $id)->first();
        
        if (!$category) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:blog_categories,slug,' . $id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:blog_categories,id',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer',
            'color' => 'nullable|string|max:7',
            'is_featured' => 'nullable|boolean',
            'show_in_menu' => 'nullable|boolean'
        ]);

        $slug = $request->slug ?: Str::slug($request->name);

        // Calculate level and path
        $level = 0;
        $path = $id;
        if ($request->parent_id) {
            $parent = DB::table('blog_categories')->where('id', $request->parent_id)->first();
            $level = $parent->level + 1;
            $path = $parent->path . '/' . $id;
        }

        DB::table('blog_categories')->where('id', $id)->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'level' => $level,
            'path' => $path,
            'sort_order' => $request->sort_order ?? 0,
            'color' => $request->color,
            'status' => $request->status,
            'is_featured' => $request->boolean('is_featured'),
            'show_in_menu' => $request->boolean('show_in_menu', true),
            'updated_at' => now()
        ]);

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'Blog category updated successfully');
    }

    public function destroy($id)
    {
        DB::table('blog_categories')->where('id', $id)->update([
            'deleted_at' => now()
        ]);

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'Blog category deleted successfully');
    }

    public function getCategoryTree()
    {
        $categories = DB::table('blog_categories')
            ->select('id', 'name', 'parent_id', 'level', 'sort_order', 'status', 'is_featured')
            ->whereNull('deleted_at')
            ->orderBy('path')
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:blog_categories,id',
            'categories.*.sort_order' => 'required|integer'
        ]);

        foreach ($request->categories as $category) {
            DB::table('blog_categories')
                ->where('id', $category['id'])
                ->update(['sort_order' => $category['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    public function uploadMedia(Request $request, $id)
    {
        $request->validate([
            'media.*' => 'required|file|image|max:2048'
        ]);

        $uploadedFiles = [];

        foreach ($request->file('media') as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('blog-categories/' . $id, $fileName, 'public');

            $mediaId = Str::uuid();
            DB::table('blog_category_media')->insert([
                'id' => $mediaId,
                'category_id' => $id,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'original_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'media_type' => 'image',
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $uploadedFiles[] = [
                'id' => $mediaId,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'url' => Storage::url($filePath)
            ];
        }

        return response()->json(['files' => $uploadedFiles]);
    }

    public function deleteMedia($categoryId, $mediaId)
    {
        $media = DB::table('blog_category_media')
            ->where('id', $mediaId)
            ->where('category_id', $categoryId)
            ->first();

        if ($media) {
            Storage::disk('public')->delete($media->file_path);
            DB::table('blog_category_media')->where('id', $mediaId)->delete();
        }

        return response()->json(['success' => true]);
    }
}