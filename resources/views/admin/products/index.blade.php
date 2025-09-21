@extends('layouts.app')

@section('title', 'E-commerce Product List')

@php
    $pageTitle = 'E-commerce Product List';
    $breadcrumbs = [
        ['title' => 'E-commerce'],
        ['title' => 'Product']
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
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Filters</h5>
                <div class="row">
                    <div class="col-sm-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" id="search" name="search"
                               value="{{ request('search') }}"
                               placeholder="Product Name / SKU / Description"
                               class="form-control">
                    </div>

                    <div class="col-sm-3">
                        <label for="store_id" class="form-label">Stores</label>
                        <select name="store_id" id="store_id" class="form-control">
                            <option value="">-- All Stores --</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">-- All Status --</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                            <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out Of Stock</option>
                        </select>
                    </div>

                    

                    <div class="col-sm-3">
                        <label for="brand_id" class="form-label">Brand</label>
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

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                </div>
            </div>
        </div>

        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
    </form>

    {{-- Table --}}
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} 
                    of {{ $products->total() }} products
                </div>
                <div>
                    <form method="GET" action="{{ route('admin.products.index') }}">
                        <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </form>
                </div>
            </div>

            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>Store</th>
                    <th>Product Name</th>
                    <th>SKU</th>
                    <th>Brand</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Created At</th>
                    <th class="text-end">Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>{{ $product->store_name ?? '-' }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->brand_name ?? '-' }}</td>
                        <td>
                            <span class="badge 
                                @if($product->status === 'draft') bg-secondary
                                @elseif($product->status === 'published') bg-success
                                @elseif($product->status === 'archived') bg-dark
                                @elseif($product->status === 'out_of_stock') bg-danger
                                @endif">
                                {{ ucfirst(str_replace('_',' ',$product->status)) }}
                            </span>
                        </td>
                        <td>{{ trim($product->creator_first_name.' '.$product->creator_last_name) }}</td>
                        <td>{{ \Carbon\Carbon::parse($product->created_at)->format('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-info"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-warning"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa fa-search fa-2x mb-2"></i>
                            <div>No products found.</div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{-- Laravel Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $products->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
