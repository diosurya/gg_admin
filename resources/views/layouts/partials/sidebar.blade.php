<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            {{-- Profile Section --}}
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <img alt="image" class="rounded-circle" src="{{ Auth::user()->avatar ?? asset('img/profile_small.jpg') }}"/>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="block m-t-xs font-bold">{{ Auth::user()->first_name . ' ' . Auth::user()->last_name ?? 'Guest User' }}</span>
                        <span class="text-muted text-xs block">{{ Auth::user()->role ?? 'User' }} <b class="caret"></b></span>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li><a class="dropdown-item" href="#">Settings</a></li>
                        <li class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <a class="dropdown-item" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                                    Logout
                                </a>
                            </form>
                        </li>
                    </ul>
                </div>
                <div class="logo-element">GG</div>
            </li>

            {{-- Dashboard --}}
            <li class="{{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fa fa-th-large"></i> 
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                <a href="#"><i class="fa fa-shopping-cart"></i> <span class="nav-label">E-commerce</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level {{ request()->routeIs('products*') ? '' : 'collapse' }}">
                    <li class="{{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.products.index') }}">Products List</a>
                    </li>
                    <li class="{{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
                        <a href="{{ route('admin.products.create') }}">Add Product</a>
                    </li>
                </ul>
            </li>

            {{-- E-commerce Section --}}
            @can('access-products')
            <li class="{{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                <a href="#"><i class="fa fa-shopping-cart"></i> <span class="nav-label">E-commerce</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level {{ request()->routeIs('products*') ? '' : 'collapse' }}">
                    <li class="{{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.products.index') }}">Products List</a>
                    </li>
                    <li class="{{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
                        <a href="{{ route('admin.products.create') }}">Add Product</a>
                    </li>
                    <li class="{{ request()->routeIs('categories*') ? 'active' : '' }}">
                        <a href="{{ route('categories.index') }}">Categories</a>
                    </li>
                    <li class="{{ request()->routeIs('orders*') ? 'active' : '' }}">
                        <a href="{{ route('orders.index') }}">Orders</a>
                    </li>
                </ul>
            </li>
            @endcan

            {{-- User Management --}}
            @can('manage-users')
            <li class="{{ request()->routeIs('users*') ? 'active' : '' }}">
                <a href="#"><i class="fa fa-users"></i> <span class="nav-label">User Management</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level {{ request()->routeIs('users*') ? '' : 'collapse' }}">
                    <li class="{{ request()->routeIs('users.index') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}">All Users</a>
                    </li>
                    <li class="{{ request()->routeIs('roles*') ? 'active' : '' }}">
                        <a href="{{ route('roles.index') }}">Roles & Permissions</a>
                    </li>
                </ul>
            </li>
            @endcan

            {{-- Reports --}}
            @can('view-reports')
            <li class="{{ request()->routeIs('reports*') ? 'active' : '' }}">
                <a href="#"><i class="fa fa-bar-chart-o"></i> <span class="nav-label">Reports</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level {{ request()->routeIs('reports*') ? '' : 'collapse' }}">
                    <li><a href="{{ route('reports.sales') }}">Sales Report</a></li>
                    <li><a href="{{ route('reports.inventory') }}">Inventory Report</a></li>
                    <li><a href="{{ route('reports.users') }}">User Activity</a></li>
                </ul>
            </li>
            @endcan

            {{-- Settings --}}
            @can('manage-settings')
            <li class="{{ request()->routeIs('settings*') ? 'active' : '' }}">
                <a href="{{ route('settings.index') }}">
                    <i class="fa fa-cog"></i> 
                    <span class="nav-label">Settings</span>
                </a>
            </li>
            @endcan
        </ul>
    </div>
</nav>