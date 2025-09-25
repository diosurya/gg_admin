{{-- resources/views/admin/recommended_products/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Recommended Products')

@php
    $pageTitle = 'Recommended Products';
    $breadcrumbs = [
        ['title' => 'Products'],
        ['title' => 'Recommended']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.settings.recommended-products.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Add Data
    </a>
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <form id="filterForm" method="GET" action="{{ route('admin.settings.recommended-products.index') }}">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Filters</h5>
                <div class="row">
                    <div class="col-sm-4">
                        <label for="section" class="form-label">Section</label>
                        <select name="section" id="section" class="form-control">
                            <option value="">-- All Sections --</option>
                            <option value="recommended" {{ request('section') === 'recommended' ? 'selected' : '' }}>Recommended</option>
                            <option value="best_seller" {{ request('section') === 'best_seller' ? 'selected' : '' }}>Best Seller</option>
                            <option value="new_products" {{ request('section') === 'new_products' ? 'selected' : '' }}>New Products</option>
                            <option value="special_offer" {{ request('section') === 'special_offer' ? 'selected' : '' }}>Special Offer</option>
                            <option value="penawaran-hari-ini" {{ request('section') === 'penawaran-hari-ini' ? 'selected' : '' }}>Penawaran Hari Ini</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                    <a href="{{ route('admin.settings.recommended-products.index') }}" class="btn btn-secondary"><i class="fa fa-refresh"></i> Reset</a>
                </div>
            </div>
        </div>
        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
    </form>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Section</th>
                        <th>Sort Order</th>
                        <th>Created At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recommendedProducts as $rec)
                    <tr>
                        <td>{{ $rec->product->name ?? '-' }}</td>
                        <td>{{ ucfirst(str_replace('_',' ',$rec->section)) }}</td>
                        <td>{{ $rec->sort_order }}</td>
                        <td>{{ \Carbon\Carbon::parse($rec->created_at)->format('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.settings.recommended-products.edit', $rec->id) }}" class="btn btn-sm btn-warning"><i class="fa fa-pencil"></i></a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('{{ route('admin.settings.recommended-products.destroy', $rec->id) }}')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fa fa-search fa-2x mb-2"></i>
                            <div>No recommended products found.</div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-3">
                {{ $recommendedProducts->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Delete Recommended Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this recommended product?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="fa fa-trash"></i> Delete
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function confirmDelete(url) {
    let form = document.getElementById('deleteForm');
    form.action = url;
    let modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>

@endsection
