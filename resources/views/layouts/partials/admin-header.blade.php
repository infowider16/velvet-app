<!-- Navigation Bar -->

<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex flex-row">

    <div class="text-center navbar-brand-wrapper d-flex align-items-top justify-content-center">

        <a class="navbar-brand brand-logo" href="{{ route('admin.dashboard') }}">

            <img src="{{ asset(env('APP_LOGO_PATH')) }}" alt="logo" />

        </a>

        <a class="navbar-brand brand-logo-mini" href="{{ route('admin.dashboard') }}">

            <img src="{{ asset(env('APP_LOGO_PATH')) }}" alt="logo" />

        </a>

    </div>

    <div class="navbar-menu-wrapper d-flex align-items-center">

        <ul class="navbar-nav">

            <!-- <li class="nav-item font-weight-semibold d-none d-lg-block">Help : +050 2992 709</li> -->

            <!-- <li class="nav-item dropdown language-dropdown">

                <a class="nav-link dropdown-toggle px-2 d-flex align-items-center" id="LanguageDropdown" href="#" data-toggle="dropdown" aria-expanded="false">

                    <div class="d-inline-flex mr-0 mr-md-3">

                        <div class="flag-icon-holder">

                            <i class="flag-icon flag-icon-us"></i>

                        </div>

                    </div>

                    <span class="profile-text font-weight-medium d-none d-md-block">English</span>

                </a>

                <div class="dropdown-menu dropdown-menu-left navbar-dropdown py-2" aria-labelledby="LanguageDropdown">

                    <a class="dropdown-item">

                        <div class="flag-icon-holder">

                            <i class="flag-icon flag-icon-us"></i>

                        </div>English

                    </a>

                    <a class="dropdown-item">

                        <div class="flag-icon-holder">

                            <i class="flag-icon flag-icon-fr"></i>

                        </div>French

                    </a>

                </div>

            </li> -->

        </ul>

        <!-- <form class="ml-auto search-form d-none d-md-block" action="#">

            <div class="form-group">

                <input type="search" class="form-control" placeholder="Search Here">

            </div>

        </form> -->

        <ul class="navbar-nav ml-auto">

            <!-- <li class="nav-item dropdown">

                <a class="nav-link count-indicator" id="notificationDropdown" href="#" data-toggle="dropdown">

                    <i class="mdi mdi-bell-outline"></i>

                    <span class="count">7</span>

                </a>

                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="notificationDropdown">

                    <a class="dropdown-item py-3">

                        <p class="mb-0 font-weight-medium float-left">You have 7 unread notifications</p>

                        <span class="badge badge-pill badge-primary float-right">View all</span>

                    </a>

                </div>

            </li> -->

            <li class="nav-item dropdown d-none d-xl-inline-block user-dropdown">

                <a class="nav-link dropdown-toggle" id="UserDropdown" href="#" data-toggle="dropdown" aria-expanded="false">

                    <img 
                                         src="{{ auth('admin')->user()->profile_image ? asset('storage/' . auth('admin')->user()->profile_image) : asset('assets/images/user-default.jpg') }}" 


                        alt="profile" 

                        id="{{ $profileImageId ?? 'admin-header-profile-img' }}"

                        class="img-xs rounded-circle"

                    >

                </a>

                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">

                    <div class="dropdown-header text-center">

                        <img class="img-md rounded-circle" src="{{ auth('admin')->user()->profile_image ? asset('storage/' . auth('admin')->user()->profile_image) : asset('assets/images/default-avatar.png') }}" alt="Profile image">

                        <p class="mb-1 mt-3 font-weight-semibold">{{ Auth::guard('admin')->user()->name ?? 'Admin User' }}</p>

                        <p class="font-weight-light text-muted mb-0">{{ Auth::guard('admin')->user()->email ?? 'admin@example.com' }}</p>

                    </div>

                    <a class="dropdown-item" href="{{ route('admin.profile') }}">My Profile <i class="dropdown-item-icon ti-dashboard"></i></a>

                    <a class="dropdown-item" href="{{ route('admin.logout') }}"

                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">

                        Sign Out<i class="dropdown-item-icon ti-power-off"></i>

                    </a>

                </div>

            </li>

        </ul>

        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">

            <span class="mdi mdi-menu"></span>

        </button>

    </div>

</nav>



<form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">

    @csrf

</form>

