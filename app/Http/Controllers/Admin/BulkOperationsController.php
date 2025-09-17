<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BulkOperationsController extends Controller
{
    /**
     * Bulk update product status
     */
    public function updateProductStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|string',
            'status' => 'required|in:draft,published,archived,out_of_stock'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Verify products exist and are not deleted
            $existingProducts = DB::table('products')
                ->whereIn('id', $request->product_ids)
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();

            if (count($existingProducts) !== count($request->product_ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some products were not found or have been deleted'
                ], 404);
            }

            // Update product status
            $updatedCount = DB::table('products')
                ->whereIn('id', $request->product_ids)
                ->whereNull('deleted_at')
                ->update([
                    'status' => $request->status,
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$updatedCount} products updated successfully",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk assign category to products
     */
    public function assignCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|string',
            'category_id' => 'required|string',
            'is_primary' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Verify category exists
            $category = DB::table('product_categories')
                ->where('id', $request->category_id)
                ->whereNull('deleted_at')
                ->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            // Verify products exist
            $existingProducts = DB::table('products')
                ->whereIn('id', $request->product_ids)
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();

            if (count($existingProducts) !== count($request->product_ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some products were not found or have been deleted'
                ], 404);
            }

            $assignedCount = 0;

            foreach ($existingProducts as $productId) {
                // Check if relationship already exists
                $exists = DB::table('product_category_relationships')
                    ->where('product_id', $productId)
                    ->where('category_id', $request->category_id)
                    ->exists();

                if (!$exists) {
                    // If setting as primary, remove primary flag from other categories for this product
                    if ($request->is_primary) {
                        DB::table('product_category_relationships')
                            ->where('product_id', $productId)
                            ->update(['is_primary' => false]);
                    }

                    DB::table('product_category_relationships')->insert([
                        'id' => \Illuminate\Support\Str::uuid(),
                        'product_id' => $productId,
                        'category_id' => $request->category_id,
                        'is_primary' => $request->is_primary ?? false,
                        'created_at' => now()
                    ]);

                    $assignedCount++;
                }
            }

            // Update category products count
            $categoryProductCount = DB::table('product_category_relationships')
                ->where('category_id', $request->category_id)
                ->count();

            DB::table('product_categories')
                ->where('id', $request->category_id)
                ->update(['products_count' => $categoryProductCount]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$assignedCount} products assigned to category successfully",
                'assigned_count' => $assignedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error assigning category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update pricing for product variants
     */
    public function updatePricing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'variant_ids' => 'required|array',
            'variant_ids.*' => 'required|string',
            'store_id' => 'required|string',
            'price_adjustment_type' => 'required|in:fixed,percentage',
            'price_adjustment_value' => 'required|numeric',
            'apply_to' => 'required|in:price,sale_price,cost_price,all'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Verify store exists
            $store = DB::table('stores')
                ->where('id', $request->store_id)
                ->where('status', 'active')
                ->first();

            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Store not found or inactive'
                ], 404);
            }

            // Get existing variant store data
            $variantStores = DB::table('variant_stores')
                ->whereIn('variant_id', $request->variant_ids)
                ->where('store_id', $request->store_id)
                ->get()
                ->keyBy('variant_id');

            $updatedCount = 0;

            foreach ($request->variant_ids as $variantId) {
                $variantStore = $variantStores->get($variantId);
                
                if (!$variantStore) {
                    continue; // Skip if variant-store relationship doesn't exist
                }

                $updateData = ['updated_at' => now()];

                // Calculate new prices based on adjustment type
                if ($request->apply_to === 'price' || $request->apply_to === 'all') {
                    $newPrice = $this->calculateNewPrice(
                        $variantStore->price,
                        $request->price_adjustment_type,
                        $request->price_adjustment_value
                    );
                    $updateData['price'] = max(0, $newPrice);
                }

                if ($request->apply_to === 'sale_price' || $request->apply_to === 'all') {
                    if ($variantStore->sale_price) {
                        $newSalePrice = $this->calculateNewPrice(
                            $variantStore->sale_price,
                            $request->price_adjustment_type,
                            $request->price_adjustment_value
                        );
                        $updateData['sale_price'] = max(0, $newSalePrice);
                    }
                }

                if ($request->apply_to === 'cost_price' || $request->apply_to === 'all') {
                    if ($variantStore->cost_price) {
                        $newCostPrice = $this->calculateNewPrice(
                            $variantStore->cost_price,
                            $request->price_adjustment_type,
                            $request->price_adjustment_value
                        );
                        $updateData['cost_price'] = max(0, $newCostPrice);
                    }
                }

                if (count($updateData) > 1) { // More than just updated_at
                    DB::table('variant_stores')
                        ->where('variant_id', $variantId)
                        ->where('store_id', $request->store_id)
                        ->update($updateData);
                    
                    $updatedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$updatedCount} variant prices updated successfully",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating pricing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update user status
     */
    public function updateUserStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|string',
            'status' => 'required|in:active,inactive,suspended'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Verify users exist and are not deleted
            $existingUsers = DB::table('users')
                ->whereIn('id', $request->user_ids)
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();

            if (count($existingUsers) !== count($request->user_ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some users were not found or have been deleted'
                ], 404);
            }

            // Prevent updating admin users if current user is not admin
            $currentUserId = session('admin_user_id');
            $currentUser = DB::table('users')->where('id', $currentUserId)->first();

            if ($currentUser && $currentUser->role !== 'admin') {
                $adminUsers = DB::table('users')
                    ->whereIn('id', $request->user_ids)
                    ->where('role', 'admin')
                    ->exists();

                if ($adminUsers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot update admin users'
                    ], 403);
                }
            }

            // Update user status
            $updatedCount = DB::table('users')
                ->whereIn('id', $request->user_ids)
                ->whereNull('deleted_at')
                ->update([
                    'status' => $request->status,
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$updatedCount} users updated successfully",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete products (soft delete)
     */
    public function deleteProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update deleted_at timestamp
            $deletedCount = DB::table('products')
                ->whereIn('id', $request->product_ids)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} products deleted successfully",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update inventory stock
     */
    public function updateStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'variant_store_updates' => 'required|array',
            'variant_store_updates.*.variant_id' => 'required|string',
            'variant_store_updates.*.store_id' => 'required|string',
            'variant_store_updates.*.stock_quantity' => 'required|integer|min:0',
            'variant_store_updates.*.min_stock_level' => 'nullable|integer|min:0',
            'variant_store_updates.*.stock_status' => 'nullable|in:in_stock,out_of_stock,on_backorder'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $updatedCount = 0;

            foreach ($request->variant_store_updates as $update) {
                $updateData = [
                    'stock_quantity' => $update['stock_quantity'],
                    'updated_at' => now()
                ];

                if (isset($update['min_stock_level'])) {
                    $updateData['min_stock_level'] = $update['min_stock_level'];
                }

                if (isset($update['stock_status'])) {
                    $updateData['stock_status'] = $update['stock_status'];
                } else {
                    // Auto-determine stock status based on quantity
                    $minLevel = $update['min_stock_level'] ?? 0;
                    if ($update['stock_quantity'] <= 0) {
                        $updateData['stock_status'] = 'out_of_stock';
                    } elseif ($update['stock_quantity'] <= $minLevel) {
                        $updateData['stock_status'] = 'in_stock'; // or 'low_stock' if you have that status
                    } else {
                        $updateData['stock_status'] = 'in_stock';
                    }
                }

                $updated = DB::table('variant_stores')
                    ->where('variant_id', $update['variant_id'])
                    ->where('store_id', $update['store_id'])
                    ->update($updateData);

                if ($updated) {
                    $updatedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$updatedCount} inventory records updated successfully",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate new price based on adjustment type and value
     */
    private function calculateNewPrice($currentPrice, $adjustmentType, $adjustmentValue)
    {
        if ($adjustmentType === 'fixed') {
            return $currentPrice + $adjustmentValue;
        } elseif ($adjustmentType === 'percentage') {
            return $currentPrice + ($currentPrice * ($adjustmentValue / 100));
        }
        
        return $currentPrice;
    }
}