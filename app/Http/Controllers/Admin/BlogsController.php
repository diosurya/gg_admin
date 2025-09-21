<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;


class BlogsController extends Controller {
    
    public function index(Request $request)
    {
        $query = DB::table('blogs as b')
            ->leftJoin('users as cu', 'b.created_by', '=', 'cu.id')
            ->leftJoin('blog_category_relationships as bcr', 'b.id', '=', 'bcr.blog_id')
            ->leftJoin('blog_categories as bc', 'bcr.category_id', '=', 'bc.id')
            ->select([
                'b.id',
                'b.title',
                'b.slug',
                'b.excerpt',
                'b.status',
                'b.publish_at',
                'b.created_at',
                'cu.first_name as creator_first_name',
                'cu.last_name as creator_last_name',
                DB::raw("GROUP_CONCAT(DISTINCT bc.name ORDER BY bc.name SEPARATOR ';') as category_names"),
            ])
            ->whereNull('b.deleted_at')
            ->groupBy(
                'b.id',
                'b.title',
                'b.slug',
                'b.excerpt',
                'b.status',
                'b.publish_at',
                'b.created_at',
                'cu.first_name',
                'cu.last_name'
            );

        // apply filters
        $this->applyFilters($query, $request);

        $perPage = $request->input('per_page', 10);

        $blogs = $query->orderBy('b.created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        // Ambil kategori untuk filter (tree-friendly)
        $categories = DB::table('blog_categories')
            ->select('id', 'name', 'parent_id')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.blogs.index', compact('blogs', 'categories'));
    }

    private function applyFilters($query, Request $request)
    {
        // 🔎 search by title / slug
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('b.title', 'LIKE', "%{$search}%")
                ->orWhere('b.slug', 'LIKE', "%{$search}%");
            });
        }

        // 🔎 filter by status
        if ($request->filled('status')) {
            $query->where('b.status', $request->status);
        }

        // 🔎 filter by categories (multi select)
        if ($request->filled('category_ids') && is_array($request->category_ids)) {
            $categoryIds = $request->category_ids;
            $query->whereExists(function ($sub) use ($categoryIds) {
                $sub->select(DB::raw(1))
                    ->from('blog_category_relationships as sbcr')
                    ->whereRaw('sbcr.blog_id = b.id')
                    ->whereIn('sbcr.category_id', $categoryIds);
            });
        }
    }

    public function create()
    {
        $blog = new Blog();
        $categories = BlogCategory::orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();
        $statuses = Blog::getStatuses();

        return view('admin.blogs.create', compact('blog', 'categories', 'tags', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:blogs,slug',
            'content'          => 'required|string',
            'excerpt'          => 'nullable|string|max:500',
            'featured_image'   => 'nullable|image|mimes:webp,jpeg,png,jpg,gif|max:2048',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:255',
            'status'           => 'required|in:draft,published,archived',
            'published_at'     => 'nullable|date',
            'publish_at'       => 'nullable|date',
            'category_ids'     => 'nullable|array',
            'category_ids.*'   => 'exists:blog_categories,id',
            'tag_ids'          => 'nullable|array',
            'tag_ids.*'        => 'exists:tags,id'
        ]);

        DB::beginTransaction();

        try {

            $data = $request->except(['featured_image', 'category_ids', 'tag_ids']);

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            // Handle featured image upload
            if ($request->hasFile('featured_image')) {
                $image = $request->file('featured_image');
                $imageName = time() . '_' . Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('blogs', $imageName, 'public');
                $data['featured_image'] = $imagePath;
            }

            // Set published_at if status is published but no date set
            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            // Default values for counters & options
            $data['view_count']    = 0;
            $data['likes_count']    = 0;
            $data['comments_count'] = 0;
            $data['reading_time']   = $data['reading_time'] ?? 0;
            $data['is_featured']    = $data['is_featured'] ?? false;
            $data['allow_comments'] = $data['allow_comments'] ?? true;

            $blog = Blog::create($data);

            // Sync categories
            if ($request->filled('category_ids')) {
                $blog->categories()->sync($request->category_ids);
            }

            // Sync tags
            if ($request->filled('tag_ids')) {
                $blog->tags()->sync($request->tag_ids);
            }

            DB::commit();

            return redirect()->route('admin.blogs.index')
                ->with('success', 'Blog created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])
                     ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        $blog->load(['categories', 'tags', 'creator', 'updater']);

        return view('admin.blogs.show', compact('blog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog)
    {
        $blog->load(['categories', 'tags']);
        $categories = BlogCategory::orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();
        $statuses = Blog::getStatuses();

        return view('admin.blogs.edit', compact('blog', 'categories', 'tags', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug'  => ['nullable', 'string', 'max:255', Rule::unique('blogs')->ignore($blog->id)],
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|image|mimes:webp,jpeg,png,jpg,gif|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:blog_categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id'
        ]);

        DB::beginTransaction();

        try {
            $data = $request->except(['featured_image', 'category_ids', 'tag_ids']);

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            if ($request->hasFile('featured_image')) {
                if ($blog->featured_image && Storage::disk('public')->exists($blog->featured_image)) {
                    Storage::disk('public')->delete($blog->featured_image);
                }

                $image = $request->file('featured_image');
                $imageName = time() . '_' . Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('blogs', $imageName, 'public');
                $data['featured_image'] = $imagePath;
            }

            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            $blog->update($data);

            $blog->categories()->sync($request->category_ids ?? []);
            $blog->tags()->sync($request->tag_ids ?? []);

            DB::commit();

            return redirect()->route('admin.blogs.index')
                ->with('success', 'Blog updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => $e->getMessage()])
                        ->withInput();
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        // Delete featured image if exists
        if ($blog->featured_image && Storage::disk('public')->exists($blog->featured_image)) {
            Storage::disk('public')->delete($blog->featured_image);
        }

        $blog->delete();

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog deleted successfully.');
    }

    /**
     * Remove featured image
     */
    public function removeImage(Blog $blog)
    {
        if ($blog->featured_image && Storage::disk('public')->exists($blog->featured_image)) {
            Storage::disk('public')->delete($blog->featured_image);
            $blog->update(['featured_image' => null]);
        }

        return response()->json(['success' => true]);
    }
}