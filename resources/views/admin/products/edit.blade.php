@extends('layouts.app')

@section('title', 'Edit Product')

@php
    $pageTitle = 'Edit Product: ' . $product->name;
    $breadcrumbs = [
        ['title' => 'E-commerce'],
        ['title' => 'Product List', 'url' => route('admin.products.index')],
        ['title' => 'Edit Product']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back to List
    </a>
    <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-info">
        <i class="fa fa-eye"></i> View Product
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
<style>
.image-preview {
    max-width: 60px;
    max-height: 60px;
    border-radius: 4px;
}
.variant-row {
    border: 1px solid #e5e6e7;
    border-radius: 4px;
    background-color: #f8f9fa;
}
.discount-row {
    border: 1px solid #e5e6e7;
    border-radius: 4px;
    background-color: #f8f9fa;
    padding: 15px;
    margin-bottom: 15px;
}
.form-help {
    font-size: 11px;
    color: #676a6c;
    margin-top: 5px;
}
.required-field:after {
    content: " *";
    color: red;
}
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 9999;
    display: none;
}
.loading-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    text-align: center;
}
.existing-image {
    position: relative;
    display: inline-block;
    margin: 5px;
}
.existing-image img {
    max-width: 80px;
    max-height: 80px;
    border-radius: 4px;
    border: 2px solid #ddd;
}
.existing-image .remove-image {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 10px;
    cursor: pointer;
}
.existing-image.featured img {
    border-color: #28a745;
}
</style>
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight ecommerce">

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="sk-spinner sk-spinner-rotating-plane"></div>
            <h3>Updating Product...</h3>
            <p>Please wait while we process your data</p>
        </div>
    </div>

    <form id="productForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        
        <div class="row">
            <div class="col-md-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs">
                        <li><a class="nav-link active" data-toggle="tab" href="#tab-basic"> Basic Info</a></li>
                        <li><a class="nav-link" data-toggle="tab" href="#tab-details"> Details</a></li>
                        <li><a class="nav-link" data-toggle="tab" href="#tab-variants"> Variants</a></li>
                        <li><a class="nav-link" data-toggle="tab" href="#tab-pricing"> Pricing & Discounts</a></li>
                        <li><a class="nav-link" data-toggle="tab" href="#tab-images"> Images</a></li>
                        <li><a class="nav-link" data-toggle="tab" href="#tab-categories"> Categories</a></li>
                        <li><a class="nav-link" data-toggle="tab" href="#tab-seo"> SEO & Meta</a></li>
                        <li><a class="nav-link" data-toggle="tab" href="#tab-shipping"> Shipping & Tax</a></li>
                    </ul>
                    
                    <div class="tab-content">
                        {{-- Basic Info Tab --}}
                        <div id="tab-basic" class="tab-pane active">
                            <div class="panel-body">
                                <fieldset>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label required-field">Name</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="name" class="form-control" 
                                                   placeholder="Product name" value="{{ old('name', $product->name) }}" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label required-field">SKU</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="sku" class="form-control" 
                                                   placeholder="Product SKU" value="{{ old('sku', $product->sku) }}" required>
                                            <div class="form-help">Unique product identifier</div>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Short Description</label>
                                        <div class="col-sm-10">
                                            <textarea name="short_description" class="form-control" rows="3" 
                                                      placeholder="Brief product description">{{ old('short_description', $product->short_description) }}</textarea>
                                            <div class="form-help">Brief description for listings (max 255 characters)</div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Description</label>
                                        <div class="col-sm-10">
                                            <div class="summernote" name="description">
                                                {{ old('description', $product->description ?? 'Enter detailed product description here...') }}
                                            </div>
                                        </div>
                                        <textarea name="description" id="description" style="display: none;">{{ old('description', $product->description) }}</textarea>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Brand</label>
                                        <div class="col-sm-10">
                                            <select name="brand_id" class="form-control select2">
                                                <option value="">Select Brand</option>
                                                @foreach($brands as $brand)
                                                    <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                                        {{ $brand->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label required-field">Status</label>
                                        <div class="col-sm-10">
                                            <select name="status" class="form-control" required>
                                                <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                                <option value="published" {{ old('status', $product->status) == 'published' ? 'selected' : '' }}>Published</option>
                                                <option value="archived" {{ old('status', $product->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                                            </select>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>

                        {{-- Details Tab --}}
                        <div id="tab-details" class="tab-pane">
                            <div class="panel-body">
                                <fieldset>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Type</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="type" class="form-control" 
                                                   placeholder="Product type (e.g., Electronics, Clothing)" value="{{ old('type', $product->type) }}">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Barcode</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="barcode" class="form-control" 
                                                   placeholder="Product barcode" value="{{ old('barcode', $product->barcode) }}">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Model</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="model" class="form-control" 
                                                   placeholder="Product model" value="{{ old('model', $product->model) }}">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Minimum Quantity</label>
                                        <div class="col-sm-10">
                                            <input type="number" name="minimum_quantity" class="form-control" 
                                                   placeholder="1" value="{{ old('minimum_quantity', $product->minimum_quantity ?? 1) }}" min="1">
                                            <div class="form-help">Minimum quantity required for purchase</div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Sort Order</label>
                                        <div class="col-sm-10">
                                            <input type="number" name="sort_order" class="form-control" 
                                                   placeholder="0" value="{{ old('sort_order', $product->sort_order ?? 0) }}">
                                            <div class="form-help">Used for ordering products in listings</div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Track Stock</label>
                                        <div class="col-sm-10">
                                            <input type="checkbox" name="track_stock" class="js-switch" value="1" 
                                                   {{ old('track_stock', $product->track_stock) ? 'checked' : '' }}>
                                            <div class="form-help">Enable stock tracking for this product</div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Featured</label>
                                        <div class="col-sm-10">
                                            <input type="checkbox" name="is_featured" class="js-switch" value="1" 
                                                   {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                                            <div class="form-help">Show product in featured section</div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>

                        {{-- Variants Tab --}}
                        <div id="tab-variants" class="tab-pane">
                            <div class="panel-body">
                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <button type="button" class="btn btn-primary" id="addVariant">
                                            <i class="fa fa-plus"></i> Add Variant
                                        </button>
                                    </div>
                                </div>
                                
                                <div id="variantContainer">
                                    {{-- Load existing variants --}}
                                    @foreach($variants as $index => $variant)
                                    <div class="variant-row border p-3 mb-3" id="variant-{{ $index + 1 }}" data-variant-id="{{ $variant->id }}">
                                        <div class="d-flex justify-content-between">
                                            <h5>{{ $variant->name ?? 'Variant #' . ($index + 1) }}</h5>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeVariant({{ $index + 1 }})">
                                                <i class="fa fa-trash"></i> Remove
                                            </button>
                                        </div>
                                        <div class="row">
                                            <input type="hidden" name="variants[{{ $index + 1 }}][id]" value="{{ $variant->id }}">
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label>Name</label>
                                                    <input type="text" name="variants[{{ $index + 1 }}][attribute_name]" class="form-control" 
                                                           placeholder="Variant name" value="{{ $variant->attribute_name }}">
                                                </div>
                                                <div class="form-group">
                                                    <label>Name</label>
                                                    <input type="text" name="variants[{{ $index + 1 }}][attribute_value]" class="form-control" 
                                                           placeholder="Variant value" value="{{ $variant->attribute_value }}">
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label>SKU</label>
                                                    <input type="text" name="variants[{{ $index + 1 }}][sku]" class="form-control" 
                                                           placeholder="Variant SKU" value="{{ $variant->sku }}">
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label>Price</label>
                                                    <div class="input-group">
                                                        <span class="input-group-prepend">
                                                            <span class="input-group-text">Rp</span>
                                                        </span>
                                                        <input type="number" name="variants[{{ $index + 1 }}][price]" class="form-control" 
                                                               placeholder="0.00" step="0.01" value="{{ $variant->price }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label>Stock (Optional)</label>
                                                    <input type="number" name="variants[{{ $index + 1 }}][stock_quantity]" class="form-control" 
                                                           placeholder="0" min="0" value="{{ $variant->stock_quantity }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Variant Attributes --}}
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <h6>Attributes</h6>
                                                <div class="row">
                                                    @foreach($variant->attributes as $attrIndex => $attribute)
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label>{{ $attribute->name }}</label>
                                                            <input type="hidden" name="variants[{{ $index + 1 }}][attributes][{{ $attrIndex }}][id]" value="{{ $attribute->id }}">
                                                            <input type="text" name="variants[{{ $index + 1 }}][attributes][{{ $attrIndex }}][name]" class="form-control" 
                                                                   placeholder="Attribute name" value="{{ $attribute->name }}">
                                                            <input type="text" name="variants[{{ $index + 1 }}][attributes][{{ $attrIndex }}][value]" class="form-control mt-1" 
                                                                   placeholder="Attribute value" value="{{ $attribute->value }}">
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Pricing & Discounts Tab --}}
                        <div id="tab-pricing" class="tab-pane">
                            <div class="panel-body">
                                <fieldset>
                                    <h4>Pricing</h4>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label required-field">Regular Price</label>
                                        <div class="col-sm-4">
                                            <div class="input-group">
                                                <span class="input-group-prepend">
                                                    <span class="input-group-text">Rp</span>
                                                </span>
                                                <input type="number" name="price" class="form-control" 
                                                       placeholder="0.00" step="0.01" value="{{ old('price', $product->price) }}" required>
                                            </div>
                                        </div>
                                        
                                        <label class="col-sm-2 col-form-label">Sale Price</label>
                                        <div class="col-sm-4">
                                            <div class="input-group">
                                                <span class="input-group-prepend">
                                                    <span class="input-group-text">Rp</span>
                                                </span>
                                                <input type="number" name="sale_price" class="form-control" 
                                                       placeholder="0.00" step="0.01" value="{{ old('sale_price', $product->sale_price) }}">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Cost Price</label>
                                        <div class="col-sm-4">
                                            <div class="input-group">
                                                <span class="input-group-prepend">
                                                    <span class="input-group-text">Rp</span>
                                                </span>
                                                <input type="number" name="cost_price" class="form-control" 
                                                       placeholder="0.00" step="0.01" value="{{ old('cost_price', $product->cost_price) }}">
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                
                                <hr>
                                
                                <fieldset>
                                    <h4>Bulk Discounts</h4>
                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <button type="button" class="btn btn-primary" id="addDiscount">
                                                <i class="fa fa-plus"></i> Add Discount Rule
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div id="discountContainer">
                                        {{-- Load existing discounts if any --}}
                                    </div>
                                </fieldset>
                            </div>
                        </div>

                        {{-- Images Tab --}}
                        <div id="tab-images" class="tab-pane">
                            <div class="panel-body">
                                {{-- Existing Images --}}
                                @if($media->count() > 0)
                                <div class="form-group">
                                    <label>Existing Images</label>
                                    <div id="existingImages" class="mb-3">
                                        @foreach($media as $image)
                                        <div class="existing-image {{ $image->is_featured ? 'featured' : '' }}" data-image-id="{{ $image->id }}">
                                            <img src="{{ asset($image->file_path) }}" alt="{{ $image->alt_text }}">
                                            <button type="button" class="remove-image" onclick="removeExistingImage({{ $image->id }})">
                                                <i class="fa fa-times"></i>
                                            </button>
                                            <input type="hidden" name="existing_images[{{ $image->id }}][keep]" value="1">
                                            <input type="hidden" name="existing_images[{{ $image->id }}][is_featured]" value="{{ $image->is_featured }}">
                                            <small class="d-block text-center mt-1">
                                                {{ $image->is_featured ? 'Featured' : 'Gallery' }}
                                                <br>
                                                <button type="button" class="btn btn-xs btn-link p-0" onclick="toggleFeatured({{ $image->id }})">
                                                    {{ $image->is_featured ? 'Remove Featured' : 'Set Featured' }}
                                                </button>
                                            </small>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                
                                <div class="form-group">
                                    <label>Add New Images</label>
                                    <div class="dropzone" id="productDropzone">
                                        <div class="dz-message">
                                            <h3>Drop files here or click to upload.</h3>
                                            <em>(Multiple files can be uploaded)</em>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered" id="imageTable" style="display: none;">
                                        <thead>
                                        <tr>
                                            <th>Image Preview</th>
                                            <th>Image Name</th>
                                            <th>Alt Text</th>
                                            <th>Sort Order</th>
                                            <th>Is Primary</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody id="imageTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Categories Tab --}}
                        <div id="tab-categories" class="tab-pane">
                            <div class="panel-body">
                                <div class="form-group">
                                    <label>Select Categories</label>
                                    <div class="form-help mb-2">Select multiple categories for this product</div>
                                    <div class="category-tree" id="categoryTree">
                                        <!-- Category tree will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SEO & Meta Tab --}}
                        <div id="tab-seo" class="tab-pane">
                            <div class="panel-body">
                                <div class="seo-section">
                                    <h4>SEO Settings</h4>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Meta Title</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="meta_title" class="form-control" 
                                                   placeholder="SEO meta title" value="{{ old('meta_title', $seoData->first()->meta_title ?? '') }}" maxlength="60">
                                            <div class="form-help">Recommended length: 50-60 characters</div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Meta Description</label>
                                        <div class="col-sm-10">
                                            <textarea name="meta_description" class="form-control" rows="3" 
                                                      placeholder="SEO meta description" maxlength="160">{{ old('meta_description', $seoData->first()->meta_description ?? '') }}</textarea>
                                            <div class="form-help">Recommended length: 150-160 characters</div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Meta Keywords</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="meta_keywords" class="form-control" 
                                                   placeholder="keyword1, keyword2, keyword3" value="{{ old('meta_keywords', $seoData->first()->meta_keywords ?? '') }}">
                                            <div class="form-help">Separate keywords with commas</div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">URL Slug</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="slug" class="form-control" 
                                                   placeholder="product-url-slug" value="{{ old('slug', $seoData->first()->slug ?? '') }}">
                                            <div class="form-help">Auto-generated from name if left empty</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Shipping & Tax Tab --}}
                        <div id="tab-shipping" class="tab-pane">
                            <div class="panel-body">
                                <fieldset>
                                    <h4>Shipping Information</h4>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Weight (kg)</label>
                                        <div class="col-sm-4">
                                            <input type="number" name="weight" class="form-control" 
                                                   placeholder="0.00" step="0.01" value="{{ old('weight', $product->weight) }}">
                                        </div>
                                        
                                        <label class="col-sm-2 col-form-label">Length (cm)</label>
                                        <div class="col-sm-4">
                                            <input type="number" name="length" class="form-control" 
                                                   placeholder="0.00" step="0.01" value="{{ old('length', $product->length) }}">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Width (cm)</label>
                                        <div class="col-sm-4">
                                            <input type="number" name="width" class="form-control" 
                                                   placeholder="0.00" step="0.01" value="{{ old('width', $product->width) }}">
                                        </div>
                                        
                                        <label class="col-sm-2 col-form-label">Height (cm)</label>
                                        <div class="col-sm-4">
                                            <input type="number" name="height" class="form-control" 
                                                   placeholder="0.00" step="0.01" value="{{ old('height', $product->height) }}">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Shipping Class</label>
                                        <div class="col-sm-10">
                                            <select name="shipping_class_id" class="form-control">
                                                <option value="">Select Shipping Class</option>
                                                <!-- Add shipping classes options -->
                                            </select>
                                        </div>
                                    </div>
                                </fieldset>
                                
                                <hr>
                                
                                <fieldset>
                                    <h4>Tax Information</h4>
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Tax Class</label>
                                        <div class="col-sm-10">
                                            <select name="tax_class_id" class="form-control">
                                                <option value="">Select Tax Class</option>
                                                <!-- Add tax classes options -->
                                            </select>
                                        </div>
                                    </div>
                                    
                                                                            <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Tax Status</label>
                                        <div class="col-sm-10">
                                            <select name="tax_status" class="form-control">
                                                <option value="taxable" {{ old('tax_status', $product->tax_status) == 'taxable' ? 'selected' : '' }}>Taxable</option>
                                                <option value="none" {{ old('tax_status', $product->tax_status) == 'none' ? 'selected' : '' }}>None</option>
                                            </select>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="row mt-3">
            <div class="col-lg-12">
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('admin.products.index') }}'">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="submit" name="action" value="draft" class="btn btn-warning">
                        <i class="fa fa-save"></i> Save as Draft
                    </button>
                    <button type="submit" name="action" value="publish" class="btn btn-primary">
                        <i class="fa fa-check"></i> Update & Publish
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/plugins/summernote/summernote-bs4.js') }}"></script>
<script src="{{ asset('js/plugins/datapicker/bootstrap-datepicker.js') }}"></script>
<script src="{{ asset('js/plugins/dropzone/dropzone.js') }}"></script>
<script src="{{ asset('js/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('js/plugins/jsTree/jstree.min.js') }}"></script>
<script src="{{ asset('js/plugins/switchery/switchery.js') }}"></script>
<script src="{{ asset('js/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('js/plugins/sweetalert/sweetalert.min.js') }}"></script>

