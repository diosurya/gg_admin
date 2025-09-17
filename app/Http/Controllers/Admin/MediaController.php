<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Upload media files
     */
    public function upload(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|mimes:jpeg,png,jpg,gif,svg,mp4,pdf,doc,docx,mp3,wav|max:10240',
            'entity_type' => 'nullable|string',
            'entity_id' => 'nullable|string'
        ]);

        $uploadedFiles = [];
        
        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('media', $fileName, 'public');
            
            $mediaData = [
                'id' => Str::uuid(),
                'file_path' => $filePath,
                'file_name' => $fileName,
                'original_name' => $originalName,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'media_type' => $this->getMediaType($file->getMimeType()),
                'created_at' => now(),
                'updated_at' => now()
            ];

            // Insert based on entity type
            if ($request->entity_type && $request->entity_id) {
                switch ($request->entity_type) {
                    case 'product':
                        $mediaData['product_id'] = $request->entity_id;
                        $mediaData['variant_id'] = $request->variant_id ?? null;
                        DB::table('product_media')->insert($mediaData);
                        break;
                    case 'blog':
                        $mediaData['blog_id'] = $request->entity_id;
                        DB::table('blog_media')->insert($mediaData);
                        break;
                    case 'product_category':
                        $mediaData['category_id'] = $request->entity_id;
                        DB::table('product_category_media')->insert($mediaData);
                        break;
                    case 'blog_category':
                        $mediaData['category_id'] = $request->entity_id;
                        DB::table('blog_category_media')->insert($mediaData);
                        break;
                }
            }

            $uploadedFiles[] = $mediaData;
        }

        return response()->json([
            'success' => true,
            'message' => 'Files uploaded successfully',
            'data' => $uploadedFiles
        ]);
    }

    /**
     * Delete media file
     */
    public function destroy($id)
    {
        try {
            // Get media info from all possible tables
            $media = $this->findMediaById($id);
            
            if (!$media) {
                return response()->json([
                    'success' => false,
                    'message' => 'Media not found'
                ], 404);
            }

            // Delete physical file
            Storage::disk('public')->delete($media->file_path);

            // Delete from database
            $this->deleteMediaFromTable($id, $media->table_name);

            return response()->json([
                'success' => true,
                'message' => 'Media deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting media: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update media details
     */
    public function updateDetails($id, Request $request)
    {
        $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'caption' => 'nullable|string'
        ]);

        try {
            $media = $this->findMediaById($id);
            
            if (!$media) {
                return response()->json([
                    'success' => false,
                    'message' => 'Media not found'
                ], 404);
            }

            $updateData = [
                'alt_text' => $request->alt_text,
                'title' => $request->title,
                'description' => $request->description,
                'updated_at' => now()
            ];

            if ($media->table_name === 'blog_media') {
                $updateData['caption'] = $request->caption;
            }

            DB::table($media->table_name)
                ->where('id', $id)
                ->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Media details updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating media: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get media type based on mime type
     */
    private function getMediaType($mimeType)
    {
        if (str_contains($mimeType, 'image')) {
            return 'image';
        } elseif (str_contains($mimeType, 'video')) {
            return 'video';
        } elseif (str_contains($mimeType, 'audio')) {
            return 'audio';
        } else {
            return 'document';
        }
    }

    /**
     * Find media by ID across all media tables
     */
    private function findMediaById($id)
    {
        $tables = ['product_media', 'blog_media', 'product_category_media', 'blog_category_media'];
        
        foreach ($tables as $table) {
            $media = DB::table($table)->where('id', $id)->first();
            if ($media) {
                $media->table_name = $table;
                return $media;
            }
        }
        
        return null;
    }

    /**
     * Delete media from specific table
     */
    private function deleteMediaFromTable($id, $tableName)
    {
        DB::table($tableName)->where('id', $id)->delete();
    }
}