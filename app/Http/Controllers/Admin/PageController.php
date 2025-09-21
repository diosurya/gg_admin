<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $query = Page::with(['creator', 'updater', 'parent']);

        // Search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Template filter
        if ($request->filled('template')) {
            $query->where('template', $request->template);
        }

        // Parent filter
        if ($request->filled('parent_id')) {
            if ($request->parent_id === 'none') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        // Show in menu filter
        if ($request->filled('show_in_menu')) {
            $query->where('show_in_menu', $request->show_in_menu === '1');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if ($sortBy === 'title') {
            $query->orderBy('title', $sortDirection);
        } elseif ($sortBy === 'sort_order') {
            $query->orderBy('sort_order', $sortDirection);
        } else {
            $query->orderBy('created_at', $sortDirection);
        }

        $perPage = $request->get('per_page', 10);
        $pages = $query->paginate($perPage);

        // Preserve query parameters in pagination links
        $pages->appends($request->query());

        // Get parent pages for filter
        $parentPages = Page::whereNull('parent_id')
            ->orderBy('title')
            ->get(['id', 'title']);

        // Get available templates
        $templates = $this->getAvailableTemplates();

        return view('admin.pages.index', compact(
            'pages', 
            'parentPages', 
            'templates'
        ));
    }

    public function create()
    {
        $parentPages = Page::whereNull('parent_id')
            ->orderBy('title')
            ->get(['id', 'title']);
        
        $templates = $this->getAvailableTemplates();

        return view('admin.pages.create', compact('parentPages', 'templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'template' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published,archived',
            'sort_order' => 'nullable|integer|min:0',
            'is_homepage' => 'boolean',
            'show_in_menu' => 'boolean',
            'parent_id' => 'nullable|exists:pages,id',
            'published_at' => 'nullable|date',
            
            // SEO validation
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string',
            'seo_og_title' => 'nullable|string|max:255',
            'seo_og_description' => 'nullable|string|max:300',
            'seo_og_image' => 'nullable|image|max:2048',
            'seo_og_type' => 'nullable|string|max:50',
            'seo_twitter_card' => 'nullable|string|max:50',
            'seo_twitter_title' => 'nullable|string|max:255',
            'seo_twitter_description' => 'nullable|string|max:200',
            'seo_twitter_image' => 'nullable|image|max:2048',
            'seo_canonical_url' => 'nullable|url|max:500',
            'seo_robots' => 'nullable|string|max:100',
            'seo_schema_markup' => 'nullable|json',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $page = new Page();
            $validated['slug'] = $page->generateUniqueSlug($validated['title']);
        }

        // Handle file uploads
        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $this->uploadImage($request->file('featured_image'), 'pages/featured');
        }

        if ($request->hasFile('seo_og_image')) {
            $validated['seo_og_image'] = $this->uploadImage($request->file('seo_og_image'), 'pages/seo');
        }

        if ($request->hasFile('seo_twitter_image')) {
            $validated['seo_twitter_image'] = $this->uploadImage($request->file('seo_twitter_image'), 'pages/seo');
        }

        // Handle homepage setting
        if (!empty($validated['is_homepage'])) {
            // Remove homepage flag from other pages
            Page::where('is_homepage', true)->update(['is_homepage' => false]);
        }

        // Handle published_at
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $page = Page::create($validated);

        return redirect()
            ->route('admin.pages.show', $page->id)
            ->with('success', 'Page created successfully!');
    }

    public function show(Page $page)
    {
        $page->load(['creator', 'updater', 'parent', 'children']);
        
        return view('admin.pages.show', compact('page'));
    }

    public function edit(Page $page)
    {
        $parentPages = Page::whereNull('parent_id')
            ->where('id', '!=', $page->id) // Exclude current page
            ->orderBy('title')
            ->get(['id', 'title']);
        
        $templates = $this->getAvailableTemplates();

        return view('admin.pages.form', compact('page', 'parentPages', 'templates'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')->ignore($page->id)
            ],
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'template' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published,archived',
            'sort_order' => 'nullable|integer|min:0',
            'is_homepage' => 'boolean',
            'show_in_menu' => 'boolean',
            'parent_id' => [
                'nullable',
                'exists:pages,id',
                function ($attribute, $value, $fail) use ($page) {
                    if ($value === $page->id) {
                        $fail('A page cannot be its own parent.');
                    }
                }
            ],
            'published_at' => 'nullable|date',
            
            // SEO validation
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string',
            'seo_og_title' => 'nullable|string|max:255',
            'seo_og_description' => 'nullable|string|max:300',
            'seo_og_image' => 'nullable|image|max:2048',
            'seo_og_type' => 'nullable|string|max:50',
            'seo_twitter_card' => 'nullable|string|max:50',
            'seo_twitter_title' => 'nullable|string|max:255',
            'seo_twitter_description' => 'nullable|string|max:200',
            'seo_twitter_image' => 'nullable|image|max:2048',
            'seo_canonical_url' => 'nullable|url|max:500',
            'seo_robots' => 'nullable|string|max:100',
            'seo_schema_markup' => 'nullable|json',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = $page->generateUniqueSlug($validated['title']);
        }

        // Handle file uploads
        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($page->featured_image) {
                Storage::delete($page->featured_image);
            }
            $validated['featured_image'] = $this->uploadImage($request->file('featured_image'), 'pages/featured');
        }

        if ($request->hasFile('seo_og_image')) {
            if ($page->seo_og_image) {
                Storage::delete($page->seo_og_image);
            }
            $validated['seo_og_image'] = $this->uploadImage($request->file('seo_og_image'), 'pages/seo');
        }

        if ($request->hasFile('seo_twitter_image')) {
            if ($page->seo_twitter_image) {
                Storage::delete($page->seo_twitter_image);
            }
            $validated['seo_twitter_image'] = $this->uploadImage($request->file('seo_twitter_image'), 'pages/seo');
        }

        // Handle homepage setting
        if (!empty($validated['is_homepage']) && !$page->is_homepage) {
            // Remove homepage flag from other pages
            Page::where('is_homepage', true)->update(['is_homepage' => false]);
        }

        // Handle published_at
        if ($validated['status'] === 'published' && !$page->published_at && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $page->update($validated);

        return redirect()
            ->route('admin.pages.show', $page->id)
            ->with('success', 'Page updated successfully!');
    }

    public function destroy(Page $page)
    {
        // Check if page has children
        if ($page->children()->count() > 0) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete page that has child pages. Please move or delete child pages first.');
        }

        // Delete associated images
        if ($page->featured_image) {
            Storage::delete($page->featured_image);
        }
        if ($page->seo_og_image) {
            Storage::delete($page->seo_og_image);
        }
        if ($page->seo_twitter_image) {
            Storage::delete($page->seo_twitter_image);
        }

        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page deleted successfully!');
    }

    // Quick actions
    public function publish(Page $page)
    {
        $page->publish();
        
        return redirect()
            ->back()
            ->with('success', 'Page published successfully!');
    }

    public function unpublish(Page $page)
    {
        $page->unpublish();
        
        return redirect()
            ->back()
            ->with('success', 'Page unpublished successfully!');
    }

    public function archive(Page $page)
    {
        $page->archive();
        
        return redirect()
            ->back()
            ->with('success', 'Page archived successfully!');
    }

    public function duplicate(Page $page)
    {
        $newPage = $page->replicate();
        $newPage->id = (string) Str::uuid();
        $newPage->title = $page->title . ' (Copy)';
        $newPage->slug = $newPage->generateUniqueSlug($newPage->title);
        $newPage->status = 'draft';
        $newPage->is_homepage = false;
        $newPage->published_at = null;
        $newPage->view_count = 0;
        $newPage->save();

        return redirect()
            ->route('admin.pages.edit', $newPage->id)
            ->with('success', 'Page duplicated successfully!');
    }

    // Helper methods
    private function uploadImage($file, $path)
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($path, $filename, 'public');
    }

    private function getAvailableTemplates()
    {
        // You can customize this based on your available templates
        return [
            'default' => 'Default',
            'landing' => 'Landing Page',
            'about' => 'About Page',
            'contact' => 'Contact Page',
            'service' => 'Service Page',
        ];
    }

    // Bulk actions
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:publish,unpublish,archive,delete',
            'page_ids' => 'required|array',
            'page_ids.*' => 'exists:pages,id'
        ]);

        $pages = Page::whereIn('id', $request->page_ids);

        switch ($request->action) {
            case 'publish':
                $pages->update([
                    'status' => 'published',
                    'published_at' => now()
                ]);
                $message = 'Selected pages published successfully!';
                break;

            case 'unpublish':
                $pages->update([
                    'status' => 'draft',
                    'published_at' => null
                ]);
                $message = 'Selected pages unpublished successfully!';
                break;

            case 'archive':
                $pages->update(['status' => 'archived']);
                $message = 'Selected pages archived successfully!';
                break;

            case 'delete':
                // Check for pages with children
                $pagesWithChildren = $pages->has('children')->get();
                
                if ($pagesWithChildren->count() > 0) {
                    return redirect()
                        ->back()
                        ->with('error', 'Cannot delete pages that have child pages.');
                }
                
                // Delete associated images
                foreach ($pages->get() as $page) {
                    if ($page->featured_image) {
                        Storage::delete($page->featured_image);
                    }
                    if ($page->seo_og_image) {
                        Storage::delete($page->seo_og_image);
                    }
                    if ($page->seo_twitter_image) {
                        Storage::delete($page->seo_twitter_image);
                    }
                }
                
                $pages->delete();
                $message = 'Selected pages deleted successfully!';
                break;
        }

        return redirect()
            ->back()
            ->with('success', $message);
    }
}