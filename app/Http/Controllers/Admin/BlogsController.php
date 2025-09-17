<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogsController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('blogs as b')
            ->join('users as u', 'b.author_id', '=', 'u.id')
            ->join('stores as s', 'b.store_id', '=', 's.id')
            ->select('b.*', 'u.first_name', 'u.last_name', 's.name as store_name')
            ->whereNull('b.deleted_at');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('b.title', 'like', '%' . $request->search . '%')
                  ->orWhere('b.slug', 'like', '%' . $request->search . '%')
                  ->orWhere('b.excerpt', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('b.status', $request->status);
        }

        if ($request->filled('store_id')) {
            $query->where('b.store_id', $request->store_id);
        }

        if ($request->filled('author_id')) {
            $query->where('b.author_id', $request->author_id);
        }

        $blogs = $query->orderBy('b.created_at', 'desc')->paginate(20);

        $stores = DB::table('stores')->select('id', 'name')->where('status', 'active')->get();
        $authors = DB::table('users')->select('id', 'first_name', 'last_name')
            ->whereIn('role', ['admin', 'manager', 'author'])->get();

        return view('admin.blogs.index', compact('blogs', 'stores', 'authors'));
    }

    public function create()
    {
        $stores = DB::table('stores')->select('id', 'name')->where('status', 'active')->get();
        $categories = DB::table('blog_categories')
            ->select('id', 'name', 'level', 'path')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('path')
            ->get();
        $tags = DB::table('tags')
            ->select('id', 'name')
            ->whereIn('type', ['blog', 'general'])
            ->get();

        return view('admin.blogs.create', compact('stores', 'categories', 'tags'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published,scheduled,archived',
            'publish_at' => 'nullable|date',
            'store_id' => 'required|exists:stores,id',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:blog_categories,id',
            'primary_category' => 'required|exists:blog_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'is_featured' => 'nullable|boolean',
            'allow_comments' => 'nullable|boolean',
            'reading_time' => 'nullable|integer'
        ]);

        $slug = $request->slug ?: Str::slug($request->title);
        
        // Check slug uniqueness with store
        $slugExists = DB::table('blogs')
            ->where('slug', $slug)
            ->where('store_id', $request->store_id)
            ->exists();

        if ($slugExists) {
            $slug .= '-' . time();
        }

        $blogId = Str::uuid();
        $featuredImage = null;

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $featuredImage = $file->storeAs('blogs/' . $blogId, $fileName, 'public');
        }

        // Insert blog
        DB::table('blogs')->insert([
            'id' => $blogId,
            'title' => $request->title,
            'slug' => $slug,
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'featured_image' => $featuredImage,
            'status' => $request->status,
            'publish_at' => $request->status === 'scheduled' ? $request->publish_at : null,
            'store_id' => $request->store_id,
            'author_id' => session('admin_user_id'),
            'is_featured' => $request->boolean('is_featured'),
            'allow_comments' => $request->boolean('allow_comments', true),
            'reading_time' => $request->reading_time,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insert category relationships
        foreach ($request->categories as $categoryId) {
            DB::table('blog_category_relationships')->insert([
                'id' => Str::uuid(),
                'blog_id' => $blogId,
                'category_id' => $categoryId,
                'is_primary' => $categoryId === $request->primary_category,
                'created_at' => now()
            ]);
        }

        // Insert tag relationships
        if ($request->tags) {
            foreach ($request->tags as $tagId) {
                DB::table('blog_tags')->insert([
                    'id' => Str::uuid(),
                    'blog_id' => $blogId,
                    'tag_id' => $tagId,
                    'created_at' => now()
                ]);

                // Update tag usage count
                DB::table('tags')->where('id', $tagId)->increment('usage_count');
            }
        }

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post created successfully');
    }

    public function show($id)
    {
        $blog = DB::table('blogs as b')
            ->join('users as u', 'b.author_id', '=', 'u.id')
            ->join('stores as s', 'b.store_id', '=', 's.id')
            ->select('b.*', 'u.first_name', 'u.last_name', 's.name as store_name')
            ->where('b.id', $id)
            ->first();

        if (!$blog) {
            abort(404);
        }

        // Get categories
        $categories = DB::table('blog_category_relationships as bcr')
            ->join('blog_categories as bc', 'bcr.category_id', '=', 'bc.id')
            ->select('bc.*', 'bcr.is_primary')
            ->where('bcr.blog_id', $id)
            ->get();

        // Get tags
        $tags = DB::table('blog_tags as bt')
            ->join('tags as t', 'bt.tag_id', '=', 't.id')
            ->select('t.*')
            ->where('bt.blog_id', $id)
            ->get();

        // Get media
        $media = DB::table('blog_media')
            ->where('blog_id', $id)
            ->orderBy('sort_order')
            ->get();

        // Get SEO data
        $seo = DB::table('blog_seo')->where('blog_id', $id)->first();

        return view('admin.blogs.show', compact('blog', 'categories', 'tags', 'media', 'seo'));
    }

    public function edit($id)
    {
        $blog = DB::table('blogs')->where('id', $id)->first();
        
        if (!$blog) {
            abort(404);
        }

        $stores = DB::table('stores')->select('id', 'name')->where('status', 'active')->get();
        $allCategories = DB::table('blog_categories')
            ->select('id', 'name', 'level', 'path')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('path')
            ->get();

        // Get selected categories
        $selectedCategories = DB::table('blog_category_relationships as bcr')
            ->join('blog_categories as bc', 'bcr.category_id', '=', 'bc.id')
            ->select('bc.*', 'bcr.is_primary')
            ->where('bcr.blog_id', $id)
            ->get();

        // Get selected tags
        $selectedTags = DB::table('blog_tags as bt')
            ->join('tags as t', 'bt.tag_id', '=', 't.id')
            ->select('t.*')
            ->where('bt.blog_id', $id)
            ->get();

        $allTags = DB::table('tags')
            ->select('id', 'name')
            ->whereIn('type', ['blog', 'general'])
            ->get();

        return view('admin.blogs.edit', compact(
            'blog', 'stores', 'allCategories', 'selectedCategories', 
            'selectedTags', 'allTags'
        ));
    }

    public function update(Request $request, $id)
    {
        $blog = DB::table('blogs')->where('id', $id)->first();
        
        if (!$blog) {
            abort(404);
        }

        $request->validate([
            'title' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published,scheduled,archived',
            'publish_at' => 'nullable|date',
            'store_id' => 'required|exists:stores,id',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:blog_categories,id',
            'primary_category' => 'required|exists:blog_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'is_featured' => 'nullable|boolean',
            'allow_comments' => 'nullable|boolean',
            'reading_time' => 'nullable|integer'
        ]);

        $slug = $request->slug ?: Str::slug($request->title);
        
        // Check slug uniqueness
        $slugExists = DB::table('blogs')
            ->where('slug', $slug)
            ->where('store_id', $request->store_id)
            ->where('id', '!=', $id)
            ->exists();

        if ($slugExists) {
            $slug .= '-' . time();
        }

        $updateData = [
            'title' => $request->title,
            'slug' => $slug,
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'status' => $request->status,
            'publish_at' => $request->status === 'scheduled' ? $request->publish_at : null,
            'store_id' => $request->store_id,
            'is_featured' => $request->boolean('is_featured'),
            'allow_comments' => $request->boolean('allow_comments', true),
            'reading_time' => $request->reading_time,
            'updated_at' => now()
        ];

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }

            $file = $request->file('featured_image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $updateData['featured_image'] = $file->storeAs('blogs/' . $id, $fileName, 'public');
        }

        DB::table('blogs')->where('id', $id)->update($updateData);

        // Update categories
        DB::table('blog_category_relationships')->where('blog_id', $id)->delete();
        foreach ($request->categories as $categoryId) {
            DB::table('blog_category_relationships')->insert([
                'id' => Str::uuid(),
                'blog_id' => $id,
                'category_id' => $categoryId,
                'is_primary' => $categoryId === $request->primary_category,
                'created_at' => now()
            ]);
        }

        // Update tags
        $oldTags = DB::table('blog_tags')->where('blog_id', $id)->pluck('tag_id');
        DB::table('blog_tags')->where('blog_id', $id)->delete();

        // Decrement old tag usage counts
        if ($oldTags->count() > 0) {
            DB::table('tags')->whereIn('id', $oldTags)->decrement('usage_count');
        }

        // Insert new tags
        if ($request->tags) {
            foreach ($request->tags as $tagId) {
                DB::table('blog_tags')->insert([
                    'id' => Str::uuid(),
                    'blog_id' => $id,
                    'tag_id' => $tagId,
                    'created_at' => now()
                ]);

                // Increment tag usage count
                DB::table('tags')->where('id', $tagId)->increment('usage_count');
            }
        }

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post updated successfully');
    }

    public function destroy($id)
    {
        $blog = DB::table('blogs')->where('id', $id)->first();
        
        if ($blog) {
            // Decrement tag usage counts
            $tagIds = DB::table('blog_tags')->where('blog_id', $id)->pluck('tag_id');
            if ($tagIds->count() > 0) {
                DB::table('tags')->whereIn('id', $tagIds)->decrement('usage_count');
            }

            // Soft delete
            DB::table('blogs')->where('id', $id)->update([
                'deleted_at' => now()
            ]);
        }

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post deleted successfully');
    }

    public function uploadMedia(Request $request, $id)
    {
        $request->validate([
            'media.*' => 'required|file|max:10240' // 10MB max
        ]);

        $uploadedFiles = [];

        foreach ($request->file('media') as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('blogs/' . $id, $fileName, 'public');

            $mediaType = 'document';
            if ($file->isImage()) {
                $mediaType = 'image';
            } elseif (str_contains($file->getMimeType(), 'video')) {
                $mediaType = 'video';
            } elseif (str_contains($file->getMimeType(), 'audio')) {
                $mediaType = 'audio';
            }

            $mediaId = Str::uuid();
            DB::table('blog_media')->insert([
                'id' => $mediaId,
                'blog_id' => $id,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'original_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'media_type' => $mediaType,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $uploadedFiles[] = [
                'id' => $mediaId,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'media_type' => $mediaType,
                'url' => Storage::url($filePath)
            ];
        }

        return response()->json(['files' => $uploadedFiles]);
    }

    public function deleteMedia($blogId, $mediaId)
    {
        $media = DB::table('blog_media')
            ->where('id', $mediaId)
            ->where('blog_id', $blogId)
            ->first();

        if ($media) {
            Storage::disk('public')->delete($media->file_path);
            DB::table('blog_media')->where('id', $mediaId)->delete();
        }

        return response()->json(['success' => true]);
    }
}