<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecommendedProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecommendedProductController extends Controller
{
    public function index(Request $request)
    {
        $query = RecommendedProduct::with('product')
            ->orderBy('section')
            ->orderBy('sort_order');

        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        $recommendedProducts = $query->paginate($request->get('per_page', 10));

        return view('admin.recommended_products.index', compact('recommendedProducts'));
    }

    public function create()
    {
        $products = Product::select('id', 'name')->get();
        return view('admin.recommended_products.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('recommended_products')->where(function ($query) use ($request) {
                    return $query->where('section', $request->section);
                }),
            ],
            'section'    => 'required|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);


        RecommendedProduct::create($request->all());

        return redirect()->route('admin.settings.recommended-products.index')
            ->with('success', 'Recommended Product added successfully.');
    }

    public function edit($id)
    {
        $recommendedProduct = RecommendedProduct::findOrFail($id);
        $products = Product::select('id', 'name')->get();
        return view('admin.recommended_products.edit', compact('recommendedProduct', 'products'));
    }

    public function update(Request $request, $id)
    {
        $recommendedProduct = RecommendedProduct::findOrFail($id);

        $request->validate([
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('recommended_products')->where(function ($query) use ($request) {
                    return $query->where('section', $request->section);
                })->ignore($recommendedProduct->id),
            ],
            'section'    => 'required|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);


        $recommendedProduct->update($request->all());

        return redirect()->route('admin.settings.recommended-products.index')
            ->with('success', 'Recommended Product updated successfully.');
    }

    public function destroy($id)
    {
        $recommendedProduct = RecommendedProduct::findOrFail($id);
        $recommendedProduct->delete();

        return redirect()->route('admin.settings.recommended-products.index')
            ->with('success', 'Recommended Product deleted successfully.');
    }
}
