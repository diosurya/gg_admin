<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;

class SliderController extends Controller
{

    public function index(Request $request)
    {
        $query = Slider::query();


        // Search (title, caption, link)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('caption', 'like', "%{$search}%")
                ->orWhere('link', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Exact sort_order filter (optional)
        if ($request->filled('sort_order')) {
            $query->where('sort_order', $request->sort_order);
        }

        // Sorting: default by sort_order asc, then created_at desc
        $orderBy = in_array($request->input('order_by'), ['sort_order','created_at','title']) ? $request->input('order_by') : 'sort_order';
        $orderDir = in_array($request->input('order_dir'), ['asc','desc']) ? $request->input('order_dir') : 'asc';

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0) $perPage = 10;

        $sliders = $query->orderBy($orderBy, $orderDir)
                        ->orderBy('created_at', 'desc')
                        ->paginate($perPage)
                        ->appends($request->query());
        // dd($sliders);


        return view('admin.sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('admin.sliders.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
            'image' => 'required|image|dimensions:width=2376,height=807|max:4096',
            'link' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('sliders', 'public');
        }

        Slider::create($validated);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider created successfully!');
    }

    public function edit(Slider $slider)
    {
        return view('admin.sliders.edit', compact('slider'));
    }

    public function update(Request $request, Slider $slider)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
            'image' => 'nullable|image|dimensions:width=2376,height=807|max:4096',
            'link' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('sliders', 'public');
        }

        $slider->update($validated);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider updated successfully!');
    }

    public function destroy(Slider $slider)
    {
        $slider->delete();
        return redirect()->route('admin.sliders.index')->with('success', 'Slider deleted!');
    }
}
