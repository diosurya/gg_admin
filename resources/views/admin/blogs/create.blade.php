@extends('layouts.app')

@section('title', 'Create Blog')

@php
    $pageTitle = 'Create Blog';
    $breadcrumbs = [
        ['title' => 'Blog', 'url' => route('admin.blogs.index')],
        ['title' => 'Create']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back to List
    </a>
@endpush

@push('styles')
<link href="{{ asset('css/plugins/summernote/summernote-bs4.css') }}" rel="stylesheet">
<link href="{{ asset('css/plugins/dropzone/dropzone.css') }}" rel="stylesheet">
<link href="{{ asset('css/plugins/select2/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/plugins/jsTree/style.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/plugins/switchery/switchery.css') }}" rel="stylesheet">
<link href="{{ asset('css/plugins/toastr/toastr.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/plugins/sweetalert/sweetalert.css') }}" rel="stylesheet">
@endpush


@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
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
                                   value="{{ old('title') }}"
                                   placeholder="Title"
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
                                   value="{{ old('slug') }}"
                                   placeholder="Auto-generated from title">
                            <small class="form-text text-muted">Leave empty to auto-generate from title</small>
                            @error('slug')
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
                                      placeholder="Brief description of the blog post">{{ old('excerpt') }}</textarea>
                            <small class="form-text text-muted">Leave empty to auto-generate from content (max 500 characters)</small>
                            @error('excerpt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Content --}}
                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <div class="summernote" name="content">
                                   
                                </div>
                            <textarea name="content" id="content" style="display: none;">{{ old('content') }}</textarea>
                             @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-help"><small>description for listings (max 255 characters)</small></div>
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
                                   value="{{ old('meta_title') }}"
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
                                      maxlength="500">{{ old('meta_description') }}</textarea>
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
                                   value="{{ old('meta_keywords') }}"
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
                                    <option value="{{ $value }}" {{ old('status', 'draft') === $value ? 'selected' : '' }}>
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
                                   value="{{ old('published_at') }}">
                            <small class="form-text text-muted">Leave empty to use current date when published</small>
                            @error('published_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Create Blog
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
                        <div class="mb-3">
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
                        
                        {{-- Image Preview --}}
                        <div id="imagePreview" style="display: none;">
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
                                    $selectedCategories = old('category_ids', []);
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
                                        {{ in_array($tag->id, old('tag_ids', [])) ? 'selected' : '' }}>
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
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/plugins/summernote/summernote-bs4.js') }}"></script>
<script>



document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate slug from title
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');

    initializeSummernote();

        function initializeSummernote() {
            $('.summernote').summernote({
                height: 200,
                callbacks: {
                    onChange: function(contents, $editable) {
                        $('#content').val(contents);
                    }
                }
            });
        }
    
    titleInput.addEventListener('input', function() {
        if (!slugInput.dataset.manual) {
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
    
    // Image preview
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
        }
    });
});

function removePreview() {
    document.getElementById('featured_image').value = '';
    document.getElementById('imagePreview').style.display = 'none';
}

// Rich text editor for content (optional - can use any WYSIWYG editor)
// Example with TinyMCE (uncomment if you have TinyMCE loaded)
/*
tinymce.init({
    selector: '#content',
    height: 400,
    menubar: false,
    plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount'
    ],
    toolbar: 'undo redo | formatselect | bold italic backcolor | \
              alignleft aligncenter alignright alignjustify | \
              bullist numlist outdent indent | removeformat | help'
});
*/
</script>
@endpush