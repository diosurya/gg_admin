@extends('layouts.app')

@section('title', 'Blog List')

@php
    $pageTitle = 'Blog List';
    $breadcrumbs = [
        ['title' => 'Blog'],
        ['title' => 'List']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Add Blogs
    </a>
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight ecommerce">

    {{-- Filter Form --}}
    <form id="filterForm" method="GET" action="{{ route('admin.blogs.index') }}">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Filters</h5>
                <div class="row">
                    <div class="col-sm-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" id="search" name="search"
                               value="{{ request('search') }}"
                               placeholder="Title / Slug"
                               class="form-control">
                    </div>

                    <div class="col-sm-4">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">-- All Status --</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>

                    <div class="col-sm-4">
                        <label for="category_ids" class="form-label">Categories</label>
                        <select name="category_ids[]" id="category_ids" class="form-control" multiple>
                            @php
                                // recursive tree builder
                                function renderCategoryOptions($categories, $parentId = null, $prefix = '', $selected = []) {
                                    foreach ($categories->where('parent_id', $parentId) as $cat) {
                                        echo '<option value="'.$cat->id.'"'.(in_array($cat->id, $selected) ? ' selected' : '').'>'
                                            .$prefix.$cat->name.'</option>';
                                        renderCategoryOptions($categories, $cat->id, $prefix.'— ', $selected);
                                    }
                                }
                            @endphp

                            @php
                                $selectedCategories = request()->input('category_ids', []);
                                renderCategoryOptions($categories, null, '', $selectedCategories);
                            @endphp
                        </select>
                    </div>

                   
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">
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
                    Showing {{ $blogs->firstItem() ?? 0 }} to {{ $blogs->lastItem() ?? 0 }} 
                    of {{ $blogs->total() }} blogs
                </div>
                <div>
                    <form method="GET" action="{{ route('admin.blogs.index') }}">
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
                        <th>Title</th>
                        <th>Category</th>
                        {{-- <th>Slug</th> --}}
                        <th>View Count</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($blogs as $blog)
                    <tr>
                        <td>{{ $blog->title }}</td>
                         <td>
                            @if(!empty($blog->category_names))
                                @foreach(explode(';', $blog->category_names) as $catName)
                                    <span class="badge bg-info">{{ $catName }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">No Category</span>
                            @endif
                        </td>
                        <td>{{ $blog->view_acount ?? 0 }}</td>
                        <td>
                            <span class="badge 
                                @if($blog->status === 'draft') bg-secondary
                                @elseif($blog->status === 'published') bg-success
                                @elseif($blog->status === 'archived') bg-dark
                                @elseif($blog->status === 'out_of_stock') bg-danger
                                @endif">
                                {{ ucfirst(str_replace('_',' ',$blog->status)) }}
                            </span>
                        </td>
                        <td>{{ trim($blog->creator_first_name.' '.$blog->creator_last_name) }}</td>
                        <td>{{ \Carbon\Carbon::parse($blog->created_at)->format('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.blogs.show', $blog->id) }}" class="btn btn-sm btn-info"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="btn btn-sm btn-warning"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                            <button type="button" class="btn btn-danger btn-sm"
                                 data-bs-toggle="modal"
                                    data-bs-target="#deleteModal"
                                    data-id="{{ $blog->id }}"
                                    data-name="{{ $blog->title }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa fa-search fa-2x mb-2"></i>
                            <div>No blogs found.</div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{-- Laravel Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $blogs->links('pagination::bootstrap-4') }}
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
        form.action = "{{ route('admin.blogs.destroy', ':id') }}".replace(':id', id)
    })
})
</script>
@endsection
