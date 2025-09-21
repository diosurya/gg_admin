@extends('layouts.app')

@section('title', 'Create Page')

@php
    $pageTitle = 'Create Page';
    $breadcrumbs = [
        ['title' => 'Pages', 'url' => route('admin.pages.index')],
        ['title' => 'Create']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back to List
    </a>
@endpush

@push('styles')
<link href="{{ asset('css/plugins/summernote/summernote-bs4.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <form action="{{ route('admin.pages.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            {{-- Main Content --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Page Content</h5></div>
                    <div class="card-body">
                        {{-- Title --}}
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" 
                                   value="{{ old('title') }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Slug --}}
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                   id="slug" name="slug" 
                                   value="{{ old('slug') }}">
                            <small class="form-text text-muted">Leave empty to auto-generate</small>
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Content --}}
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <div class="summernote">{!! old('content') !!}</div>
                            <textarea name="content" id="content" style="display:none;">{{ old('content') }}</textarea>
                            @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Excerpt --}}
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea name="excerpt" id="excerpt" class="form-control">{{ old('excerpt') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- SEO --}}
                <div class="card mt-4">
                    <div class="card-header"><h5 class="card-title mb-0">SEO Settings</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="seo_title" class="form-label">SEO Title</label>
                            <input type="text" class="form-control" id="seo_title" name="seo_title" value="{{ old('seo_title') }}">
                        </div>
                        <div class="mb-3">
                            <label for="seo_description" class="form-label">SEO Description</label>
                            <textarea class="form-control" id="seo_description" name="seo_description">{{ old('seo_description') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="seo_keywords" class="form-label">SEO Keywords</label>
                            <input type="text" class="form-control" id="seo_keywords" name="seo_keywords" value="{{ old('seo_keywords') }}">
                        </div>
                        <div class="mb-3">
                            <label for="seo_og_title" class="form-label">OG Title</label>
                            <input type="text" class="form-control" id="seo_og_title" name="seo_og_title" value="{{ old('seo_og_title') }}">
                        </div>
                        <div class="mb-3">
                            <label for="seo_og_description" class="form-label">OG Description</label>
                            <textarea class="form-control" id="seo_og_description" name="seo_og_description">{{ old('seo_og_description') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="seo_og_image" class="form-label">OG Image</label>
                            <input type="file" class="form-control" name="seo_og_image" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="seo_twitter_title" class="form-label">Twitter Title</label>
                            <input type="text" class="form-control" id="seo_twitter_title" name="seo_twitter_title" value="{{ old('seo_twitter_title') }}">
                        </div>
                        <div class="mb-3">
                            <label for="seo_twitter_description" class="form-label">Twitter Description</label>
                            <textarea class="form-control" id="seo_twitter_description" name="seo_twitter_description">{{ old('seo_twitter_description') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="seo_twitter_image" class="form-label">Twitter Image</label>
                            <input type="file" class="form-control" name="seo_twitter_image" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="seo_canonical_url" class="form-label">Canonical URL</label>
                            <input type="url" class="form-control" id="seo_canonical_url" name="seo_canonical_url" value="{{ old('seo_canonical_url') }}">
                        </div>
                        <div class="mb-3">
                            <label for="seo_robots" class="form-label">Robots</label>
                            <input type="text" class="form-control" id="seo_robots" name="seo_robots" value="{{ old('seo_robots','index,follow') }}">
                        </div>
                        <div class="mb-3">
                            <label for="seo_schema_markup" class="form-label">Schema Markup (JSON)</label>
                            <textarea class="form-control" id="seo_schema_markup" name="seo_schema_markup">{{ old('seo_schema_markup') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Publish --}}
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Publish Settings</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="draft" {{ old('status')==='draft'?'selected':'' }}>Draft</option>
                                <option value="published" {{ old('status')==='published'?'selected':'' }}>Published</option>
                                <option value="archived" {{ old('status')==='archived'?'selected':'' }}>Archived</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="published_at" class="form-label">Publish Date</label>
                            <input type="datetime-local" class="form-control" id="published_at" name="published_at" value="{{ old('published_at') }}">
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Page</label>
                            <select class="form-control" id="parent_id" name="parent_id">
                                <option value="">— None —</option>
                                @foreach($parentPages as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id')==$parent->id?'selected':'' }}>
                                        {{ $parent->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="template" class="form-label">Template</label>
                            <select class="form-control" id="template" name="template">
                                @foreach($templates as $template)
                                    <option value="{{ $template }}" {{ old('template')==$template?'selected':'' }}>
                                        {{ ucfirst($template) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order',0) }}">
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_homepage" name="is_homepage" value="1" {{ old('is_homepage')?'checked':'' }}>
                            <label class="form-check-label" for="is_homepage">Set as Homepage</label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="show_in_menu" name="show_in_menu" value="1" {{ old('show_in_menu',1)?'checked':'' }}>
                            <label class="form-check-label" for="show_in_menu">Show in Menu</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Create Page</button>
                            <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary"><i class="fa fa-times"></i> Cancel</a>
                        </div>
                    </div>
                </div>

                {{-- Featured Image --}}
                <div class="card mt-4">
                    <div class="card-header"><h5 class="card-title mb-0">Featured Image</h5></div>
                    <div class="card-body">
                        <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                        <div id="imagePreview" style="display:none;">
                            <img id="previewImg" src="" class="img-fluid rounded mt-2">
                            <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removePreview()">Remove</button>
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
    // Summernote
    $('.summernote').summernote({
        height: 200,
        callbacks: {
            onChange: function(contents) {
                $('#content').val(contents);
            }
        }
    });

    // Slug auto generate
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    titleInput.addEventListener('input', function() {
        if (!slugInput.dataset.manual) {
            slugInput.value = this.value.toLowerCase().replace(/[^\w\s-]/g, '').replace(/\s+/g, '-');
        }
    });
    slugInput.addEventListener('input', function(){ this.dataset.manual = true; });

    // Image Preview
    const imageInput = document.getElementById('featured_image');
    const previewImg = document.getElementById('previewImg');
    const previewBox = document.getElementById('imagePreview');
    imageInput.addEventListener('change', e=>{
        const file = e.target.files[0];
        if(file){
            const reader = new FileReader();
            reader.onload = e=>{
                previewImg.src = e.target.result;
                previewBox.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
});
function removePreview(){
    document.getElementById('featured_image').value='';
    document.getElementById('imagePreview').style.display='none';
}
</script>
@endpush
