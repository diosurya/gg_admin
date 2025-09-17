{{-- resources/views/layouts/partials/header.blade.php --}}
<div class="row border-bottom">
    <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <a class="navbar-minimalize minimalize-styl-2 btn btn-primary" href="#"><i class="fa fa-bars"></i></a>
            <form role="search" class="navbar-form-custom" action="#" method="GET">
                <div class="form-group">
                    <input type="text" placeholder="Search for something..." class="form-control" 
                           name="q" id="top-search" value="{{ request('q') }}">
                </div>
            </form>
        </div>
        
        <ul class="nav navbar-top-links navbar-right">
            <li>
                <span class="m-r-sm text-muted welcome-message">
                    Welcome to Gudang Grosiran, {{ Auth::user()->first_name ?? 'User' }}!
                </span>
            </li>

            {{-- User Menu --}}
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">
                    <img alt="image" class="rounded-circle" src="{{ Auth::user()->avatar ?? asset('img/profile_small.jpg') }}" 
                         style="width: 25px; height: 25px;">
                    {{ Auth::user()->name ?? ''}} <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Profile</a></li>
                    <li><a class="dropdown-item" href="#">Settings</a></li>
                    <li class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <a class="dropdown-item" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="fa fa-sign-out"></i> Log out
                            </a>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</div>

{{-- Page Heading Component --}}
@if(isset($pageTitle) || isset($breadcrumbs))
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        @if(isset($pageTitle))
        <h2>{{ $pageTitle }}</h2>
        @endif
        
        @if(isset($breadcrumbs))
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">Home</a>
            </li>
            @foreach($breadcrumbs as $breadcrumb)
                @if($loop->last)
                    <li class="breadcrumb-item active">
                        <strong>{{ $breadcrumb['title'] }}</strong>
                    </li>
                @else
                    <li class="breadcrumb-item">
                        @if(isset($breadcrumb['url']))
                            <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a>
                        @else
                            {{ $breadcrumb['title'] }}
                        @endif
                    </li>
                @endif
            @endforeach
        </ol>
        @endif
    </div>
    
    <div class="col-lg-2">
        @stack('page-actions')
    </div>
</div>
@endif