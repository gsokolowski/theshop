<div class="sidebar border border-right col-md-3 col-lg-2 p-0 bg-body-tertiary">
    <div class="offcanvas-md offcanvas-end bg-body-tertiary" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="sidebarMenuLabel">Vue T-shirts</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 @if(request()->routeIs('admin.dashboard')) text-dark @endif" aria-current="page" href="{{route('admin.dashboard')}}">
                        <i class="fa-solid fa-dashboard"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 @if(request()->routeIs('admin.categories.index')) text-dark @endif" aria-current="page" href="{{route('admin.categories.index')}}">
                        <i class="fa-solid fa-layer-group"></i>
                        Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 @if(request()->routeIs('admin.brands.index')) text-dark @endif" aria-current="page" href="{{route('admin.brands.index')}}">
                        <i class="fa-solid fa-layer-group"></i>
                        Brands
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 @if(request()->routeIs('admin.colors.index')) text-dark @endif" aria-current="page" href="{{route('admin.colors.index')}}">
                        <i class="fa-solid fa-palette"></i>
                        Colors
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 @if(request()->routeIs('admin.sizes.index')) text-dark @endif" aria-current="page" href="{{route('admin.sizes.index')}}">
                        <i class="fa-solid fa-ruler"></i>
                        Sizes
                    </a>
                </li>
            </ul>
            <hr class="my-3">
            <ul class="nav flex-column mb-auto">
                <li class="nav-item">
                    <a href="#" class="nav-link d-flex align-items-center gap-2">
                        <i class="fas fa-user"></i>
                        <!-- Display the name of the logged in admin -->
                        {{ auth()->guard('admin')->user()->name }}
                    </a>
                </li>
                <li class="nav-item">
                    <!-- Form to submit the logout request -->
                    <form id="adminLogoutForm" action="{{route('admin.logout')}}" method="post">
                        @csrf
                        @method('POST')
                    </form>
                    <!-- Logout button -->
                    <a href="#" onclick="document.getElementById('adminLogoutForm').submit()"
                        class="nav-link d-flex align-items-center gap-2">
                        <i class="fas fa-sign-out"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>