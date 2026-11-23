<nav class="navbar" role="navigation" aria-label="Main navigation">

    {{-- Logo: actual AeroSense logo image (left) --}}
    <a href="{{ route('viewer.dashboard') }}" class="navbar__logo" id="nav-logo">
        <img
            src="{{ asset('assets/images/aerosenselogo.png') }}"
            alt="AeroSense Logo"
            class="navbar__logo-img"
        >
    </a>

    {{-- Navigation Links: centered --}}
    <ul class="navbar__nav" role="list">
        <li>
            <a href="{{ route('viewer.dashboard') }}"
               class="navbar__link {{ request()->routeIs('viewer.dashboard') ? 'active' : '' }}"
               id="nav-dashboard"
               aria-current="{{ request()->routeIs('viewer.dashboard') ? 'page' : 'false' }}">
                Dashboard
            </a>
        </li>
        <li>
            <a href="{{ route('viewer.education') }}"
               class="navbar__link {{ request()->routeIs('viewer.education') ? 'active' : '' }}"
               id="nav-education"
               aria-current="{{ request()->routeIs('viewer.education') ? 'page' : 'false' }}">
                Education
            </a>
        </li>
    </ul>

    {{-- Right spacer/actions --}}
    <div class="navbar__right-actions">
        @if(request()->routeIs('viewer.dashboard'))
            <a href="{{ url('/admin') }}" class="admin-login-link">
                Log in as admin
            </a>
        @else
            <div class="navbar__spacer" aria-hidden="true"></div>
        @endif
    </div>

</nav>
