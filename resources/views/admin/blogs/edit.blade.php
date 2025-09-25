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
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            {{-- Main Content --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5>Blog Content</h5></div>
                    <div class="card-body">
                        {{-- Title --}}
                        <div class="mb-3">
                            <label for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title"
                                   value="{{ old('title', $blog->title) }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Slug --}}
                        <div class="mb-3">
                            <label for="slug">Slug</label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                   id="slug" name="slug"
                                   value="{{ old('slug', $blog->slug) }}"
                                   placeholder="Auto-generated from title">
                            <small class="form-text text-muted">Leave empty to auto-generate from title</small>
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Content --}}
                        <div class="mb-3">
                            <label for="content">Content <span class="text-danger">*</span></label>
                            <div class="summernote">{!! old('content', $blog->content) !!}</div>
                            <textarea name="content" id="content" style="display: none;">{{ old('content', $blog->content) }}</textarea>
                            @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Excerpt --}}
                        <div class="mb-3">
                            <label for="excerpt">Excerpt</label>
                            <textarea class="form-control @error('excerpt') is-invalid @enderror"
                                      id="excerpt" name="excerpt" rows="3" maxlength="500"
                                      placeholder="Brief description">{{ old('excerpt', $blog->excerpt) }}</textarea>
                            @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- SEO Settings --}}
                <div class="card mt-4">
                    <div class="card-header"><h5>SEO Settings</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="meta_title">Meta Title</label>
                            <input type="text" class="form-control @error('meta_title') is-invalid @enderror"
                                   id="meta_title" name="meta_title" value="{{ old('meta_title', $blog->meta_title) }}" maxlength="255">
                            @error('meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="meta_description">Meta Description</label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                      id="meta_description" name="meta_description" rows="3" maxlength="500">{{ old('meta_description', $blog->meta_description) }}</textarea>
                            @error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="meta_keywords">Meta Keywords</label>
                            <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror"
                                   id="meta_keywords" name="meta_keywords"
                                   value="{{ old('meta_keywords', $blog->meta_keywords) }}"
                                   placeholder="keyword1, keyword2, keyword3">
                            @error('meta_keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Publish Settings --}}
                <div class="card">
                    <div class="card-header"><h5>Publish Settings</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $blog->status) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="published_at">Published Date</label>
                            <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror"
                                   id="published_at" name="published_at"
                                   value="{{ old('published_at', $blog->published_at ? $blog->published_at->format('Y-m-d\TH:i') : '') }}">
                            @error('published_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Blog</button>
                            <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary"><i class="fa fa-times"></i> Cancel</a>
                        </div>
                    </div>
                </div>

                {{-- Featured Image --}}
                <div class="card mt-4">
                    <div class="card-header"><h5>Featured Image</h5></div>
                    <div class="card-body">
                        {{-- Input --}}
                        <div class="mb-3">
                            <input type="file" 
                                class="form-control @error('featured_image') is-invalid @enderror" 
                                id="featured_image" 
                                name="featured_image" 
                                accept="image/*">
                            <small class="form-text text-muted">
                                Max size: 2MB. Formats: JPEG, PNG, JPG, GIF
                            </small>
                            @error('featured_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Old Image Preview --}}
                        @if($blog->featured_image)
                            <div class="mb-2">
                                <p class="mb-1">Current Image:</p>
                                <img src="{{ asset('storage/' . $blog->featured_image) }}" 
                                    alt="Featured Image" 
                                    class="img-fluid rounded">
                            </div>
                        @endif

                        {{-- New Image Preview --}}
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
                    <div class="card-header"><h5>Categories</h5></div>
                    <div class="card-body">
                        <select name="category_ids[]" class="form-control" multiple>
                            @php
                                $selectedCategories = old('category_ids', $blog->categories->pluck('id')->toArray());
                                function renderCategoryOptions($categories, $parentId = null, $prefix = '', $selected = []) {
                                    foreach ($categories->where('parent_id', $parentId) as $cat) {
                                        echo '<option value="'.$cat->id.'"'.(in_array($cat->id, $selected) ? ' selected' : '').'>'.$prefix.$cat->name.'</option>';
                                        renderCategoryOptions($categories, $cat->id, $prefix.'— ', $selected);
                                    }
                                }
                                renderCategoryOptions($categories, null, '', $selectedCategories);
                            @endphp
                        </select>
                        <small>Hold Ctrl/Cmd to select multiple categories</small>
                    </div>
                </div>

                {{-- Tags --}}
                <div class="card mt-4">
                    <div class="card-header"><h5>Tags</h5></div>
                    <div class="card-body">
                        <select name="tag_ids[]" class="form-control" multiple>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ $tag->selected ? 'selected' : '' }}>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                        <small>Hold Ctrl/Cmd to select multiple tags</small>
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
$(document).ready(function() {
    // Summernote init
    $('.summernote').summernote({
        height: 200,
        callbacks: {
            onChange: function(contents) {
                $('#content').val(contents);
            }
        }
    });

    // Copy content on submit
    $('form').on('submit', function() {
        $('#content').val($('.summernote').summernote('code'));
    });

    // Slug auto-generate
    const titleInput = $('#title'), slugInput = $('#slug');
    const originalSlug = slugInput.val();
    titleInput.on('input', function() {
        if (!slugInput.data('manual') && !originalSlug) {
            let slug = $(this).val().toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            slugInput.val(slug);
        }
    });
    slugInput.on('input', function() { $(this).data('manual', true); });
});
</script>
@endpush
