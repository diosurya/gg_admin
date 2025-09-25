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

class BlogController extends Controller
{
    /**
     * Get blogs for landing page with pagination
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('blogs as b')
                ->leftJoin('users as u', 'b.created_by', '=', 'u.id')
                ->select([
                    'b.id',
                    'b.title',
                    'b.slug',
                    'b.excerpt',
                    'b.content',
                    'b.status',
                    'b.published_at',
                    'b.reading_time',
                    'b.view_count',
                    'b.created_at',
                    'b.updated_at',
                    'u.first_name',
                    'u.last_name',
                    'b.featured_image as cover_image'
                ])
                ->whereNull('b.deleted_at')
                ->where('b.status', 'published');

            // Apply search filter by title only
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where('b.title', 'LIKE', "%{$searchTerm}%");
            }

            $perPage = $request->input('per_page', 12);
            
            $blogs = $query->orderBy('b.created_at', 'desc')
                          ->paginate($perPage);

            // Format the response data
            $formattedBlogs = $blogs->getCollection()->map(function ($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'slug' => $blog->slug,
                    'excerpt' => $blog->excerpt,
                    'content' => $blog->content,
                    'cover_image' => $blog->cover_image,
                    'status' => $blog->status,
                    'reading_time' => $blog->reading_time ?? 5,
                    'view_count' => $blog->view_count ?? 0,
                    'share_count' => 0,
                    'average_rating' => 0,
                    'category_name' => 'Uncategorized',
                    'author_name' => trim(($blog->first_name ?? '') . ' ' . ($blog->last_name ?? '')),
                    'published_at' => $blog->published_at,
                    'created_at' => $blog->created_at,
                    'updated_at' => $blog->updated_at,
                    'formatted_date' => Carbon::parse($blog->created_at)->format('d M Y'),
                    'human_date' => Carbon::parse($blog->created_at)->diffForHumans()
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Blogs retrieved successfully',
                'data' => $formattedBlogs,
                'meta' => [
                    'current_page' => $blogs->currentPage(),
                    'from' => $blogs->firstItem(),
                    'last_page' => $blogs->lastPage(),
                    'per_page' => $blogs->perPage(),
                    'to' => $blogs->lastItem(),
                    'total' => $blogs->total(),
                    'has_more_pages' => $blogs->hasMorePages()
                ],
                'links' => [
                    'first' => $blogs->url(1),
                    'last' => $blogs->url($blogs->lastPage()),
                    'prev' => $blogs->previousPageUrl(),
                    'next' => $blogs->nextPageUrl()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve blogs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single blog by slug
     */
     public function show($slug)
    {
        try {
             $blog = DB::table('blogs as b')
                ->leftJoin('users as u', 'b.created_by', '=', 'u.id')
                ->select([
                    'b.*',
                    'u.first_name',
                    'u.last_name',
                    'b.featured_image as cover_image'
                ])
                ->where('b.slug', $slug)
                ->first();

            if (!$blog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Blog not found'
                ], 404);
            }

