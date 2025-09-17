<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductVariantsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'sku' => 'required|string|max:100|unique:product_variants,sku',
            'name' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'weight' => 'nullable|numeric|min:0',
            'dimensions_length' => 'nullable|numeric|min:0',
            'dimensions_width' => 'nullable|numeric|min:0',
            'dimensions_height' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer',
            'status' => 'required|in:active,inactive',
            'attributes' => 'nullable|array',
            'attributes.*.name' => 'required|string|max:50',
            'attributes.*.value' => 'required|string|max:100',
            'store_pricing' => 'nullable|array',
            'store_pricing.*.store_id' => 'required|exists:stores,id',
            'store_pricing.*.price' => 'required|numeric|min:0',
            'store_pricing.*.sale_price' => 'nullable|numeric|min:0',
            'store_pricing.*.cost_price' => 'nullable|numeric|min:0',
            'store_pricing.*.stock_quantity' => 'required|integer|min:0',
            'store_pricing.*.min_stock_level' => 'nullable|integer|min:0',
            'store_pricing.*.max_stock_level' => 'nullable|integer',
            'store_pricing.*.manage_stock' => 'nullable|boolean',
            'store_pricing.*.stock_status' => 'required|in:in_stock,out_of_stock,on_backorder'
        ]);

        $variantId = Str::uuid();
        $imagePath = null;

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $imagePath = $file->storeAs('variants/' . $variantId, $fileName, 'public');
        }

        // Insert variant
        DB::table('product_variants')->insert([
            'id' => $variantId,
            'product_id' => $request->product_id,
            'sku' => $request->sku,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath,
            'weight' => $request->weight,
            'dimensions_length' => $request->dimensions_length,
            'dimensions_width' => $request->dimensions_width,
            'dimensions_height' => $request->dimensions_height,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->status,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insert variant attributes
        if ($request->attributes) {
            foreach ($request->attributes as $attribute) {
                DB::table('variant_attributes')->insert([
                    'id' => Str::uuid(),
                    'variant_id' => $variantId,
                    'attribute_name' => $attribute['name'],
                    'attribute_value' => $attribute['value'],
                    'created_at' => now()
                ]);
            }
        }

        // Insert store pricing
        if ($request->store_pricing) {
            foreach ($request->store_pricing as $pricing) {
                DB::table('variant_stores')->insert([
                    'id' => Str::uuid(),
                    'variant_id' => $variantId,
                    'store_id' => $pricing['store_id'],
                    'price' => $pricing['price'],
                    'sale_price' => $pricing['sale_price'] ?? null,
                    'cost_price' => $pricing['cost_price'] ?? null,
                    'stock_quantity' => $pricing['stock_quantity'],
                    'min_stock_level' => $pricing['min_stock_level'] ?? 0,
                    'max_stock_level' => $pricing['max_stock_level'] ?? null,
                    'manage_stock' => $pricing['manage_stock'] ?? true,
                    'stock_status' => $pricing['stock_status'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'variant_id' => $variantId,
            'message' => 'Variant created successfully'
        ]);
    }

    public function show($id)
    {
        $variant = DB::table('product_variants as pv')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->select('pv.*', 'p.name as product_name', 'p.slug as product_slug')
            ->where('pv.id', $id)
            ->whereNull('pv.deleted_at')
            ->first();

        if (!$variant) {
            abort(404);
        }

        // Get variant attributes
        $attributes = DB::table('variant_attributes')
            ->where('variant_id', $id)
            ->get();

        // Get store pricing
        $storePricing = DB::table('variant_stores as vs')
            ->join('stores as s', 'vs.store_id', '=', 's.id')
            ->select('vs.*', 's.name as store_name', 's.currency')
            ->where('vs.variant_id', $id)
            ->get();

        return response()->json([
            'variant' => $variant,
            'attributes' => $attributes,
            'store_pricing' => $storePricing
        ]);
    }

    public function update(Request $request, $id)
    {
        $variant = DB::table('product_variants')->where('id', $id)->first();
        
        if (!$variant) {
            abort(404);
        }

        $request->validate([
            'sku' => 'required|string|max:100|unique:product_variants,sku,' . $id,
            'name' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'weight' => 'nullable|numeric|min:0',
            'dimensions_length' => 'nullable|numeric|min:0',
            'dimensions_width' => 'nullable|numeric|min:0',
            'dimensions_height' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer',
            'status' => 'required|in:active,inactive',
            'attributes' => 'nullable|array',
            'attributes.*.name' => 'required|string|max:50',
            'attributes.*.value' => 'required|string|max:100'
        ]);

        $updateData = [
            'sku' => $request->sku,
            'name' => $request->name,
            'description' => $request->description,
            'weight' => $request->weight,
            'dimensions_length' => $request->dimensions_length,
            'dimensions_width' => $request->dimensions_width,
            'dimensions_height' => $request->dimensions_height,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->status,
            'updated_at' => now()
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($variant->image) {
                Storage::disk('public')->delete($variant->image);
            }

            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $updateData['image'] = $file->storeAs('variants/' . $id, $fileName, 'public');
        }

        DB::table('product_variants')->where('id', $id)->update($updateData);

        // Update attributes
        DB::table('variant_attributes')->where('variant_id', $id)->delete();
        if ($request->attributes) {
            foreach ($request->attributes as $attribute) {
                DB::table('variant_attributes')->insert([
                    'id' => Str::uuid(),
                    'variant_id' => $id,
                    'attribute_name' => $attribute['name'],
                    'attribute_value' => $attribute['value'],
                    'created_at' => now()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Variant updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $variant = DB::table('product_variants')->where('id', $id)->first();
        
        if (!$variant) {
            abort(404);
        }

        // Delete image
        if ($variant->image) {
            Storage::disk('public')->delete($variant->image);
        }

        // Soft delete
        DB::table('product_variants')->where('id', $id)->update([
            'deleted_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Variant deleted successfully'
        ]);
    }

    public function updateStorePricing(Request $request, $id)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'max_stock_level' => 'nullable|integer',
            'manage_stock' => 'nullable|boolean',
            'stock_status' => 'required|in:in_stock,out_of_stock,on_backorder',
            'is_active' => 'nullable|boolean'
        ]);

        $exists = DB::table('variant_stores')
            ->where('variant_id', $id)
            ->where('store_id', $request->store_id)
            ->exists();

        $data = [
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'cost_price' => $request->cost_price,
            'stock_quantity' => $request->stock_quantity,
            'min_stock_level' => $request->min_stock_level ?? 0,
            'max_stock_level' => $request->max_stock_level,
            'manage_stock' => $request->boolean('manage_stock', true),
            'stock_status' => $request->stock_status,
            'is_active' => $request->boolean('is_active', true),
            'updated_at' => now()
        ];

        if ($exists) {
            DB::table('variant_stores')
                ->where('variant_id', $id)
                ->where('store_id', $request->store_id)
                ->update($data);
        } else {
            $data['id'] = Str::uuid();
            $data['variant_id'] = $id;
            $data['store_id'] = $request->store_id;
            $data['created_at'] = now();
            
            DB::table('variant_stores')->insert($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Store pricing updated successfully'
        ]);
    }

    public function getStorePricing($id, $storeId)
    {
        $pricing = DB::table('variant_stores as vs')
            ->join('stores as s', 'vs.store_id', '=', 's.id')
            ->select('vs.*', 's.name as store_name', 's.currency')
            ->where('vs.variant_id', $id)
            ->where('vs.store_id', $storeId)
            ->first();

        if (!$pricing) {
            return response()->json(['error' => 'Pricing not found'], 404);
        }

        return response()->json($pricing);
    }
}