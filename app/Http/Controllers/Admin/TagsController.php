<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagsController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('tags')->select('*');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('slug', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $tags = $query->orderBy('usage_count', 'desc')
                     ->orderBy('name')
                     ->paginate(20);

        return view('admin.tags.index', compact('tags'));
    }

    public function create()
    {
        return view('admin.tags.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'nullable|string|max:50|unique:tags,slug',
            'description' => 'nullable|string',
            'type' => 'required|in:product,blog,general',
            'color' => 'nullable|string|max:7'
        ]);

        $slug = $request->slug ?: Str::slug($request->name);

        DB::table('tags')->insert([
            'id' => Str::uuid(),
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'type' => $request->type,
            'color' => $request->color,
            'usage_count' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag created successfully');
    }

    public function show($id)
    {
        $tag = DB::table('tags')->where('id', $id)->first();
        
        if (!$tag) {
            abort(404);
        }

        // Get related products
        $products = [];
        if (in_array($tag->type, ['product', 'general'])) {
            $products = DB::table('product_tags as pt')
                ->join('products as p', 'pt.product_id', '=', 'p.id')
                ->select('p.*')
                ->where('pt.tag_id', $id)
                ->where('p.status', 'published')
                ->whereNull('p.deleted_at')
                ->orderBy('p.created_at', 'desc')
                ->limit(10)
                ->get();
        }

        // Get related blogs
        $blogs = [];
        if (in_array($tag->type, ['blog', 'general'])) {
            $blogs = DB::table('blog_tags as bt')
                ->join('blogs as b', 'bt.blog_id', '=', 'b.id')
                ->join('users as u', 'b.author_id', '=', 'u.id')
                ->select('b.*', 'u.first_name', 'u.last_name')
                ->where('bt.tag_id', $id)
                ->where('b.status', 'published')
                ->whereNull('b.deleted_at')
                ->orderBy('b.created_at', 'desc')
                ->limit(10)
                ->get();
        }

        return view('admin.tags.show', compact('tag', 'products', 'blogs'));
    }

    public function edit($id)
    {
        $tag = DB::table('tags')->where('id', $id)->first();
        
        if (!$tag) {
            abort(404);
        }

        return view('admin.tags.edit', compact('tag'));
    }

    public function update(Request $request, $id)
    {
        $tag = DB::table('tags')->where('id', $id)->first();
        
        if (!$tag) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'nullable|string|max:50|unique:tags,slug,' . $id,
            'description' => 'nullable|string',
            'type' => 'required|in:product,blog,general',
            'color' => 'nullable|string|max:7'
        ]);

        $slug = $request->slug ?: Str::slug($request->name);

        DB::table('tags')->where('id', $id)->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'type' => $request->type,
            'color' => $request->color,
            'updated_at' => now()
        ]);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag updated successfully');
    }

    public function destroy($id)
    {
        $tag = DB::table('tags')->where('id', $id)->first();
        
        if (!$tag) {
            abort(404);
        }

        // Check if tag is being used
        $productCount = DB::table('product_tags')->where('tag_id', $id)->count();
        $blogCount = DB::table('blog_tags')->where('tag_id', $id)->count();

        if ($productCount > 0 || $blogCount > 0) {
            return redirect()->route('admin.tags.index')
                ->with('error', 'Cannot delete tag. It is being used by ' . ($productCount + $blogCount) . ' items.');
        }

        DB::table('tags')->where('id', $id)->delete();

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag deleted successfully');
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', '');

        $tags = DB::table('tags')
            ->select('id', 'name', 'slug', 'type', 'color')
            ->where('name', 'like', '%' . $query . '%');

        if ($type) {
            $tags->where('type', $type);
        }

        $tags = $tags->orderBy('usage_count', 'desc')
                    ->limit(20)
                    ->get();

        return response()->json($tags);
    }

    public function getByType($type)
    {
        $tags = DB::table('tags')
            ->select('id', 'name', 'slug', 'color', 'usage_count')
            ->where('type', $type)
            ->orderBy('usage_count', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json($tags);
    }
}