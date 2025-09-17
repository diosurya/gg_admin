<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeoController extends Controller
{
    /**
     * Show product SEO form
     */
    public function showProduct($id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        
        if (!$product) {
            return redirect()->back()->with('error', 'Product not found');
        }

        $stores = DB::table('stores')->where('status', 'active')->get();
        
        // Get existing SEO data for each store
        $seoData = DB::table('product_seo')
            ->where('product_id', $id)
            ->get()
            ->keyBy('store_id');

        return view('admin.seo.product', compact('product', 'stores', 'seoData'));
    }

    /**
     * Update product SEO
     */
    public function updateProduct($id, Request $request)
    {
        $request->validate([
            'store_id' => 'nullable|string',
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:70',
            'og_description' => 'nullable|string|max:160',
            'og_image' => 'nullable|string|max:255',
            'og_type' => 'nullable|string|max:50',
            'twitter_card' => 'nullable|string|max:50',
            'twitter_title' => 'nullable|string|max:70',
            'twitter_description' => 'nullable|string|max:160',
            'twitter_image' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|string|max:255',
            'robots' => 'nullable|string|max:50',
            'schema_markup' => 'nullable|json'
        ]);

        try {
            $seoData = [
                'product_id' => $id,
                'store_id' => $request->store_id,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'og_title' => $request->og_title,
                'og_description' => $request->og_description,
                'og_image' => $request->og_image,
                'og_type' => $request->og_type ?? 'product',
                'twitter_card' => $request->twitter_card ?? 'summary',
                'twitter_title' => $request->twitter_title,
                'twitter_description' => $request->twitter_description,
                'twitter_image' => $request->twitter_image,
                'canonical_url' => $request->canonical_url,
                'robots' => $request->robots ?? 'index,follow',
                'schema_markup' => $request->schema_markup,
                'updated_at' => now()
            ];

            $existing = DB::table('product_seo')
                ->where('product_id', $id)
                ->where('store_id', $request->store_id)
                ->first();

            if ($existing) {
                DB::table('product_seo')
                    ->where('id', $existing->id)
                    ->update($seoData);
            } else {
                $seoData['id'] = Str::uuid();
                $seoData['created_at'] = now();
                DB::table('product_seo')->insert($seoData);
            }

            return redirect()->back()->with('success', 'Product SEO updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating SEO: ' . $e->getMessage());
        }
    }

    /**
     * Show category SEO form
     */
    public function showCategory($id)
    {
        $category = DB::table('product_categories')->where('id', $id)->first();
        
        if (!$category) {
            return redirect()->back()->with('error', 'Category not found');
        }

        $stores = DB::table('stores')->where('status', 'active')->get();
        
        // Get existing SEO data for each store
        $seoData = DB::table('product_category_seo')
            ->where('category_id', $id)
            ->get()
            ->keyBy('store_id');

        return view('admin.seo.category', compact('category', 'stores', 'seoData'));
    }

    /**
     * Update category SEO
     */
    public function updateCategory($id, Request $request)
    {
        $request->validate([
            'store_id' => 'nullable|string',
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:70',
            'og_description' => 'nullable|string|max:160',
            'og_image' => 'nullable|string|max:255',
            'og_type' => 'nullable|string|max:50',
            'twitter_card' => 'nullable|string|max:50',
            'twitter_title' => 'nullable|string|max:70',
            'twitter_description' => 'nullable|string|max:160',
            'twitter_image' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|string|max:255',
            'robots' => 'nullable|string|max:50',
            'schema_markup' => 'nullable|json'
        ]);

        try {
            $seoData = [
                'category_id' => $id,
                'store_id' => $request->store_id,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'og_title' => $request->og_title,
                'og_description' => $request->og_description,
                'og_image' => $request->og_image,
                'og_type' => $request->og_type ?? 'website',
                'twitter_card' => $request->twitter_card ?? 'summary',
                'twitter_title' => $request->twitter_title,
                'twitter_description' => $request->twitter_description,
                'twitter_image' => $request->twitter_image,
                'canonical_url' => $request->canonical_url,
                'robots' => $request->robots ?? 'index,follow',
                'schema_markup' => $request->schema_markup,
                'updated_at' => now()
            ];

            $existing = DB::table('product_category_seo')
                ->where('category_id', $id)
                ->where('store_id', $request->store_id)
                ->first();

            if ($existing) {
                DB::table('product_category_seo')
                    ->where('id', $existing->id)
                    ->update($seoData);
            } else {
                $seoData['id'] = Str::uuid();
                $seoData['created_at'] = now();
                DB::table('product_category_seo')->insert($seoData);
            }

            return redirect()->back()->with('success', 'Category SEO updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating SEO: ' . $e->getMessage());
        }
    }

    /**
     * Show blog SEO form
     */
    public function showBlog($id)
    {
        $blog = DB::table('blogs')->where('id', $id)->first();
        
        if (!$blog) {
            return redirect()->back()->with('error', 'Blog not found');
        }

        // Get existing SEO data
        $seoData = DB::table('blog_seo')->where('blog_id', $id)->first();

        return view('admin.seo.blog', compact('blog', 'seoData'));
    }

    /**
     * Update blog SEO
     */
    public function updateBlog($id, Request $request)
    {
        $request->validate([
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:70',
            'og_description' => 'nullable|string|max:160',
            'og_image' => 'nullable|string|max:255',
            'og_type' => 'nullable|string|max:50',
            'twitter_card' => 'nullable|string|max:50',
            'twitter_title' => 'nullable|string|max:70',
            'twitter_description' => 'nullable|string|max:160',
            'twitter_image' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|string|max:255',
            'robots' => 'nullable|string|max:50',
            'schema_markup' => 'nullable|json'
        ]);

        try {
            $seoData = [
                'blog_id' => $id,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'og_title' => $request->og_title,
                'og_description' => $request->og_description,
                'og_image' => $request->og_image,
                'og_type' => $request->og_type ?? 'article',
                'twitter_card' => $request->twitter_card ?? 'summary',
                'twitter_title' => $request->twitter_title,
                'twitter_description' => $request->twitter_description,
                'twitter_image' => $request->twitter_image,
                'canonical_url' => $request->canonical_url,
                'robots' => $request->robots ?? 'index,follow',
                'schema_markup' => $request->schema_markup,
                'updated_at' => now()
            ];

            $existing = DB::table('blog_seo')->where('blog_id', $id)->first();

            if ($existing) {
                DB::table('blog_seo')
                    ->where('id', $existing->id)
                    ->update($seoData);
            } else {
                $seoData['id'] = Str::uuid();
                $seoData['created_at'] = now();
                DB::table('blog_seo')->insert($seoData);
            }

            return redirect()->back()->with('success', 'Blog SEO updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating SEO: ' . $e->getMessage());
        }
    }
}