@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">{{ isset($page) ? 'Edit Page' : 'Create Page' }}</h1>

    <form action="{{ isset($page) ? route('admin.pages.update', $page->id) : route('admin.pages.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($page))
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-8">
                <!-- Title -->
                <div class="form-group mb-3">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $page->title ?? '') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="form-group mb-3">
                    <label for="slug">Slug</label>
                    <input type="text" name="slug" id="slug"
                           class="form-control @error('slug') is-invalid @enderror"
                           value="{{ old('slug', $page->slug ?? '') }}" required>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Content -->
                <div class="form-group mb-3">
                    <label for="content">Content</label>
                    <textarea name="content" id="content"
                              class="form-control summernote @error('content') is-invalid @enderror"
                              rows="10">{{ old('content', $page->content ?? '') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Excerpt -->
                <div class="form-group mb-3">
                    <label for="excerpt">Excerpt</label>
                    <textarea name="excerpt" id="excerpt"
                              class="form-control @error('excerpt') is-invalid @enderror"
                              rows="3">{{ old('excerpt', $page->excerpt ?? '') }}</textarea>
                    @error('excerpt')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- SEO -->
                <h4 class="mt-4">SEO Settings</h4>
                <div class="form-group mb-3">
                    <label for="seo_title">SEO Title</label>
                    <input type="text" name="seo_title" id="seo_title"
                           class="form-control @error('seo_title') is-invalid @enderror"
                           value="{{ old('seo_title', $page->seo_title ?? '') }}">
                    @error('seo_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label for="seo_description">SEO Description</label>
                    <textarea name="seo_description" id="seo_description"
                              class="form-control @error('seo_description') is-invalid @enderror"
                              rows="2">{{ old('seo_description', $page->seo_description ?? '') }}</textarea>
                    @error('seo_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label for="seo_keywords">SEO Keywords</label>
                    <input type="text" name="seo_keywords" id="seo_keywords"
                           class="form-control @error('seo_keywords') is-invalid @enderror"
                           value="{{ old('seo_keywords', $page->seo_keywords ?? '') }}">
                    @error('seo_keywords')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="col-md-4">
                <!-- Featured Image -->
                <div class="form-group mb-3">
                    <label for="featured_image">Featured Image</label>
                    <input type="file" name="featured_image" id="featured_image"
                           class="form-control @error('featured_image') is-invalid @enderror">
                    @if(isset($page) && $page->featured_image)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $page->featured_image) }}"
                                 alt="Featured Image" class="img-fluid">
                        </div>
                    @endif
                    @error('featured_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Template -->
                <div class="form-group mb-3">
                    <label for="template">Template</label>
                    <select name="template" id="template" class="form-control">
                        <option value="default" {{ old('template', $page->template ?? '') == 'default' ? 'selected' : '' }}>Default</option>
                        <option value="landing" {{ old('template', $page->template ?? '') == 'landing' ? 'selected' : '' }}>Landing</option>
                        <option value="contact" {{ old('template', $page->template ?? '') == 'contact' ? 'selected' : '' }}>Contact</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="form-group mb-3">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="draft" {{ old('status', $page->status ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $page->status ?? '') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="archived" {{ old('status', $page->status ?? '') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>

                <!-- Extra Options -->
                <div class="form-check mb-2">
                    <input type="checkbox" name="is_homepage" id="is_homepage" class="form-check-input"
                           value="1" {{ old('is_homepage', $page->is_homepage ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_homepage">Set as Homepage</label>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="show_in_menu" id="show_in_menu" class="form-check-input"
                           value="1" {{ old('show_in_menu', $page->show_in_menu ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_in_menu">Show in Menu</label>
                </div>

                <!-- Parent Page -->
                <div class="form-group mb-3">
                    <label for="parent_id">Parent Page</label>
                    <select name="parent_id" id="parent_id" class="form-control">
                        <option value="">-- None --</option>
                        @foreach($pages as $p)
                            <option value="{{ $p->id }}" {{ old('parent_id', $page->parent_id ?? '') == $p->id ? 'selected' : '' }}>
                                {{ $p->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit -->
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        {{ isset($page) ? 'Update Page' : 'Create Page' }}
                    </button>
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.summernote').summernote({
            height: 300
        });
    });
</script>
@endpush
