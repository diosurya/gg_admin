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

class SliderController extends Controller
{
    /**
     * Get blogs for landing page with pagination
     */
    public function index(Request $request)
    {
        try {
             $datas = DB::table('sliders')
                ->where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->get();

            $formated = $datas->map(function ($data) {
                return [
                    'id' => $data->id,
                    'title' => $data->title,
                    'caption' => $data->caption,
                    'path' => $data->image,
                    'link' => $data->link,
                    'sort_order' => $data->sort_order,
                    'status' => $data->status,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Datas retrieved successfully',
                'data' => $formated
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve datas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
