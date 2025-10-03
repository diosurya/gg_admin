@extends('layouts.app')

@section('title', 'Edit Category')

@php
    $pageTitle = 'Edit Category';
    $breadcrumbs = [
        ['title' => 'Product Categories', 'url' => route('admin.product-categories.index')],
        ['title' => 'Edit']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.product-categories.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back to List
    </a>
@endpush

@push('styles')
<link href="{{ asset('css/plugins/summernote/summernote-bs4.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.product-categories.update', $category->id) }}" 
          method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Main Content --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5>Edit Category Information</h5></div>
                    <div class="card-body">
                        {{-- Name --}}
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" 
                                   value="{{ old('name', $category->name) }}" required>
                        </div>

                        {{-- Slug --}}
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" 
                                   value="{{ old('slug', $category->slug) }}">
                            <small class="text-muted">Leave empty to auto-generate from name</small>
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <div class="summernote">{!! old('description', $category->description) !!}</div>
                            <textarea name="description" id="description" style="display:none;">
                                {{ old('description', $category->description) }}
                            </textarea>
                        </div>
                    </div>
                </div>

                {{-- Media Gallery --}}
                <div class="card mt-4">
                    <div class="card-header"><h5>Media Gallery</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="file" name="media[]" class="form-control" multiple>
                        </div>

                        {{-- Existing Media --}}
                        <div class="row g-2">
                            @foreach($media as $m)
                                <div class="col-6 col-md-4">
                                    <div class="position-relative">
                                        <img src="{{ asset('storage/'.$m->file_path) }}" class="img-fluid rounded">
                                        <div class="mt-1 text-center">
                                            <label>
                                                <input type="checkbox" name="remove_media[]" value="{{ $m->id }}"> Remove
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5>Publish Settings</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="active" {{ old('status',$category->status)=='active'?'selected':'' }}>Active</option>
                                <option value="inactive" {{ old('status',$category->status)=='inactive'?'selected':'' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Parent Category</label>
                            <select name="parent_id" class="form-control">
                                <option value="">— None —</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}" 
                                        {{ old('parent_id', $category->parent_id)==$parent->id?'selected':'' }}>
                                        {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" 
                                   value="{{ old('sort_order', $category->sort_order) }}">
                        </div>

                        <div class="form-check">
                            <input type="checkbox" name="is_featured" class="form-check-input"
                                   {{ old('is_featured',$category->is_featured)?'checked':'' }}>
                            <label class="form-check-label">Featured Category</label>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="show_in_menu" class="form-check-input"
                                   {{ old('show_in_menu',$category->show_in_menu)?'checked':'' }}>
                            <label class="form-check-label">Show in Menu</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
                            <a href="{{ route('admin.product-categories.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </div>

                {{-- Image --}}
                <div class="card mt-4">
                    <div class="card-header"><h5>Category Image</h5></div>
                    <div class="card-body">
                        <input type="file" name="image" class="form-control">
                        @if($category->image)
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.$category->image) }}" class="img-fluid rounded">
                                <label><input type="checkbox" name="remove_image" value="1"> Remove</label>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Banner --}}
                <div class="card mt-4">
                    <div class="card-header"><h5>Banner</h5></div>
                    <div class="card-body">
                        <input type="file" name="banner" class="form-control">
                        @if($category->banner)
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.$category->banner) }}" class="img-fluid rounded">
                                <label><input type="checkbox" name="remove_banner" value="1"> Remove</label>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Icon --}}
                <div class="card mt-4">
                    <div class="card-header"><h5>Icon</h5></div>
                    <div class="card-body">
                        <input type="file" name="icon" class="form-control">
                        @if($category->icon)
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.$category->icon) }}" class="img-fluid rounded" style="max-width:80px">
                                <label><input type="checkbox" name="remove_icon" value="1"> Remove</label>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
