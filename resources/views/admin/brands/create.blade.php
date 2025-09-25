@extends('layouts.app')

@section('title', 'Create Brand')

@php
    $pageTitle = 'Create Brand';
    $breadcrumbs = [
        ['title' => 'Brands', 'url' => route('admin.brands.index')],
        ['title' => 'Create']
    ];
@endphp

@push('page-actions')
    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back to List
    </a>
@endpush

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.brands.form', ['brand' => null, 'submitLabel' => 'Create Brand'])
    </form>
</div>
@endsection
