@extends('layouts.app')

@section('title', 'Edit Blog')

@php
    $pageTitle = 'Edit Blog';
    $breadcrumbs = [
        ['title' => 'Blog', 'url' => route('admin.blogs.index')],
        ['title' => 'Edit']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back to List
    </a>
    <a href="{{ route('admin.blogs.show', $blog->id) }}" class="btn btn-info">
        <i class="fa fa-eye"></i> View
    </a>
@endpush

@push('styles')
<link href="{{ asset('css/plugins/summernote/summernote-bs4.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <form action="{{ route('admin.blogs.update', $blog->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            {{-- Main Content --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Blog Content</h5>
                    </div>
                    <div class="card-body">
                        {{-- Title --}}
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $blog->title) }}" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Slug --}}
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" 
                                   class="form-control @error('slug') is-invalid @enderror" 
                                   id="slug" 
                                   name="slug" 
                                   value="{{ old('slug', $blog->slug) }}"
                                   placeholder="Auto-generated from title">
                            <small class="form-text text-muted">Leave empty to auto-generate from title</small>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Content --}}
                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <div class="summernote" name="content">
                                {!! old('content', $blog->content) !!}
                            </div>
                            <textarea name="content" id="content" style="display: none;">{{ old('content', $blog->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Excerpt --}}
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                      id="excerpt" 
                                      name="excerpt" 
                                      rows="3" 
                                      maxlength="500"
                                      placeholder="Brief description of the blog post">{{ old('excerpt', $blog->excerpt) }}</textarea>
                            <small class="form-text text-muted">Leave empty to auto-generate from content (max 500 characters)</small>
                            @error('excerpt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- SEO Settings --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">SEO Settings</h5>
                    </div>
                    <div class="card-body">
                        {{-- Meta Title --}}
                        <div class="mb-3">
                            <label for="meta_title" class="form-label">Meta Title</label>
                            <input type="text" 
                                   class="form-control @error('meta_title') is-invalid @enderror" 
                                   id="meta_title" 
                                   name="meta_title" 
                                   value="{{ old('meta_title', $blog->meta_title) }}"
                                   maxlength="255">
                            @error('meta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Meta Description --}}
                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                      id="meta_description" 
                                      name="meta_description" 
                                      rows="3" 
                                      maxlength="500">{{ old('meta_description', $blog->meta_description) }}</textarea>
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Meta Keywords --}}
                        <div class="mb-3">
                            <label for="meta_keywords" class="form-label">Meta Keywords</label>
                            <input type="text" 
                                   class="form-control @error('meta_keywords') is-invalid @enderror" 
                                   id="meta_keywords" 
                                   name="meta_keywords" 
                                   value="{{ old('meta_keywords', $blog->meta_keywords) }}"
                                   placeholder="keyword1, keyword2, keyword3">
                            <small class="form-text text-muted">Separate keywords with commas</small>
                            @error('meta_keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Publish Settings --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Publish Settings</h5>
                    </div>
                    <div class="card-body">
                        {{-- Status --}}
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $blog->status) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Published At --}}
                        <div class="mb-3">
                            <label for="published_at" class="form-label">Published Date</label>
                            <input type="datetime-local" 
                                   class="form-control @error('published_at') is-invalid @enderror" 
                                   id="published_at" 
                                   name="published_at" 
                                   value="{{ old('published_at', $blog->published_at ? $blog->published_at->format('Y-m-d\TH:i') : '') }}">
                            <small class="form-text text-muted">Leave empty to use current date when published</small>
                            @error('published_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- View Count --}}
                        <div class="mb-3">
                            <label class="form-label">View Count</label>
                            <div class="form-control-plaintext">
                                <strong>{{ number_format($blog->view_count ?? 0) }}</strong> views
                            </div>
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Update Blog
                            </button>
                            <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Featured Image --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Featured Image</h5>
                    </div>
                    <div class="card-body">
                        {{-- Current Image --}}
                        @if($blog->featured_image)
                            <div class="mb-3" id="currentImage">
                                <label class="form-label">Current Image:</label>
                                <div class="position-relative">
                                    <img src="{{ asset('storage/' . $blog->featured_image) }}" 
                                         alt="{{ $blog->title }}" 
                                         class="img-fluid rounded">
                                    <button type="button" 
                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" 
                                            onclick="removeCurrentImage({{ $blog->id }})">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">{{ $blog->featured_image ? 'Replace Image:' : 'Upload Image:' }}</label>
                            <input type="file" 
                                   class="form-control @error('featured_image') is-invalid @enderror" 
                                   id="featured_image" 
                                   name="featured_image" 
                                   accept="image/*">
                            <small class="form-text text-muted">Max size: 2MB. Formats: JPEG, PNG, JPG, GIF</small>
                            @error('featured_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- New Image Preview --}}
                        <div id="imagePreview" style="display: none;">
                            <label class="form-label">New Image Preview:</label>
                            <img id="previewImg" src="" alt="Preview" class="img-fluid rounded">
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removePreview()">
                                <i class="fa fa-times"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Categories --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Categories</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <select name="category_ids[]" 
                                    class="form-control @error('category_ids') is-invalid @enderror" 
                                    multiple>
                                @php
                                    function renderCategoryOptions($categories, $parentId = null, $prefix = '', $selected = []) {
                                        foreach ($categories->where('parent_id', $parentId) as $cat) {
                                            echo '<option value="'.$cat->id.'"'.(in_array($cat->id, $selected) ? ' selected' : '').'>'
                                                .$prefix.$cat->name.'</option>';
                                            renderCategoryOptions($categories, $cat->id, $prefix.'— ', $selected);
                                        }
                                    }
                                @endphp
                                
                                @php
                                    $selectedCategories = old('category_ids', $blog->categories->pluck('id')->toArray());
                                    renderCategoryOptions($categories, null, '', $selectedCategories);
                                @endphp
                            </select>
                            <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple categories</small>
                            @error('category_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Tags --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tags</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <select name="tag_ids[]" 
                                    class="form-control @error('tag_ids') is-invalid @enderror" 
                                    multiple>
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" 
                                        {{ in_array($tag->id, old('tag_ids', $blog->tags->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $tag->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple tags</small>
                            @error('tag_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Blog Info --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Blog Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <strong>Created:</strong><br>
                                <small>{{ $blog->created_at->format('d M Y H:i') }}</small><br>
                                @if($blog->creator)
                                    <small>by {{ $blog->creator->first_name }} {{ $blog->creator->last_name }}</small>
                                @endif
                            </div>
                            <div class="col-6">
                                @if($blog->updated_at != $blog->created_at)
                                    <strong>Updated:</strong><br>
                                    <small>{{ $blog->updated_at->format('d M Y H:i') }}</small><br>
                                    @if($blog->updater)
                                        <small>by {{ $blog->updater->first_name }} {{ $blog->updater->last_name }}</small>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/plugins/summernote/summernote-bs4.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
     // Summernote init
    $('.summernote').summernote({
        height: 200,
        callbacks: {
            onChange: function(contents, $editable) {
                $('#content').val(contents);
            }
        }
    });
    
    // Auto-generate slug from title (only if slug is empty)
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    const originalSlug = slugInput.value;
    
    titleInput.addEventListener('input', function() {
        if (!slugInput.dataset.manual && !originalSlug) {
            const slug = this.value
                .toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            slugInput.value = slug;
        }
    });
    
    slugInput.addEventListener('input', function() {
        this.dataset.manual = 'true';
    });
    
    // Image preview for new uploads
    const imageInput = document.getElementById('featured_image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });
});

function removePreview() {
    document.getElementById('featured_image').value = '';
    document.getElementById('imagePreview').style.display = 'none';
}

function removeCurrentImage(blogId) {
    if (confirm('Are you sure you want to remove the current featured image?')) {
        fetch(`/admin/blogs/${blogId}/remove-image`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('currentImage').style.display = 'none';
                alert('Image removed successfully');
            } else {
                alert('Failed to remove image');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to remove image');
        });
    }
}
</script>
@endpush