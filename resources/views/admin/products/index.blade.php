@extends('layouts.app')

@section('title', 'E-commerce Product List')

@push('styles')
    <link href="{{ asset('css/plugins/footable/footable.core.css') }}" rel="stylesheet">
    <style>
        .filter-section {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #fff;
        }
        
        .btn-reset {
            background: #6c757d;
            color: white;
            border: none;
        }
        
        .btn-reset:hover {
            background: #5a6268;
            color: white;
        }

        .table-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px 0;
        }

        .entries-info {
            color: #666;
            font-size: 14px;
        }

        .per-page-selector {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .loading-overlay {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 999;
        }

        .loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .table-container {
            position: relative;
        }
    </style>
@endpush

@php
    $pageTitle = 'E-commerce Product List';
    $breadcrumbs = [
        ['title' => 'E-commerce'],
        ['title' => 'Product List']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Add Product
    </a>
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight ecommerce">

    {{-- Filter Form --}}
    <form id="filterForm" method="GET" action="{{ route('admin.products.index') }}">
        <div class="filter-section">
            <h5 class="mb-3">Filters</h5>
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        <label class="col-form-label" for="search">Search</label>
                        <input type="text" id="search" name="search"
                               value="{{ request('search') }}"
                               placeholder="Product Name / SKU / Description"
                               class="form-control">
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="form-group">
                        <label class="col-form-label" for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">-- All Status --</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                            <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out Of Stock</option>
                        </select>
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="form-group">
                        <label class="col-form-label" for="type">Type</label>
                        <input type="text" id="type" name="type"
                               value="{{ request('type') }}"
                               placeholder="Product Type"
                               class="form-control">
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="form-group">
                        <label class="col-form-label" for="brand_id">Brand</label>
                        <select name="brand_id" id="brand_id" class="form-control">
                            <option value="">-- All Brands --</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <button type="button" id="btn-reset" class="btn btn-reset">
                        <i class="fa fa-refresh"></i> Reset Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Hidden field to maintain per_page value -->
        <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">
    </form>

    {{-- Table --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    {{-- Table Info & Per Page Selector --}}
                    <div class="table-info py-2 px-3" style="background-color: #f3f3f4;">
                        <div class="entries-info">
                            Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} 
                            of {{ $products->total() }} products
                        </div>
                        
                        <div class="per-page-selector">
                            <!-- <label for="per_page_select">Show:</label> -->
                             <span>Show:</span>
                            <select id="per_page_select" class="form-control form-control-sm" style="width: auto;">
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                            <span>entries</span>
                        </div>
                    </div>

                    <div class="table-container">
                        {{-- Loading Overlay --}}
                        <div class="loading-overlay" id="loadingOverlay">
                            <div class="loading-spinner">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                        </div>

                        <table class="footable table table-stripped toggle-arrow-tiny" data-page-size="20">
                            <thead>
                            <tr>
                                <th data-toggle="true">Product Name</th>
                                <th data-hide="phone">SKU</th>
                                <th data-hide="all">Short Description</th>
                                <th data-hide="phone">Brand</th>
                                <th data-hide="phone">Status</th>
                                <th data-hide="phone">Created By</th>
                                <th data-hide="phone">Created At</th>
                                <th class="text-right" data-sort-ignore="true">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->sku }}</td>
                                    <td>
                                        @if($product->short_description && strlen($product->short_description) > 50)
                                            {{ substr($product->short_description, 0, 50) }}...
                                        @else
                                            {{ $product->short_description ?? '-' }}
                                        @endif
                                    </td>
                                    <td>{{ $product->brand_name ?? '-' }}</td>
                                    <td>
                                        @if($product->status === 'draft')
                                            <span class="label label-secondary">Draft</span>
                                        @elseif($product->status === 'published')
                                            <span class="label label-success">Published</span>
                                        @elseif($product->status === 'archived')
                                            <span class="label label-dark">Archived</span>
                                        @elseif($product->status === 'out_of_stock')
                                            <span class="label label-danger">Out Of Stock</span>
                                        @endif
                                    </td>
                                    <td>{{ trim($product->creator_first_name . ' ' . $product->creator_last_name) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($product->created_at)->format('d M Y') }}</td>
                                    <td class="text-right">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.products.show', $product->id) }}" class="btn-white btn btn-xs">View</a>
                                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-white btn btn-xs">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="py-4">
                                            <i class="fa fa-search fa-2x text-muted mb-3"></i>
                                            <p class="text-muted">No products found.</p>
                                            @if(request()->hasAny(['search', 'status', 'type', 'brand_id']))
                                                <button type="button" class="btn btn-sm btn-primary" onclick="resetFilters()">
                                                    Clear Filters
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($products->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="pagination-info">
                                <small class="text-muted">
                                    Page {{ $products->currentPage() }} of {{ $products->lastPage() }}
                                </small>
                            </div>
                            
                            <div class="pagination-links">
                                {{ $products->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/plugins/footable/footable.all.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize footable
    $('.footable').footable();

    // Auto submit filter form when select changes
    $('#status, #brand_id').change(function() {
        showLoading();
        $('#filterForm').submit();
    });

    // Handle per page change
    $('#per_page_select').change(function() {
        var perPage = $(this).val();
        $('input[name="per_page"]').val(perPage);
        showLoading();
        $('#filterForm').submit();
    });

    // Debounce search input
    var searchTimeout;
    $('#search, #type').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            // Auto submit after 500ms of no typing
            // Uncomment this if you want auto search
            // showLoading();
            // $('#filterForm').submit();
        }, 500);
    });

    // Reset filters
    $('#btn-reset').on('click', function() {
        // Clear all form inputs
        $('#search').val('');
        $('#status').val('');
        $('#type').val('');
        $('#brand_id').val('');
        $('input[name="per_page"]').val('20');
        $('#per_page_select').val('20');
        
        // Submit form to reset
        showLoading();
        $('#filterForm').submit();
    });

    // Show loading overlay
    function showLoading() {
        $('#loadingOverlay').show();
    }

    // Hide loading overlay when page loads
    $(window).on('load', function() {
        $('#loadingOverlay').hide();
    });

    // Handle form submission
    $('#filterForm').on('submit', function() {
        showLoading();
    });
});

// Global function for reset filters (can be called from empty state)
function resetFilters() {
    $('#btn-reset').click();
}
</script>
@endpush