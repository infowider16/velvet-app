<nav class="sidebar sidebar-offcanvas" id="sidebar">

    <ul class="nav">

        <li class="nav-item nav-profile">

            <a href="#" class="nav-link">

                <div class="profile-image">

                    <img 
                                         src="{{ auth('admin')->user()->profile_image ? asset('storage/' . auth('admin')->user()->profile_image) : asset('assets/images/user-default.jpg') }}" 

                        alt="profile" 

                        id="{{ $profileImageId ?? 'admin-sidebar-profile-img' }}"

                        class="img-xs rounded-circle"

                    >

                    <div class="dot-indicator bg-success"></div>

                </div>

                <div class="text-wrapper">

                    <p class="profile-name">{{ Auth::guard('admin')->user()->name ?? 'Admin User' }}</p>

                    <p class="designation">Administrator</p>

                </div>

            </a>

        </li>

        <li class="nav-item nav-category">Main Menu</li>

        <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">

            <a class="nav-link" href="{{ route('admin.dashboard') }}">

                <i class="menu-icon typcn typcn-document-text"></i>

                <span class="menu-title">Dashboard</span>

            </a>

        </li>

        <li class="nav-item {{ request()->routeIs('admin.ghost.*', 'admin.boost.*', 'admin.pin.*') ? 'active' : '' }}">

            <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="{{ request()->routeIs('admin.ghost.*', 'admin.boost.*', 'admin.pin.*') ? 'true' : 'false' }}" aria-controls="ui-basic">

                <i class="menu-icon typcn typcn-coffee"></i>

                <span class="menu-title">User Management</span>

                <i class="menu-arrow"></i>

            </a>

            <div class="collapse {{ request()->routeIs('admin.ghost.*', 'admin.boost.*', 'admin.pin.*') ? 'show' : '' }}" id="ui-basic">

                <ul class="nav flex-column sub-menu">

                    <li class="nav-item">

                        <a class="nav-link" href="{{ route('admin.users.index') }}">User list</a>

                    </li>

                </ul>

            </div>

        </li>

         <li class="nav-item {{ request()->routeIs('admin.contact.index') ? 'active' : '' }}">

            <a class="nav-link" href="{{ route('admin.contact.index') }}">

                <i class="menu-icon typcn typcn-document-text"></i>

                <span class="menu-title">Contact Management</span>

            </a>

        </li>

        <li class="nav-item {{ request()->routeIs('admin.faq.index') ? 'active' : '' }}">

            <a class="nav-link" href="{{ route('admin.faq.index') }}">

                <i class="menu-icon typcn typcn-document-text"></i>

                <span class="menu-title">FAQ Management</span>

            </a>

        </li>

        <li class="nav-item {{ request()->routeIs('admin.content.index') ? 'active' : '' }}">

            <a class="nav-link" href="{{ route('admin.content.index') }}">

                <i class="menu-icon typcn typcn-document-text"></i>

                <span class="menu-title">Content Management</span>

            </a>

        </li>

        <li class="nav-item {{ request()->routeIs('admin.interest.index') ? 'active' : '' }}">

            <a class="nav-link" href="{{ route('admin.interest.index') }}">

                <i class="menu-icon typcn typcn-document-text"></i>

                <span class="menu-title">Interest Management</span>

            </a>

        </li>
        <li class="nav-item {{ request()->routeIs('admin.sub-interest.index') ? 'active' : '' }}">

            <a class="nav-link" href="{{ route('admin.sub-interest.index') }}">

                <i class="menu-icon typcn typcn-document-text"></i>

                <span class="menu-title">Sub Interest Management</span>

            </a>

        </li>
        <li class="nav-item {{ request()->routeIs('admin.transaction.*') ? 'active' : '' }}">
    <a class="nav-link"
       data-toggle="collapse"
       href="#transaction-menu"
       aria-expanded="false"
       aria-controls="transaction-menu">

        <i class="menu-icon typcn typcn-document-text"></i>
        <span class="menu-title">Transaction Management</span>
        <i class="menu-arrow"></i>
    </a>

    <div class="collapse "  id="transaction-menu">

        <ul class="nav flex-column sub-menu">

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.transaction.ghost*') ? 'active' : '' }}"
                   href="{{ route('admin.transaction.ghost') }}">
                   Ghost Transactions
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.transaction.boost*') ? 'active' : '' }}"
                   href="{{ route('admin.transaction.boost') }}">
                   Boost Transactions
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.transaction.pin*') ? 'active' : '' }}"
                   href="{{ route('admin.transaction.pin') }}">
                   Pin Transactions
                </a>
            </li>

        </ul>
    </div>
</li>


        <li class="nav-item {{ (request()->routeIs('admin.ghost.*') || request()->routeIs('admin.boost.*') || request()->routeIs('admin.pin.*')) && !request()->routeIs('admin.transaction.*') ? 'active' : '' }}">

            <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">

                <i class="menu-icon typcn typcn-coffee"></i>

                <span class="menu-title">Plan Management</span>

                <i class="menu-arrow"></i>

            </a>

            <div class="collapse" id="ui-basic">

                <ul class="nav flex-column sub-menu">

                    <li class="nav-item">

                        <a class="nav-link" href="{{ route('admin.ghost.index') }}">Ghost Management</a>

                    </li>

                    <li class="nav-item">

                        <a class="nav-link" href="{{ route('admin.boost.index') }}">Boost Management</a>

                    </li>
                    <li class="nav-item">

                        <a class="nav-link" href="{{ route('admin.pin.index') }}">Pin Management</a>

                    </li>

                </ul>

            </div>

        </li>
        


         

    </ul>

</nav>

