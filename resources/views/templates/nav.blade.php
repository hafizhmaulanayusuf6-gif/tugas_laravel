<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar"><i class="bi bi-list"></i></a>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width:2rem;height:2rem" aria-hidden="true">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">{{ auth()->user()->name }}<br><small>{{ \App\Models\User::ROLES[auth()->user()->role] ?? 'User' }}</small></h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="{{ auth()->user()->role === 'admin' ? route('dashboard.index') : route('orders.index') }}" class="brand-link">
            <img src="{{ asset('assets/assets/img/AdminLTELogo.png') }}" alt="ZieMarket" class="brand-image opacity-75 shadow">
            <span class="brand-text fw-light">ZieMarket</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2" aria-label="Navigasi utama">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false" id="navigation">
                @if (auth()->user()->role === 'admin')
                    <li class="nav-item">
                        <a href="{{ route('dashboard.index') }}" class="nav-link {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer"></i><p>Dashboard</p>
                        </a>
                    </li>
                @endif

                @php($masterDataActive = request()->routeIs('members.*', 'categories.*', 'products.*', 'users.*'))
                <li @class(['nav-item', 'menu-open' => $masterDataActive])>
                    <a href="#" @class(['nav-link', 'active' => $masterDataActive])><i class="nav-icon bi bi-box-seam-fill"></i><p>Master Data<i class="nav-arrow bi bi-chevron-right"></i></p></a>
                    <ul class="nav nav-treeview">
                        @if (auth()->user()->role === 'admin')
                            <li class="nav-item"><a href="{{ route('members.index') }}" class="nav-link {{ request()->routeIs('members.*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Member</p></a></li>
                        @endif
                        <li class="nav-item"><a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Categories</p></a></li>
                        <li class="nav-item"><a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Products</p></a></li>
                        @if (auth()->user()->role === 'admin')
                            <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>User</p></a></li>
                        @endif
                    </ul>
                </li>

                @php($transactionsActive = request()->routeIs('orders.*', 'history.*'))
                <li @class(['nav-item', 'menu-open' => $transactionsActive])>
                    @if (auth()->user()->role === 'admin')
                        <a href="#" @class(['nav-link', 'active' => $transactionsActive])><i class="nav-icon bi bi-shop"></i><p>Transactions<i class="nav-arrow bi bi-chevron-right"></i></p></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Kasir / Order</p></a></li>
                            <li class="nav-item"><a href="{{ route('history.index') }}" class="nav-link {{ request()->routeIs('history.*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>History</p></a></li>
                        </ul>
                    @else
                        <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}"><i class="nav-icon bi bi-shop"></i><p>Kasir</p></a>
                    @endif
                </li>

                @if (auth()->user()->role === 'admin')
                    @php($reportsActive = request()->routeIs('reports.*'))
                    <li @class(['nav-item', 'menu-open' => $reportsActive])>
                        <a href="#" @class(['nav-link', 'active' => $reportsActive])><i class="nav-icon bi bi-clipboard"></i><p>Reports<i class="nav-arrow bi bi-chevron-right"></i></p></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('reports.transactions') }}" class="nav-link {{ request()->routeIs('reports.transactions') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Transactions</p></a></li>
                            <li class="nav-item"><a href="{{ route('reports.products') }}" class="nav-link {{ request()->routeIs('reports.products') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Products</p></a></li>
                            <li class="nav-item"><a href="{{ route('reports.members') }}" class="nav-link {{ request()->routeIs('reports.members') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Members</p></a></li>
                        </ul>
                    </li>
                @endif
            </ul>

            <div class="p-3 mt-3 border-top border-secondary border-opacity-25">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-danger w-100 d-flex align-items-center justify-content-center gap-2" type="submit"><i class="bi bi-power" aria-hidden="true"></i>Logout</button>
                </form>
            </div>
        </nav>
    </div>
</aside>
