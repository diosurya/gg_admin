@extends('layouts.app')

@section('title', 'Edit Brand')

@php
    $pageTitle = 'Edit Brand';
    $breadcrumbs = [
        ['title' => 'Brands', 'url' => route('admin.brands.index')],
        ['title' => 'Edit']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back to List
    </a>
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.brands.form', ['brand' => $brand, 'submitLabel' => 'Update Brand'])
    </form>
</div>
@endsection
