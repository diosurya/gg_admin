@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $pageTitle = 'Admin Dashboard';
    $breadcrumbs = [
        ['title' => 'Dashboard']
    ];
    $adminUser = session('admin_user');
@endphp

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            Welcome back, <strong>{{ $adminUser->first_name ?? 'Admin' }}</strong>! 
            Last login: {{ $adminUser->last_login_at ? \Carbon\Carbon::parse($adminUser->last_login_at)->format('M d, Y - H:i') : 'First time login' }}
        </div>
    </div>
</div>

{{-- Statistics Cards --}}
<div class="row">
    <div class="col-lg-3">
        <div class="ibox">
            <div class="ibox-title">
                <span class="label label-success float-right">{{ $stats['users_count'] }}</span>
                <h5>Users</h5>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins">{{ number_format($stats['users_count']) }}</h1>
                <div class="stat-percent font-bold text-success">
                    <i class="fa fa-users"></i> Total Users
                </div>
                <small>Registered users in system</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3">
        <div class="ibox">
            <div class="ibox-title">
                <span class="label label-info float-right">{{ $stats['products_count'] }}</span>
                <h5>Products</h5>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins">{{ number_format($stats['products_count']) }}</h1>
                <div class="stat-percent font-bold text-info">
                    <i class="fa fa-shopping-cart"></i> Total Products
                </div>
                <small>Available products</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3">
        <div class="ibox">
            <div class="ibox-title">
                <span class="label label-primary float-right">{{ $stats['stores_count'] }}</span>
                <h5>Stores</h5>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins">{{ number_format($stats['stores_count']) }}</h1>
                <div class="stat-percent font-bold text-primary">
                    <i class="fa fa-store"></i> Active Stores
                </div>
                <small>Registered stores</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3">
        <div class="ibox">
            <div class="ibox-title">
                <span class="label label-warning float-right">{{ $stats['blogs_count'] }}</span>
                <h5>Blog Posts</h5>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins">{{ number_format($stats['blogs_count']) }}</h1>
                <div class="stat-percent font-bold text-warning">
                    <i class="fa fa-edit"></i> Blog Posts
                </div>
                <small>Published content</small>
            </div>
        </div>
    </div>
</div>

{{-- Recent Activities --}}
<div class="row">
    <div class="col-lg-6">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Recent Products</h5>
                <div class="ibox-tools">
                    <a href="{{ route('admin.products.index') }}">
                        <i class="fa fa-eye"></i> View All
                    </a>
                </div>
            </div>
            <div class="ibox-content">
                @forelse($recentProducts as $product)
                <div class="feed-activity-list">
                    <div class="feed-element">
                        <div class="media-body">
                            <small class="float-right">{{ \Carbon\Carbon::parse($product->created_at)->diffForHumans() }}</small>
                            <strong>{{ $product->name }}</strong><br>
                            <small class="text-muted">
                                Status: 
                                <span class="label label-{{ $product->status === 'active' ? 'primary' : 'warning' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </small>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted">No recent products</p>
                @endforelse
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Recent Blog Posts</h5>
                <div class="ibox-tools">
                    <a href="{{ route('admin.blogs.index') }}">
                        <i class="fa fa-eye"></i> View All
                    </a>
                </div>
            </div>
            <div class="ibox-content">
                @forelse($recentBlogs as $blog)
                <div class="feed-activity-list">
                    <div class="feed-element">
                        <div class="media-body">
                            <small class="float-right">{{ \Carbon\Carbon::parse($blog->created_at)->diffForHumans() }}</small>
                            <strong>{{ Str::limit($blog->title, 40) }}</strong><br>
                            <small class="text-muted">
                                Status: 
                                <span class="label label-{{ $blog->status === 'published' ? 'primary' : 'warning' }}">
                                    {{ ucfirst($blog->status) }}
                                </span>
                            </small>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted">No recent blog posts</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection