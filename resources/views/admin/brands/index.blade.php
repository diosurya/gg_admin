@extends('layouts.app')

@section('title', 'Brands List')

@php
    $pageTitle = 'Brands List';
    $breadcrumbs = [
        ['title' => 'Brands'],
        ['title' => 'List']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Add Brand
    </a>
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    
 {{-- Filter / Search --}}
    <form action="{{ route('admin.brands.index') }}" method="GET">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Filters</h5>
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2" placeholder="Search brand name...">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- Success message --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Table --}}
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    Showing {{ $brands->firstItem() ?? 0 }} to {{ $brands->lastItem() ?? 0 }}
                    of {{ $brands->total() }} brands
                </div>
                <div>
                    <form method="GET" action="{{ route('admin.brands.index') }}">
                        <input type="hidden" name="search" value="{{ request('search') }}">
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
                        <th>Logo</th>
                        <th>Banner</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $brand)
                    <tr>
                        <td>{{ $brand->name }}</td>
                        <td>
                            @if($brand->logo)
                                <img src="{{ asset('storage/'.$brand->logo) }}" width="80" alt="logo">
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($brand->banner)
                                <img src="{{ asset('storage/'.$brand->banner) }}" width="120" alt="banner">
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-warning btn-sm">
                                <i class="fa fa-pencil"></i>
                            </a>
                           <button type="button" class="btn btn-danger btn-sm"
                                 data-bs-toggle="modal"
                                    data-bs-target="#deleteModal"
                                    data-id="{{ $brand->id }}"
                                    data-name="{{ $brand->name }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="fa fa-search fa-2x mb-2"></i>
                            <div>No brands found.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $brands->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>


{{-- Modal hanya sekali, di luar loop --}}
<div class="modal fade"  id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Konfirmasi Hapus</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center">
            Apakah kamu yakin ingin menghapus <br>
            <strong id="productName"></strong>?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger">Hapus</button>
          </div>
        </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const deleteModal = document.getElementById('deleteModal')
    deleteModal.addEventListener('show.bs.modal', event => {
        console.log('Modal delete triggered') // cek apakah event masuk
        const button = event.relatedTarget
        const id = button.getAttribute('data-id')
        const name = button.getAttribute('data-name')

        document.getElementById('productName').textContent = name
        const form = document.getElementById('deleteForm')
        form.action = "{{ route('admin.brands.destroy', ':id') }}".replace(':id', id)
    })
})
</script>
@endsection
