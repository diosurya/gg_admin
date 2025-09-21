@extends('layouts.app')

@section('title', 'Edit Recommended Product')

@php
    $pageTitle = 'Edit Recommended Product';
    $breadcrumbs = [
        ['title' => 'Recommended Products', 'url' => route('admin.recommended-products.index')],
        ['title' => 'Edit']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.settings.recommended-products.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back to List
    </a>
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <form action="{{ route('admin.settings.recommended-products.update', $recommendedProduct->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Alert Error --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recommended Product Settings</h5>
                    </div>
                    <div class="card-body">
                        {{-- Product --}}
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Select Product <span class="text-danger">*</span></label>
                            <select name="product_id" id="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id', $recommendedProduct->product_id) == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Section --}}
                        <div class="mb-3">
                            <label for="section" class="form-label">Section</label>
                            <select name="section" id="section" 
                                    class="form-control @error('section') is-invalid @enderror">
                                <option value="recommended" {{ old('section', $recommendedProduct->section) == 'recommended' ? 'selected' : '' }}>Recommended</option>
                                <option value="best-seller" {{ old('section', $recommendedProduct->section) == 'best-seller' ? 'selected' : '' }}>Best Seller</option>
                                <option value="new-products" {{ old('section', $recommendedProduct->section) == 'new-products' ? 'selected' : '' }}>New Products</option>
                                <option value="special-offer" {{ old('section', $recommendedProduct->section) == 'special-offer' ? 'selected' : '' }}>Special Offer</option>
                            </select>
                            @error('section')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        {{-- Sort Order --}}
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" 
                                   class="form-control @error('sort_order') is-invalid @enderror" 
                                   value="{{ old('sort_order', $recommendedProduct->sort_order) }}">
                            <small class="form-text text-muted">Smaller number = higher priority</small>
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="card mt-4">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update Recommended Product
                        </button>
                        <a href="{{ route('admin.recommended-products.index') }}" class="btn btn-secondary">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
