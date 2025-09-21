@extends('layouts.app')

@section('title', 'Product Categories')

@php
    $pageTitle = 'Product Categories';
    $breadcrumbs = [
        ['title' => 'E-commerce'],
        ['title' => 'Product'],
        ['title' => 'Categories']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.product-categories.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Add Category
    </a>
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight ecommerce">

    {{-- Filter Form --}}
    <form id="filterForm" method="GET" action="{{ route('admin.product-categories.index') }}">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Filters</h5>
                <div class="row">
                    <div class="col-sm-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" id="search" name="search"
                               value="{{ request('search') }}"
                               placeholder="Category Name / Slug"
                               class="form-control">
                    </div>

                    <div class="col-sm-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">-- All Status --</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.product-categories.index') }}" class="btn btn-secondary">
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
                    Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} 
                    of {{ $categories->total() }} categories
                </div>
                <div>
                    <form method="GET" action="{{ route('admin.product-categories.index') }}">
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
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Parent</th>
                    <th>Level</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th class="text-end">Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>{!! str_repeat('&mdash; ', $category->level) !!} {{ $category->name }}</td>
                        <td>{{ $category->slug }}</td>
                        <td>{{ $category->parent_name ?? '-' }}</td>
                        <td>{{ $category->level }}</td>
                        <td>
                            <span class="badge {{ $category->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($category->status) }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($category->created_at)->format('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.product-categories.edit', $category->id) }}" class="btn btn-sm btn-warning"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                            <form action="{{ route('admin.product-categories.destroy', $category->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this category?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fa fa-search fa-2x mb-2"></i>
                            <div>No categories found.</div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{-- Laravel Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $categories->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
