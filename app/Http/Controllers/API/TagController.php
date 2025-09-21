<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Carbon\Carbon;

class TagController extends Controller
{
    /**
     * Get blogs for landing page with pagination
     */
    public function index(Request $request)
    {
        try {
             $tags = DB::table('tags')
                ->where('type', 'blog')
                ->orderBy('created_at', 'desc')
                ->get();

            // Format data biar rapi
            $formattedTags = $tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'description' => $tag->description,
                    'color' => $tag->color,
                    'type' => $tag->type,
                    'usage_count' => $tag->usage_count,
                    'created_at' => $tag->created_at,
                    'updated_at' => $tag->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Tags retrieved successfully',
                'data' => $formattedTags
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve blogs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
