@extends('layouts.app')

@section('title', 'View Blog')

@php
    $pageTitle = 'View Blog: ' . Str::limit($blog->title, 30);
    $breadcrumbs = [
        ['title' => 'Blog', 'url' => route('admin.blogs.index')],
        ['title' => 'View']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back to List
    </a>
    <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="btn btn-warning">
        <i class="fa fa-pencil"></i> Edit
    </a>
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        {{-- Main Content --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    {{-- Title --}}
                    <h1 class="mb-3">{{ $blog->title }}</h1>

                    {{-- Meta Info --}}
                    <div class="d-flex flex-wrap align-items-center mb-4 text-muted">
                        <div class="me-4">
                            <i class="fa fa-user"></i>
                            @if($blog->creator)
                                {{ $blog->creator->first_name }} {{ $blog->creator->last_name }}
                            @else
                                Unknown Author
                            @endif
                        </div>
                        <div class="me-4">
                            <i class="fa fa-calendar"></i>
                            {{ $blog->created_at->format('d M Y H:i') }}
                        </div>
                        <div class="me-4">
                            <i class="fa fa-eye"></i>
                            {{ number_format($blog->view_count ?? 0) }} views
                        </div>
                        <div>
                            <span class="badge 
                                @if($blog->status === 'draft') bg-secondary
                                @elseif($blog->status === 'published') bg-success
                                @elseif($blog->status === 'archived') bg-dark
                                @endif">
                                {{ $blog->status_label }}
                            </span>
                        </div>
                    </div>

                    {{-- Categories & Tags --}}
                    <div class="mb-4">
                        @if($blog->categories->count() > 0)
                            <div class="mb-2">
                                <strong>Categories:</strong>
                                @foreach($blog->categories as $category)
                                    <span class="badge bg-info me-1">{{ $category->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if($blog->tags->count() > 0)
                            <div class="mb-2">
                                <strong>Tags:</strong>
                                @foreach($blog->tags as $tag)
                                    <span class="badge bg-secondary me-1">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Excerpt --}}
                    @if($blog->excerpt)
                        <div class="alert alert-light mb-4">
                            <h5>Excerpt</h5>
                            <p class="mb-0">{{ $blog->excerpt }}</p>
                        </div>
                    @endif

                    {{-- Content --}}
                    <div class="blog-content">
                        {{ ($blog->content)}}
                    </div>
                </div>
            </div>

            {{-- SEO Information --}}
            @if($blog->meta_title || $blog->meta_description || $blog->meta_keywords)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">SEO Information</h5>
                    </div>
                    <div class="card-body">
                        @if($blog->meta_title)
                            <div class="mb-3">
                                <strong>Meta Title:</strong>
                                <p class="mb-0">{{ $blog->meta_title }}</p>
                            </div>
                        @endif

                        @if($blog->meta_description)
                            <div class="mb-3">
                                <strong>Meta Description:</strong>
                                <p class="mb-0">{{ $blog->meta_description }}</p>
                            </div>
                        @endif

                        @if($blog->meta_keywords)
                            <div class="mb-3">
                                <strong>Meta Keywords:</strong>
                                <p class="mb-0">{{ $blog->meta_keywords }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Blog Status --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Blog Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <strong>Status:</strong><br>
                            <span class="badge 
                                @if($blog->status === 'draft') bg-secondary
                                @elseif($blog->status === 'published') bg-success
                                @elseif($blog->status === 'archived') bg-dark
                                @endif">
                                {{ $blog->status_label }}
                            </span>
                        </div>
                        <div class="col-6">
                            <strong>Views:</strong><br>
                            <span class="h4">{{ number_format($blog->view_count ?? 0) }}</span>
                        </div>
                    </div>

                    @if($blog->published_at)
                        <hr>
                        <div>
                            <strong>Published:</strong><br>
                            {{ $blog->published_at->format('d M Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Blog Details --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Blog Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Slug:</strong><br>
                        <code>{{ $blog->slug }}</code>
                    </div>

                    <div class="mb-3">
                        <strong>Created:</strong><br>
                        {{ $blog->created_at->format('d M Y H:i') }}<br>
                        @if($blog->creator)
                            <small class="text-muted">by {{ $blog->creator->first_name }} {{ $blog->creator->last_name }}</small>
                        @endif
                    </div>

                    @if($blog->updated_at != $blog->created_at)
                        <div class="mb-3">
                            <strong>Last Updated:</strong><br>
                            {{ $blog->updated_at->format('d M Y H:i') }}<br>
                            @if($blog->updater)
                                <small class="text-muted">by {{ $blog->updater->first_name }} {{ $blog->updater->last_name }}</small>
                            @endif
                        </div>
                    @endif

                    <div class="mb-3">
                        <strong>Content Length:</strong><br>
                        {{ number_format(strlen(strip_tags($blog->content))) }} characters<br>
                        <small class="text-muted">≈ {{ number_format(str_word_count(strip_tags($blog->content))) }} words</small>
                    </div>
                </div>
            </div>

            {{-- Categories --}}
            @if($blog->categories->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Categories</h5>
                    </div>
                    <div class="card-body">
                        @foreach($blog->categories as $category)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-info">{{ $category->name }}</span>
                                @if($category->parent)
                                    <small class="text-muted">{{ $category->parent->name }}</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Feature Images --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Feature Image </h5>
                </div>
                <div class="card-body">
                     {{-- Featured Image --}}
                        @if($blog->featured_image)
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $blog->featured_image) }}" 
                                    alt="{{ $blog->title }}" 
                                    class="img-fluid rounded">
                            </div>
                        @endif
                </div>
            </div>

            

            {{-- Tags --}}
            @if($blog->tags->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tags</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap">
                            @foreach($blog->tags as $tag)
                                <span class="badge bg-secondary me-2 mb-2">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Quick Actions --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="btn btn-warning">
                            <i class="fa fa-pencil"></i> Edit Blog
                        </a>
                        
                        @if($blog->status === 'draft')
                            <form action="{{ route('admin.blogs.update', $blog->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="published">
                                <input type="hidden" name="title" value="{{ $blog->title }}">
                                <input type="hidden" name="content" value="{{ $blog->content }}">
                                <button type="submit" class="btn btn-success ">
                                    <i class="fa fa-check"></i> Publish Now
                                </button>
                            </form>
                        @elseif($blog->status === 'published')
                            <form action="{{ route('admin.blogs.update', $blog->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="archived">
                                <input type="hidden" name="title" value="{{ $blog->title }}">
                                <input type="hidden" name="content" value="{{ $blog->content }}">
                                <button type="submit" class="btn btn-dark ">
                                    <i class="fa fa-archive"></i> Archive
                                </button>
                            </form>
                        @endif
<!-- 
                        <button type="button" class="btn btn-danger" onclick="deleteBlog({{ $blog->id }})">
                            <i class="fa fa-trash"></i> Delete Blog
                        </button> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this blog?</p>
                <div class="alert alert-warning">
                    <strong>{{ $blog->title }}</strong><br>
                    <small>This action cannot be undone.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.blog-content {
    font-size: 16px;
    line-height: 1.8;
    color: #333;
}

.blog-content h1,
.blog-content h2,
.blog-content h3,
.blog-content h4,
.blog-content h5,
.blog-content h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #2c3e50;
}

.blog-content p {
    margin-bottom: 1rem;
}

.blog-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1rem 0;
}

.blog-content blockquote {
    padding: 1rem 1.5rem;
    margin: 1rem 0;
    border-left: 4px solid #007bff;
    background-color: #f8f9fa;
    font-style: italic;
}

.blog-content ul,
.blog-content ol {
    padding-left: 2rem;
    margin-bottom: 1rem;
}

.blog-content li {
    margin-bottom: 0.5rem;
}

.blog-content code {
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-size: 0.9em;
}

.blog-content pre {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 5px;
    overflow-x: auto;
}
</style>
@endpush

@push('scripts')
<script>
function deleteBlog(id) {
    const form = document.getElementById('deleteForm');
    form.action = `/admin/blogs/${id}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endpush