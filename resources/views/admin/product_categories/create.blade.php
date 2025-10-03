@extends('layouts.app')

@section('title', 'Create Category')

@php
    $pageTitle = 'Create Category';
    $breadcrumbs = [
        ['title' => 'Product Categories', 'url' => route('admin.product-categories.index')],
        ['title' => 'Create']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.product-categories.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back to List
    </a>
@endpush

@push('styles')
<link href="{{ asset('css/plugins/summernote/summernote-bs4.css') }}" rel="stylesheet">
<style>
    .color-preview {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        border: 1px solid #ddd;
        display: inline-block;
        vertical-align: middle;
        margin-left: 10px;
    }
</style>
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.product-categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            {{-- Main Content --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Category Information</h5></div>
                    <div class="card-body">
                        {{-- Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" 
                                   value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Slug --}}
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                   id="slug" name="slug" 
                                   value="{{ old('slug') }}">
                            <small class="form-text text-muted">Leave empty to auto-generate from name</small>
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <div class="summernote">{!! old('description') !!}</div>
                            <textarea name="description" id="description" style="display:none;">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Color --}}
                        <div class="mb-3">
                            <label for="color" class="form-label">Category Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="color" name="color" value="{{ old('color', '#3498db') }}" style="width: 60px;">
                                <input type="text" class="form-control" id="color_text" value="{{ old('color', '#3498db') }}" readonly>
                            </div>
                            <small class="form-text text-muted">Choose a color to represent this category</small>
                        </div>
                    </div>
                </div>

                {{-- Media Gallery --}}
                <div class="card mt-4">
                    <div class="card-header"><h5 class="card-title mb-0">Media Gallery</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="media" class="form-label">Upload Images</label>
                            <input type="file" class="form-control" id="media" name="media[]" accept="image/*" multiple>
                            <small class="form-text text-muted">You can select multiple images</small>
                        </div>
                        <div id="mediaPreview" class="row g-2"></div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Publish Settings --}}
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Publish Settings</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="active" {{ old('status')==='active'?'selected':'' }}>Active</option>
                                <option value="inactive" {{ old('status')==='inactive'?'selected':'' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Category</label>
                            <select class="form-control" id="parent_id" name="parent_id">
                                <option value="">— None (Root Category) —</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id')==$parent->id?'selected':'' }}>
                                        {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Select parent category for sub-category</small>
                        </div>

                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}">
                            <small class="form-text text-muted">Lower numbers appear first</small>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured')?'checked':'' }}>
                            <label class="form-check-label" for="is_featured">Featured Category</label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="show_in_menu" name="show_in_menu" value="1" {{ old('show_in_menu', 1)?'checked':'' }}>
                            <label class="form-check-label" for="show_in_menu">Show in Menu</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Create Category</button>
                            <a href="{{ route('admin.product-categories.index') }}" class="btn btn-secondary"><i class="fa fa-times"></i> Cancel</a>
                        </div>
                    </div>
                </div>

                {{-- Category Image --}}
                <div class="card mt-4">
                    <div class="card-header"><h5 class="card-title mb-0">Category Image</h5></div>
                    <div class="card-body">
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="form-text text-muted">Main category image</small>
                        <div id="imagePreview" style="display:none;" class="mt-2">
                            <img id="previewImg" src="" class="img-fluid rounded">
                            <button type="button" class="btn btn-danger btn-sm mt-2 w-100" onclick="removeImage('image')">Remove</button>
                        </div>
                    </div>
                </div>

                {{-- Banner --}}
                <div class="card mt-4">
                    <div class="card-header"><h5 class="card-title mb-0">Banner Image</h5></div>
                    <div class="card-body">
                        <input type="file" class="form-control" id="banner" name="banner" accept="image/*">
                        <small class="form-text text-muted">Banner for category page</small>
                        <div id="bannerPreview" style="display:none;" class="mt-2">
                            <img id="previewBanner" src="" class="img-fluid rounded">
                            <button type="button" class="btn btn-danger btn-sm mt-2 w-100" onclick="removeImage('banner')">Remove</button>
                        </div>
                    </div>
                </div>

                {{-- Icon --}}
                <div class="card mt-4">
                    <div class="card-header"><h5 class="card-title mb-0">Category Icon</h5></div>
                    <div class="card-body">
                        <input type="file" class="form-control" id="icon" name="icon" accept="image/*">
                        <small class="form-text text-muted">Small icon for menu/list</small>
                        <div id="iconPreview" style="display:none;" class="mt-2">
                            <img id="previewIcon" src="" class="img-fluid rounded" style="max-width: 100px;">
                            <button type="button" class="btn btn-danger btn-sm mt-2 w-100" onclick="removeImage('icon')">Remove</button>
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
                $('#description').val(contents);
            }
        }
    });

    // Slug auto generate
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    nameInput.addEventListener('input', function() {
        if (!slugInput.dataset.manual) {
            slugInput.value = this.value.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/--+/g, '-');
        }
    });
    slugInput.addEventListener('input', function(){ this.dataset.manual = true; });

    // Color picker sync
    const colorInput = document.getElementById('color');
    const colorText = document.getElementById('color_text');
    colorInput.addEventListener('input', function() {
        colorText.value = this.value;
    });

    // Single Image Preview Handler
    function setupImagePreview(inputId, previewId, imgId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const img = document.getElementById(imgId);
        
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    setupImagePreview('image', 'imagePreview', 'previewImg');
    setupImagePreview('banner', 'bannerPreview', 'previewBanner');
    setupImagePreview('icon', 'iconPreview', 'previewIcon');

    // Multiple Media Preview
    const mediaInput = document.getElementById('media');
    const mediaPreview = document.getElementById('mediaPreview');
    mediaInput.addEventListener('change', function(e) {
        mediaPreview.innerHTML = '';
        const files = Array.from(e.target.files);
        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-6 col-md-4';
                col.innerHTML = `
                    <div class="position-relative">
                        <img src="${e.target.result}" class="img-fluid rounded">
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                                onclick="removeMediaPreview(this, ${index})">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                `;
                mediaPreview.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    });
});

function removeImage(type) {
    document.getElementById(type).value = '';
    document.getElementById(type + 'Preview').style.display = 'none';
}

function removeMediaPreview(btn, index) {
    const mediaInput = document.getElementById('media');
    const dt = new DataTransfer();
    const files = Array.from(mediaInput.files);
    files.forEach((file, i) => {
        if (i !== index) dt.items.add(file);
    });
    mediaInput.files = dt.files;
    btn.closest('.col-6').remove();
}
</script>
@endpush