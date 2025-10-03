@extends('layouts.app')

@section('title', 'Product Details')

@php
    $pageTitle = 'Product Details: ' . $product->name;
    $breadcrumbs = [
        ['title' => 'E-commerce'],
        ['title' => 'Product List', 'url' => route('admin.products.index')],
        ['title' => 'Product Details']
    ];
@endphp

@push('page-actions')

@endpush

@push('styles')
<link href="{{ asset('css/plugins/sweetalert/sweetalert.css') }}" rel="stylesheet">
<style>
.product-detail-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.product-detail-card .card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 15px 20px;
    font-weight: 600;
}
.product-detail-card .card-body {
    padding: 20px;
}
.product-image {
    height: 200px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.product-gallery img {
    max-width: 80px;
    max-height: 80px;
    border-radius: 4px;
    margin: 5px;
    cursor: pointer;
    border: 2px solid transparent;
}
.product-gallery img:hover {
    border-color: #1ab394;
}
.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}
.status-draft { background: #ffc107; color: #fff; }
.status-published { background: #28a745; color: #fff; }
.status-archived { background: #6c757d; color: #fff; }
.category-tag {
    background: #e9ecef;
    color: #495057;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    margin: 2px;
    display: inline-block;
}
.category-tag.primary {
    background: #1ab394;
    color: white;
}
.variant-card {
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;
    background: #f8f9fa;
}
.attribute-badge {
    background: #17a2b8;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 10px;
    margin: 2px;
}
.store-pricing {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px;
    margin: 5px 0;
}
.price-tag {
    font-size: 16px;
    font-weight: 600;
    color: #28a745;
}
.sale-price {
    color: #dc3545;
}
.original-price {
    text-decoration: line-through;
    color: #6c757d;
    font-size: 14px;
}
.detail-row {
    border-bottom: 1px solid #f0f0f0;
    padding: 8px 0;
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    font-weight: 600;
    color: #495057;
    min-width: 150px;
    display: inline-block;
}
.detail-value {
    color: #6c757d;
}
.tag-item {
    background: #6f42c1;
    color: white;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 12px;
    margin: 3px;
    display: inline-block;
}
.seo-preview {
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 15px;
    background: #f8f9fa;
}
.seo-title {
    color: #1a0dab;
    font-size: 18px;
    text-decoration: none;
    font-weight: normal;
    line-height: 1.2;
}
.seo-url {
    color: #006621;
    font-size: 14px;
}
.seo-description {
    color: #545454;
    font-size: 13px;
    line-height: 1.4;
}
</style>
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    
    {{-- Product Header --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="product-detail-card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-0">{{ $product->name }}</h2>
                            <p class="text-muted mb-0">SKU: {{ $product->sku }}</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary">
                                <i class="fa fa-edit"></i> Edit Product
                            </a>
                            <button type="button" class="btn btn-danger" onclick="deleteProduct({{ $product->id }})">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Left Column - Basic Info & Images --}}
        <div class="col-lg-8">

        {{-- Store Availability --}}
        @if($productStores->count() > 0)
        <div class="product-detail-card">
            <div class="card-header">
                <i class="fa fa-store"></i> Store Availability
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($productStores as $store)
                    <div class="col-md-4 mb-3">
                        <div class="store-pricing">
                            <h6>{{ $store->store_name }}</h6>
                            
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
            
            {{-- Basic Information --}}
            <div class="product-detail-card">
                <div class="card-header">
                    <i class="fa fa-info-circle"></i> Basic Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                          @php
                                $imageUrl = $product->cover_image ? url($product->cover_image) : null;
                            @endphp

                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" 
                                    class="img-thumbnail" 
                                    alt="{{ $product->name }}" 
                                    title="{{ $featured->alt_text ?? $product->name }}">
                            @else
                                <div class="product-image d-flex align-items-center justify-content-center bg-light">
                                    <i class="fa fa-image fa-3x text-muted"></i>
                                </div>
                            @endif

                        </div>
                        <div class="col-md-9">
                            <div class="detail-row">
                                <span class="detail-label">Name:</span>
                                <span class="detail-value">{{ $product->name }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">SKU:</span>
                                <span class="detail-value">{{ $product->sku }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Brand:</span>
                                <span class="detail-value">{{ $product->brand_name ?? 'No Brand' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Type:</span>
                                <span class="detail-value">{{ $product->type ?? 'N/A' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Model:</span>
                                <span class="detail-value">{{ $product->model ?? 'N/A' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Barcode:</span>
                                <span class="detail-value">{{ $product->barcode ?? 'N/A' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Created By:</span>
                                <span class="detail-value">{{ $product->creator_first_name }} {{ $product->creator_last_name }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Created At:</span>
                                <span class="detail-value">{{ date('d M Y H:i', strtotime($product->created_at)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Description --}}
            @if($product->description)
            <div class="product-detail-card">
                <div class="card-header">
                    <i class="fa fa-file-text"></i> Product Description
                </div>
                <div class="card-body">
                    <div class="product-description">
                        {!! $product->description !!}
                    </div>
                </div>
            </div>
            @endif

           

            {{-- Tags --}}
            @if($tags->count() > 0)
            <div class="product-detail-card">
                <div class="card-header">
                    <i class="fa fa-tags"></i> Tags
                </div>
                <div class="card-body">
                    @foreach($tags as $tag)
                        <span class="tag-item">{{ $tag->name }}</span>
                    @endforeach
                </div>
            </div>
            @endif

           
            {{-- Product Variants --}}
            @if($variants->count() > 0)
            <div class="row">
                <div class="col-lg-12">
                    <div class="product-detail-card">
                        <div class="card-header">
                            <i class="fa fa-list"></i> Product Variants ({{ $variants->count() }})
                        </div>
                        <div class="card-body">
                            @foreach($variants as $variant)
                            <div class="variant-card">
                                <div class="row">
                                    <div class="col-md-3">
                                        @php
                                            $imageUrlVariant = $variant->cover_image ? url($variant->cover_image) : null;
                                        @endphp

                                         @if($imageUrl)
                                            <img src="{{ $imageUrlVariant }}" 
                                                class="img-thumbnail">
                                        @else
                                            <div class="product-image d-flex align-items-center justify-content-center bg-light">
                                                <i class="fa fa-image fa-3x text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <h5>{{ $variant->name ?? 'Variant #' . $loop->iteration }}</h5>
                                        <p class="mb-1"><strong>SKU:</strong> {{ $variant->sku }}</p>
                                        
                                        {{-- Variant Attributes --}}
                                        @if($variant->attributes && count($variant->attributes) > 0)
                                        <div class="mb-2">
                                            <small class="text-muted">Attributes:</small><br>
                                            @foreach($variant->attributes as $attr)
                                                <span class="attribute-badge">{{ $attr->name }}: {{ $attr->value }}</span>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <div class="detail-row">
                                            <span class="detail-label">Price:</span>
                                            <span class="detail-value price-tag">Rp {{ number_format($variant->price, 0, ',', '.') }}</span>
                                        </div>
                                        @if($variant->stock_quantity !== null)
                                        <div class="detail-row">
                                            <span class="detail-label">Stock:</span>
                                            <span class="detail-value">{{ $variant->stock_quantity }}</span>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        {{-- Store Pricing for this variant --}}
                                        @if($variant->stores && count($variant->stores) > 0)
                                            <small class="text-muted">Store Pricing:</small>
                                            @foreach($variant->stores as $store)
                                            <div class="store-pricing">
                                                <small><strong>{{ $store->store_name }}:</strong></small><br>
                                               <br>
                                                @if($store->stock_quantity !== null)
                                                <small>Stock: {{ $store->stock_quantity }}</small>
                                                @endif
                                            </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- Right Column - Pricing, Variants, etc --}}
        <div class="col-lg-4">

            {{-- Pricing Information --}}
            <div class="product-detail-card">
                <div class="card-header">
                    <i class="fa fa-globe" aria-hidden="true"></i> Public information
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <a href="javascript::void()" class="ml-2 badge status-{{ $product->status }}">{{ ucfirst($product->status) }}</a>
                    </div>
                     @if($product->is_featured)
                    <div class="detail-row">
                        <span class="detail-label">Feature:</span>
                        <span class="badge badge-warning ml-2">Featured</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Categories --}}
            @if($categories->count() > 0)
            <div class="product-detail-card">
                <div class="card-header">
                    <i class="fa fa-folder"></i> Categories
                </div>
                <div class="card-body">
                    @foreach($categories as $category)
                        <span class="category-tag {{ $category->is_primary ? 'primary' : '' }}">
                            {{ $category->name }}
                            @if($category->is_primary)
                                <small>(Primary)</small>
                            @endif
                        </span>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Pricing Information --}}
            <div class="product-detail-card">
                <div class="card-header">
                    <i class="fa fa-money"></i> Pricing
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <span class="detail-label">Regular Price:</span>
                        <span class="detail-value price-tag">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    </div>
                    @if($product->sale_price)
                    <div class="detail-row">
                        <span class="detail-label">Sale Price:</span>
                        <span class="detail-value sale-price">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($product->cost_price)
                    <div class="detail-row">
                        <span class="detail-label">Cost Price:</span>
                        <span class="detail-value">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</span>
                    </div>
                    @endif
                </div>
            </div>

             {{-- Product Images Gallery --}}
            @if($media->count() > 0)
            <div class="product-detail-card">
                <div class="card-header">
                    <i class="fa fa-images"></i> Product Images ({{ $media->count() }})
                </div>
                <div class="card-body">
                    <div class="product-gallery">
                        @foreach($media as $image)
                            @php
                                // pastikan ada base url penuh
                                $imageUrl = url($image->image_path);
                                $altText = $image->alt_text ?? $product->name;
                            @endphp

                            <img src="{{ $imageUrl }}"
                                alt="{{ $altText }}"
                                title="{{ $altText }}"
                                onclick="showImageModal('{{ $imageUrl }}', '{{ $altText }}')">
                        @endforeach
                    </div>
                </div>
            </div>
            @endif


            {{-- Stock & Settings --}}
            <div class="product-detail-card">
                <div class="card-header">
                    <i class="fa fa-cogs"></i> Settings
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <span class="detail-label">Track Stock:</span>
                        <span class="detail-value">
                            <span class="badge badge-{{ $product->track_stock ? 'success' : 'secondary' }}">
                                {{ $product->track_stock ? 'Yes' : 'No' }}
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Min Quantity:</span>
                        <span class="detail-value">{{ $product->minimum_quantity ?? 1 }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Sort Order:</span>
                        <span class="detail-value">{{ $product->sort_order ?? 0 }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Featured:</span>
                        <span class="detail-value">
                            <span class="badge badge-{{ $product->is_featured ? 'warning' : 'secondary' }}">
                                {{ $product->is_featured ? 'Yes' : 'No' }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Shipping Information --}}
            @if($product->weight || $product->length || $product->width || $product->height)
            <div class="product-detail-card">
                <div class="card-header">
                    <i class="fa fa-truck"></i> Shipping Info
                </div>
                <div class="card-body">
                    @if($product->weight)
                    <div class="detail-row">
                        <span class="detail-label">Weight:</span>
                        <span class="detail-value">{{ $product->weight }} kg</span>
                    </div>
                    @endif
                    @if($product->length || $product->width || $product->height)
                    <div class="detail-row">
                        <span class="detail-label">Dimensions:</span>
                        <span class="detail-value">
                            {{ $product->length ?? 0 }} × {{ $product->width ?? 0 }} × {{ $product->height ?? 0 }} cm
                        </span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>


    {{-- SEO Information --}}
    @if($seoData->count() > 0)
    <div class="row">
        <div class="col-lg-12">
            <div class="product-detail-card">
                <div class="card-header">
                    <i class="fa fa-search"></i> SEO Information
                </div>
                <div class="card-body">
                    @foreach($seoData as $storeId => $seo)
                    <div class="seo-preview mb-4">
                        <h6>Store ID: {{ $storeId }}</h6>
                        @if($seo->meta_title)
                            <div class="seo-title">{{ $seo->meta_title }}</div>
                        @endif
                        @if($seo->slug)
                            <div class="seo-url">{{ url('/') }}/{{ $seo->slug }}</div>
                        @endif
                        @if($seo->meta_description)
                            <div class="seo-description">{{ $seo->meta_description }}</div>
                        @endif
                        @if($seo->meta_keywords)
                            <small class="text-muted"><strong>Keywords:</strong> {{ $seo->meta_keywords }}</small>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- Image Modal --}}
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Product Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="" class="img-fluid">
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/plugins/sweetalert/sweetalert.min.js') }}"></script>

<script>
$(document).ready(function(){
    
});

// Show image in modal
function showImageModal(imageSrc, imageAlt) {
    $('#modalImage').attr('src', imageSrc).attr('alt', imageAlt);
    $('#imageModal').modal('show');
}

// Delete product function
function deleteProduct(productId) {
    swal({
        title: "Are you sure?",
        text: "This action will permanently delete the product and all its data!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dd6b55",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel"
    }, function(isConfirm) {
        if (isConfirm) {
            // Show loading
            swal({
                title: "Deleting...",
                text: "Please wait while we delete the product",
                type: "info",
                showConfirmButton: false,
                allowOutsideClick: false
            });
            
            // Perform delete
            $.ajax({
                url: "#",
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    swal({
                        title: "Deleted!",
                        text: response.message || "Product has been deleted successfully.",
                        type: "success",
                        confirmButtonText: "OK"
                    }, function() {
                        window.location.href = "{{ route('admin.products.index') }}";
                    });
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    swal({
                        title: "Error!",
                        text: response?.message || "Failed to delete product.",
                        type: "error",
                        confirmButtonText: "OK"
                    });
                }
            });
        }
    });
}
</script>
@endpush