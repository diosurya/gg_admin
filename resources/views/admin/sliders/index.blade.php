@extends('layouts.app')

@section('title', 'Slider List')

@php
    $pageTitle = 'Slider List';
    $breadcrumbs = [
        ['title' => 'Slider'],
        ['title' => 'List']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.settings.sliders.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Add Slider
    </a>
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight ecommerce">

    {{-- Filter Form --}}
    <form id="filterForm" method="GET" action="{{ route('admin.settings.sliders.index') }}">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Filters</h5>
                <div class="row">
                    <div class="col-sm-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" id="search" name="search"
                               value="{{ request('search') }}"
                               placeholder="Title / Caption"
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
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order"
                               value="{{ request('sort_order') }}"
                               placeholder="e.g. 1"
                               class="form-control">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.settings.sliders.index') }}" class="btn btn-secondary">
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
                    Showing {{ $sliders->firstItem() ?? 0 }} to {{ $sliders->lastItem() ?? 0 }} 
                    of {{ $sliders->total() }} sliders
                </div>
                <div>
                    <form method="GET" action="{{ route('admin.settings.sliders.index') }}">
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
                        <th>Image</th>
                        <th>Title</th>
                        <th>Caption</th>
                        <th>Link</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($sliders as $slider)
                    <tr>
                        <td>
                            @if($slider->image)
                                <img src="{{ asset('storage/'.$slider->image) }}" 
                                     alt="{{ $slider->title }}" 
                                     class="img-thumbnail" 
                                     style="width: 100px; height: auto;">
                            @else
                                <span class="text-muted">No Image</span>
                            @endif
                        </td>
                        <td>{{ $slider->title }}</td>
                        <td>{{ $slider->caption }}</td>
                        <td>
                            @if($slider->link)
                                <a href="{{ $slider->link_url }}" target="_blank">{{ $slider->link }}</a>
                            @else
                                <span class="text-muted">No Link</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge 
                                @if($slider->status === 'draft') bg-secondary
                                @elseif($slider->status === 'published') bg-success
                                @elseif($slider->status === 'archived') bg-dark
                                @endif">
                                {{ ucfirst($slider->status) }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($slider->created_at)->format('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.settings.sliders.edit', $slider->id) }}" class="btn btn-sm btn-warning"><i class="fa fa-pencil"></i></a>
                            <form action="{{ route('admin.settings.sliders.destroy', $slider->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this slider?')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa fa-search fa-2x mb-2"></i>
                            <div>No sliders found.</div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $sliders->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