<script>
Dropzone.autoDiscover = false;

$(document).ready(function(){
    let variantCount = {{ $variants->count() }};
    let discountCount = 0;
    let uploadedImages = [];
    let removedImages = [];

    // Initialize components
    initializeSummernote();
    initializeSelect2();
    initializeSwitchery();
    initializeDropzone();
    initializeCategoryTree();
    initializeDatePickers();

    // Configure toastr
    toastr.options = {
        closeButton: true,
        debug: false,
        newestOnTop: false,
        progressBar: true,
        positionClass: "toast-top-right",
        preventDuplicates: false,
        onclick: null,
        showDuration: "300",
        hideDuration: "1000",
        timeOut: "5000",
        extendedTimeOut: "1000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut"
    };

    // Summernote
    function initializeSummernote() {
        $('.summernote').summernote({
            height: 200,
            callbacks: {
                onChange: function(contents, $editable) {
                    $('#description').val(contents);
                }
            }
        });
    }

    // Select2
    function initializeSelect2() {
        $('.select2').select2({
            width: '100%'
        });
    }

    // Switchery
    function initializeSwitchery() {
        $('.js-switch').each(function() {
            new Switchery(this, {
                color: '#1AB394',
                size: 'small'
            });
        });
    }

    // Dropzone
    function initializeDropzone() {
        const dropzone = new Dropzone("#productDropzone", {
            url: "{{ route('admin.products.upload-image') }}",
            paramName: "file",
            maxFilesize: 5,
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(file, response) {
                console.log('Image uploaded:', response);
                uploadedImages.push({
                    id: response.id,
                    filename: response.filename,
                    original_name: response.original_name,
                    path: response.path
                });
                addImageToTable(response, uploadedImages.length);
                $('#imageTable').show();
            },
            error: function(file, errorMessage) {
                console.error('Upload error:', errorMessage);
                toastr.error('Failed to upload image: ' + (errorMessage.message || errorMessage));
            },
            removedfile: function(file) {
                const imageIndex = uploadedImages.findIndex(img => img.filename === file.upload?.filename);
                if (imageIndex > -1) {
                    uploadedImages.splice(imageIndex, 1);
                    $(`#image-row-${imageIndex}`).remove();
                }
                file.previewElement.remove();
            }
        });
    }

    // Category Tree
    function initializeCategoryTree() {
        const selectedCategories = {!! json_encode($categories->pluck('id')->toArray()) !!};
        
        $('#categoryTree').jstree({
            'core': {
                'data': {!! json_encode($categoryTree) !!}
            },
            'checkbox': {
                'keep_selected_style': false
            },
            'plugins': ['checkbox']
        }).on('ready.jstree', function () {
            // Select existing categories
            selectedCategories.forEach(function(categoryId) {
                $('#categoryTree').jstree('select_node', categoryId);
            });
        });
    }

    // Date Pickers
    function initializeDatePickers() {
        $('.input-group.date, .datepicker').datepicker({
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true,
            format: 'yyyy-mm-dd'
        });
    }

    // Add image to table
    function addImageToTable(image, index) {
        const row = `
            <tr id="image-row-${index}">
                <td>
                    <img src="${image.path}" class="image-preview">
                </td>
                <td>
                    <input type="hidden" name="new_images[${index}][id]" value="${image.id}">
                    <input type="hidden" name="new_images[${index}][path]" value="${image.path}">
                    <input type="text" name="new_images[${index}][name]" class="form-control" value="${image.original_name}">
                </td>
                <td>
                    <input type="text" name="new_images[${index}][alt_text]" class="form-control" placeholder="Alt text">
                </td>
                <td>
                    <input type="number" name="new_images[${index}][sort_order]" class="form-control" value="${index}" min="1">
                </td>
                <td>
                    <input type="radio" name="new_primary_image" value="${index}" ${index === 1 ? 'checked' : ''}>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeImageRow(${index})">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#imageTableBody').append(row);
    }

    // Add Variant
    $('#addVariant').click(function() {
        variantCount++;
        const variantHtml = `
            <div class="variant-row border p-3 mb-3" id="variant-${variantCount}">
                <div class="d-flex justify-content-between">
                    <h5>Variant #${variantCount}</h5>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeVariant(${variantCount})">
                        <i class="fa fa-trash"></i> Remove
                    </button>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="variants[${variantCount}][name]" class="form-control" placeholder="Variant name">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>SKU</label>
                            <input type="text" name="variants[${variantCount}][sku]" class="form-control" placeholder="Variant SKU">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Price</label>
                            <div class="input-group">
                                <span class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </span>
                                <input type="number" name="variants[${variantCount}][price]" class="form-control" placeholder="0.00" step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Stock (Optional)</label>
                            <input type="number" name="variants[${variantCount}][stock_quantity]" class="form-control" placeholder="0" min="0">
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#variantContainer').append(variantHtml);
    });

    // Add Discount
    $('#addDiscount').click(function() {
        discountCount++;
        const discountHtml = `
            <div class="discount-row" id="discount-${discountCount}">
                <div class="row">
                    <div class="col-sm-12">
                        <h5>Discount Rule #${discountCount} 
                            <button type="button" class="btn btn-sm btn-danger float-right" onclick="removeDiscount(${discountCount})">
                                <i class="fa fa-trash"></i> Remove
                            </button>
                        </h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Customer Group</label>
                            <select name="discounts[${discountCount}][customer_group_id]" class="form-control">
                                <option value="">All Customers</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Min Quantity</label>
                            <input type="number" name="discounts[${discountCount}][quantity]" class="form-control" placeholder="1" min="1">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Discount Type</label>
                            <select name="discounts[${discountCount}][type]" class="form-control">
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Discount Value</label>
                            <input type="number" name="discounts[${discountCount}][value]" class="form-control" placeholder="10" step="0.01">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>Start Date</label>
                            <div class="input-group date">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input type="text" name="discounts[${discountCount}][start_date]" class="form-control datepicker">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label>End Date</label>
                            <div class="input-group date">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input type="text" name="discounts[${discountCount}][end_date]" class="form-control datepicker">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#discountContainer').append(discountHtml);
        
        // Re-initialize datepickers for new elements
        $('.datepicker').datepicker({
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true,
            format: 'yyyy-mm-dd'
        });
    });

    // AJAX Form submission
    $('#productForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading overlay
        $('#loadingOverlay').show();
        
        // Clear previous validation errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Update description from summernote
        $('#description').val($('.summernote').summernote('code'));
        
        // Get form data
        const formData = new FormData();
        
        // Collect all form data
        const formFields = $(this).serializeArray();
        $.each(formFields, function(i, field) {
            formData.append(field.name, field.value);
        });
        
        // Get selected categories from jstree
        const selectedCategories = $('#categoryTree').jstree('get_selected');
        selectedCategories.forEach((categoryId, index) => {
            formData.append(`categories[${index}]`, categoryId);
        });
        
        // Add removed images
        removedImages.forEach((imageId, index) => {
            formData.append(`removed_images[${index}]`, imageId);
        });
        
        // Set status based on action button
        const action = $(document.activeElement).val();
        if (action === 'draft') {
            formData.set('status', 'draft');
        } else if (action === 'publish') {
            formData.set('status', 'published');
        }
        
        // Log all data being sent for debugging
        console.log('=== FORM DATA BEING SENT ===');
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }
        
        // Send AJAX request
        $.ajax({
            url: "{{ route('admin.products.update', $product->id) }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('=== SUCCESS RESPONSE ===');
                console.log(response);
                
                $('#loadingOverlay').hide();
                
                // Show success message
                swal({
                    title: "Success!",
                    text: response.message,
                    type: "success",
                    confirmButtonColor: "#1ab394",
                    confirmButtonText: "OK"
                }, function() {
                    if (response.data && response.data.redirect_url) {
                        window.location.href = response.data.redirect_url;
                    } else {
                        window.location.href = "{{ route('admin.products.show', $product->id) }}";
                    }
                });
                
                // Show success toastr as well
                toastr.success(response.message);
            },
            error: function(xhr) {
                console.log('=== ERROR RESPONSE ===');
                console.log(xhr.responseJSON);
                
                $('#loadingOverlay').hide();
                
                const response = xhr.responseJSON;
                let errorMessage = 'An error occurred while updating the product.';
                
                if (response && response.message) {
                    errorMessage = response.message;
                }
                
                // Show error popup
                swal({
                    title: "Error!",
                    text: errorMessage,
                    type: "error",
                    confirmButtonColor: "#dd6b55",
                    confirmButtonText: "OK"
                });
                
                // Show error toastr
                toastr.error(errorMessage);
                
                // Handle validation errors
                if (xhr.status === 422 && response.errors) {
                    console.log('=== VALIDATION ERRORS ===');
                    console.log(response.errors);
                    
                    $.each(response.errors, function(field, messages) {
                        const input = $(`[name="${field}"]`);
                        if (input.length) {
                            input.addClass('is-invalid');
                            input.siblings('.invalid-feedback').text(messages[0]);
                        }
                    });
                    
                    // Show validation summary
                    let errorList = '<ul>';
                    $.each(response.errors, function(field, messages) {
                        errorList += '<li>' + field + ': ' + messages[0] + '</li>';
                    });
                    errorList += '</ul>';
                    
                    swal({
                        title: "Validation Errors",
                        text: "Please check the following errors:",
                        type: "warning",
                        html: true,
                        confirmButtonText: "OK"
                    });
                }
            },
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        console.log('Upload progress: ' + Math.round(percentComplete * 100) + '%');
                    }
                }, false);
                return xhr;
            }
        });
    });

    // Auto-generate slug from name
    $('input[name="name"]').on('input', function() {
        const name = $(this).val();
        const slug = name.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
        $('input[name="slug"]').val(slug);
    });
});

// Global functions for removing elements
function removeVariant(id) {
    const variantRow = $(`#variant-${id}`);
    const variantId = variantRow.data('variant-id');
    
    if (variantId) {
        // Mark for deletion if it's an existing variant
        $('<input>').attr({
            type: 'hidden',
            name: 'deleted_variants[]',
            value: variantId
        }).appendTo('#productForm');
    }
    
    variantRow.remove();
}

function removeDiscount(id) {
    $(`#discount-${id}`).remove();
}

function removeImageRow(index) {
    $(`#image-row-${index}`).remove();
    const imageIndex = uploadedImages.findIndex((img, i) => i === index - 1);
    if (imageIndex > -1) {
        uploadedImages.splice(imageIndex, 1);
    }
}

function removeExistingImage(imageId) {
    // Add to removed images array
    removedImages.push(imageId);
    
    // Hide the image and mark it for removal
    const imageDiv = $(`.existing-image[data-image-id="${imageId}"]`);
    imageDiv.hide();
    imageDiv.find('input[name*="[keep]"]').val('0');
    
    toastr.info('Image marked for removal. Save the form to complete the removal.');
}

function toggleFeatured(imageId) {
    const imageDiv = $(`.existing-image[data-image-id="${imageId}"]`);
    const isFeatured = imageDiv.hasClass('featured');
    
    // Remove featured from all images
    $('.existing-image').removeClass('featured').find('img').css('border-color', '#ddd');
    $('.existing-image').find('input[name*="[is_featured]"]').val('0');
    
    if (!isFeatured) {
        // Set this image as featured
        imageDiv.addClass('featured').find('img').css('border-color', '#28a745');
        imageDiv.find('input[name*="[is_featured]"]').val('1');
        
        // Update button text
        imageDiv.find('button[onclick*="toggleFeatured"]').text('Remove Featured');
    }
    
    // Update all button texts
    $('.existing-image').each(function() {
        const button = $(this).find('button[onclick*="toggleFeatured"]');
        const isFeat = $(this).hasClass('featured');
        button.text(isFeat ? 'Remove Featured' : 'Set Featured');
    });
}
</script>
@endpush