             $relatedBlogs = DB::table('blogs as b')
                ->leftJoin('users as u', 'b.created_by', '=', 'u.id')
                ->select([
                    'b.*',
                    'u.first_name',
                    'u.last_name',
                    'b.featured_image as cover_image'
                ])
                ->where('b.id', '!=', $blog->id)
                ->where('b.status', 'published')
                ->whereNull('b.deleted_at')
                ->orderBy('b.created_at', 'desc')
                ->limit(4)
                ->get();

            
            $formattedBlog = [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'excerpt' => $blog->excerpt,
                'content' => $blog->content,
                'cover_image' => $blog->cover_image,
                'status' => $blog->status,
                'author_name' => trim(($blog->first_name ?? '') . ' ' . ($blog->last_name ?? '')),
                'meta' => [
                    'title' => $blog->meta_title ?: $blog->title,
                    'description' => $blog->meta_description ?: $blog->excerpt,
                    'keywords' => $blog->meta_keywords
                ],
                'published_at' => $blog->published_at,
                'created_at' => $blog->created_at,
                'updated_at' => $blog->updated_at,
                'formatted_date' => Carbon::parse($blog->created_at)->format('d M Y'),
                'human_date' => Carbon::parse($blog->created_at)->diffForHumans(),
                'related_blogs' => $relatedBlogs->map(function($related) {
                    return [
                        'id' => $related->id,
                        'title' => $related->title,
                        'slug' => $related->slug,
                        'excerpt' => $related->excerpt,
                        'cover_image' => $related->cover_image,
                        'author_name' => trim(($related->first_name ?? '') . ' ' . ($related->last_name ?? '')),
                        'created_at' => $related->created_at,
                        'formatted_date' => Carbon::parse($related->created_at)->format('d M Y')
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'message' => 'Blog retrieved successfully',
                'data' => $formattedBlog
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve blog',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get popular/featured blogs
     */
    public function popular(Request $request)
    {
        try {
            $limit = $request->input('limit', 5);

            $popularBlogs = DB::table('blogs as b')
                ->leftJoin('users as u', 'b.user_id', '=', 'u.id')
                ->leftJoin('blog_categories as bc', 'b.blog_category_id', '=', 'bc.id')
                ->leftJoin('blog_media as bm', 'b.id', '=', 'bm.blog_id')
                ->select([
                    'b.id',
                    'b.title',
                    'b.slug',
                    'b.excerpt',
                    'b.reading_time',
                    'b.view_count',
                    'b.created_at',
                    'bc.name as category_name',
                    'u.name as author_name',
                    'bm.media_path as cover_image'
                ])
                ->whereNull('b.deleted_at')
                ->where('b.status', 'published')
                ->orderBy('b.view_count', 'desc')
                ->orderBy('b.created_at', 'desc')
                ->limit($limit)
                ->get();

            $formattedBlogs = $popularBlogs->map(function ($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'slug' => $blog->slug,
                    'excerpt' => $blog->excerpt,
                    'cover_image' => $blog->cover_image,
                    'reading_time' => $blog->reading_time ?? 5,
                    'view_count' => $blog->view_count ?? 0,
                    'category_name' => $blog->category_name ?? 'Uncategorized',
                    'author_name' => $blog->author_name,
                    'created_at' => $blog->created_at,
                    'formatted_date' => Carbon::parse($blog->created_at)->format('d M Y'),
                    'human_date' => Carbon::parse($blog->created_at)->diffForHumans()
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Popular blogs retrieved successfully',
                'data' => $formattedBlogs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve popular blogs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get blog categories
     */
    public function categories()
    {
        try {
            $categories = DB::table('blog_categories as bc')
                ->leftJoin('blogs as b', function($join) {
                    $join->on('bc.id', '=', 'b.blog_category_id')
                         ->where('b.status', 'published')
                         ->whereNull('b.deleted_at');
                })
                ->select([
                    'bc.id',
                    'bc.name',
                    'bc.slug',
                    'bc.description',
                    DB::raw('COUNT(b.id) as blog_count')
                ])
                ->whereNull('bc.deleted_at')
                ->groupBy('bc.id', 'bc.name', 'bc.slug', 'bc.description')
                ->orderBy('bc.name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get blog tags
     */
    public function tags()
    {
        try {
            $tags = DB::table('tags as t')
                ->leftJoin('tag_blogs as tb', 't.id', '=', 'tb.tag_id')
                ->leftJoin('blogs as b', function($join) {
                    $join->on('tb.blog_id', '=', 'b.id')
                         ->where('b.status', 'published')
                         ->whereNull('b.deleted_at');
                })
                ->select([
                    't.id',
                    't.name',
                    't.slug',
                    DB::raw('COUNT(b.id) as blog_count')
                ])
                ->whereNull('t.deleted_at')
                ->groupBy('t.id', 't.name', 't.slug')
                ->having('blog_count', '>', 0)
                ->orderBy('blog_count', 'desc')
                ->orderBy('t.name')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Tags retrieved successfully',
                'data' => $tags
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tags',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function blogsByTag($slug) {
        try {
            // Cari tag berdasar slug
            $tag = DB::table('tags')
                ->where('slug', $slug)
                ->where('type', 'blog')
                ->first();

            if (!$tag) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tag not found'
                ], 404);
            }

            // Ambil blogs yang punya tag ini via pivot blog_tags
            $blogs = DB::table('blogs as b')
                ->join('blog_tags as bt', 'b.id', '=', 'bt.blog_id')
                ->where('bt.tag_id', $tag->id)
                ->where('b.status', 'published')
                ->orderByDesc('b.published_at')
                ->select(
                    'b.id',
                    'b.title',
                    'b.slug',
                    'b.excerpt',
                    'b.featured_image as cover_image',
                    'b.published_at',
                    'b.created_at',
                    'b.updated_at'
                )
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Blogs retrieved successfully',
                'tag' => $tag,
                'data' => $blogs
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
