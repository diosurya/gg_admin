@extends('layouts.app')

@section('title', 'Pages List')

@php
    $pageTitle = 'Pages List';
    $breadcrumbs = [
        ['title' => 'Pages'],
        ['title' => 'List']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Add Page
    </a>
@endpush

@push('styles')
<style>
    .page-hierarchy {
        padding-left: 0;
    }
    .page-hierarchy .depth-1 { padding-left: 20px; }
    .page-hierarchy .depth-2 { padding-left: 40px; }
    .page-hierarchy .depth-3 { padding-left: 60px; }
    .page-hierarchy .depth-4 { padding-left: 80px; }
</style>
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight ecommerce">

    {{-- Filter Form --}}
    <form id="filterForm" method="GET" action="{{ route('admin.pages.index') }}">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Filters</h5>
                <div class="row">
                    <div class="col-sm-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" id="search" name="search"
                               value="{{ request('search') }}"
                               placeholder="Title / Slug / Content"
                               class="form-control">
                    </div>

                    <div class="col-sm-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">-- All Status --</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>

                    <div class="col-sm-3">
                        <label for="template" class="form-label">Template</label>
                        <select name="template" id="template" class="form-control">
                            <option value="">-- All Templates --</option>
                            @foreach($templates as $key => $label)
                                <option value="{{ $key }}" {{ request('template') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-3">
                        <label for="parent_id" class="form-label">Parent Page</label>
                        <select name="parent_id" id="parent_id" class="form-control">
                            <option value="">-- All Pages --</option>
                            <option value="none" {{ request('parent_id') === 'none' ? 'selected' : '' }}>No Parent (Root Pages)</option>
                            @foreach($parentPages as $parent)
                                <option value="{{ $parent->id }}" {{ request('parent_id') === $parent->id ? 'selected' : '' }}>
                                    {{ $parent->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-3">
                        <label for="show_in_menu" class="form-label">Show in Menu</label>
                        <select name="show_in_menu" id="show_in_menu" class="form-control">
                            <option value="">-- All --</option>
                            <option value="1" {{ request('show_in_menu') === '1' ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ request('show_in_menu') === '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <div class="col-sm-3">
                        <label for="sort_by" class="form-label">Sort By</label>
                        <select name="sort_by" id="sort_by" class="form-control">
                            <option value="created_at" {{ request('sort_by', 'created_at') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                            <option value="title" {{ request('sort_by') === 'title' ? 'selected' : '' }}>Title</option>
                            <option value="sort_order" {{ request('sort_by') === 'sort_order' ? 'selected' : '' }}>Sort Order</option>
                        </select>
                    </div>

                    <div class="col-sm-3">
                        <label for="sort_direction" class="form-label">Sort Direction</label>
                        <select name="sort_direction" id="sort_direction" class="form-control">
                            <option value="desc" {{ request('sort_direction', 'desc') === 'desc' ? 'selected' : '' }}>Descending</option>
                            <option value="asc" {{ request('sort_direction') === 'asc' ? 'selected' : '' }}>Ascending</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                </div>
            </div>
        </div>

        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
    </form>

    {{-- Bulk Actions --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form id="bulkActionForm" method="POST" action="{{ route('admin.pages.bulk-action') }}">
                @csrf
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <input type="checkbox" id="selectAll" class="form-check-input me-2">
                            <label for="selectAll" class="form-check-label me-3">Select All</label>
                            
                            <select name="action" class="form-select form-select-sm me-2" style="width: auto;">
                                <option value="">-- Bulk Actions --</option>
                                <option value="publish">Publish</option>
                                <option value="unpublish">Unpublish</option>
                                <option value="archive">Archive</option>
                                <option value="delete">Delete</option>
                            </select>
                            
                            <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirm('Are you sure?')">
                                Apply
                            </button>
                        </div>
                    </div>
                    <div class="col-sm-6 text-end">
                        <span id="selectedCount" class="text-muted">0 selected</span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    Showing {{ $pages->firstItem() ?? 0 }} to {{ $pages->lastItem() ?? 0 }} 
                    of {{ $pages->total() }} pages
                </div>
                <div>
                    <form method="GET" action="{{ route('admin.pages.index') }}">
                        @foreach(request()->except('per_page') as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $subValue)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $subValue }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle page-hierarchy">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAllTable" class="form-check-input">
                            </th>
                            <th>Title</th>
                            <th>Slug</th>
                            <th width="100">Template</th>
                            <th width="80">Status</th>
                            <th width="80">Homepage</th>
                            <th width="80">In Menu</th>
                            <th width="80">Views</th>
                            <th width="120">Created By</th>
                            <th width="100">Created At</th>
                            <th width="150" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($pages as $page)
                        <tr class="depth-{{ $page->depth }}">
                            <td>
                                <input type="checkbox" name="page_ids[]" value="{{ $page->id }}" 
                                       class="form-check-input page-checkbox">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($page->depth > 0)
                                        <span class="text-muted me-1">└</span>
                                    @endif
                                    <div>
                                        <strong>{{ $page->title }}</strong>
                                        @if($page->is_homepage)
                                            <span class="badge bg-primary ms-1">Homepage</span>
                                        @endif
                                        @if($page->featured_image)
                                            <i class="fa fa-image text-success ms-1" title="Has featured image"></i>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="font-monospace text-muted">{{ $page->slug }}</span>
                                <a href="{{ $page->full_url }}" target="_blank" class="ms-1">
                                    <i class="fa fa-external-link-alt text-primary"></i>
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ ucfirst($page->template) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $page->status_badge }}">
                                    {{ ucfirst($page->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($page->is_homepage)
                                    <i class="fa fa-check text-success"></i>
                                @else
                                    <i class="fa fa-times text-muted"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($page->show_in_menu)
                                    <i class="fa fa-check text-success"></i>
                                @else
                                    <i class="fa fa-times text-muted"></i>
                                @endif
                            </td>
                            <td>{{ number_format($page->view_count) }}</td>
                            <td>{{ $page->creator_name }}</td>
                            <td>{{ $page->formatted_created_at }}</td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    
                                    <a href="{{ route('admin.pages.edit', $page->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" 
                                                data-bs-toggle="dropdown">
                                            <i class="fa fa-cog"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @if($page->status === 'draft')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.pages.publish', $page->id) }}"
                                                       onclick="event.preventDefault(); document.getElementById('publish-{{ $page->id }}').submit();">
                                                        <i class="fa fa-check text-success"></i> Publish
                                                    </a>
                                                    <form id="publish-{{ $page->id }}" action="{{ route('admin.pages.publish', $page->id) }}" 
                                                          method="POST" style="display: none;">
                                                        @csrf
                                                        @method('PATCH')
                                                    </form>
                                                </li>
                                            @endif
                                            
                                            @if($page->status === 'published')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.pages.unpublish', $page->id) }}"
                                                       onclick="event.preventDefault(); document.getElementById('unpublish-{{ $page->id }}').submit();">
                                                        <i class="fa fa-pause text-warning"></i> Unpublish
                                                    </a>
                                                    <form id="unpublish-{{ $page->id }}" action="{{ route('admin.pages.unpublish', $page->id) }}" 
                                                          method="POST" style="display: none;">
                                                        @csrf
                                                        @method('PATCH')
                                                    </form>
                                                </li>
                                            @endif
                                            
                                            @if($page->status !== 'archived')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.pages.archive', $page->id) }}"
                                                       onclick="event.preventDefault(); document.getElementById('archive-{{ $page->id }}').submit();">
                                                        <i class="fa fa-archive text-dark"></i> Archive
                                                    </a>
                                                    <form id="archive-{{ $page->id }}" action="{{ route('admin.pages.archive', $page->id) }}" 
                                                          method="POST" style="display: none;">
                                                        @csrf
                                                        @method('PATCH')
                                                    </form>
                                                </li>
                                            @endif
                                            
                                            <li><hr class="dropdown-divider"></li>
                                            
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.pages.duplicate', $page->id) }}"
                                                   onclick="event.preventDefault(); document.getElementById('duplicate-{{ $page->id }}').submit();">
                                                    <i class="fa fa-copy text-info"></i> Duplicate
                                                </a>
                                                <form id="duplicate-{{ $page->id }}" action="{{ route('admin.pages.duplicate', $page->id) }}" 
                                                      method="POST" style="display: none;">
                                                    @csrf
                                                </form>
                                            </li>
                                            
                                            <li><hr class="dropdown-divider"></li>
                                            
                                            <li>
                                                <a class="dropdown-item text-danger" href="#"
                                                   onclick="if(confirm('Are you sure you want to delete this page?')) { 
                                                       event.preventDefault(); 
                                                       document.getElementById('delete-{{ $page->id }}').submit(); 
                                                   }">
                                                    <i class="fa fa-trash"></i> Delete
                                                </a>
                                                <form id="delete-{{ $page->id }}" action="{{ route('admin.pages.destroy', $page->id) }}" 
                                                      method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </li>
                                        </ul>

                                    </div>

                                    <a class="btn btn-sm btn-danger" href="javascript:void(0)"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"
                                        data-id="{{ $page->id }}"
                                        data-title="{{ $page->title }}">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                <i class="fa fa-file-text fa-2x mb-2"></i>
                                <div>No pages found.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Laravel Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $pages->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

{{-- Modal Delete --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="deleteForm">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Page</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <strong id="pageTitle"></strong>?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const selectAllTable = document.getElementById('selectAllTable');
    const pageCheckboxes = document.querySelectorAll('.page-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const bulkActionForm = document.getElementById('bulkActionForm');

    function updateSelectedCount() {
        const checked = document.querySelectorAll('.page-checkbox:checked').length;
        selectedCount.textContent = `${checked} selected`;
        
        // Update select all checkboxes
        const allChecked = checked === pageCheckboxes.length && pageCheckboxes.length > 0;
        const someChecked = checked > 0;
        
        selectAll.checked = allChecked;
        selectAllTable.checked = allChecked;
        selectAll.indeterminate = someChecked && !allChecked;
        selectAllTable.indeterminate = someChecked && !allChecked;
    }

    function toggleAllCheckboxes(checked) {
        pageCheckboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
        updateSelectedCount();
    }

    // Select all handlers
    selectAll.addEventListener('change', (e) => {
        toggleAllCheckboxes(e.target.checked);
    });

    selectAllTable.addEventListener('change', (e) => {
        toggleAllCheckboxes(e.target.checked);
    });

    // Individual checkbox handlers
    pageCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Bulk action form handler
    bulkActionForm.addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('.page-checkbox:checked');
        const actionSelect = this.querySelector('select[name="action"]');
        
        if (checkedBoxes.length === 0) {
            e.preventDefault();
            alert('Please select at least one page.');
            return false;
        }
        
        if (!actionSelect.value) {
            e.preventDefault();
            alert('Please select an action.');
            return false;
        }

        // Add selected IDs to form
        checkedBoxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'page_ids[]';
            input.value = checkbox.value;
            this.appendChild(input);
        });
    });

    // Initial count update
    updateSelectedCount();
});

document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const title = button.getAttribute('data-title');

            document.getElementById('pageTitle').textContent = title;

            const form = document.getElementById('deleteForm');
            form.action = "{{ route('admin.pages.destroy', ':id') }}".replace(':id', id);
        });
    }
});
</script>
@endpush
@endsection