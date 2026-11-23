<nav class="navbar" role="navigation" aria-label="Main navigation">

    
    <a href="<?php echo e(route('viewer.dashboard')); ?>" class="navbar__logo" id="nav-logo">
        <img
            src="<?php echo e(asset('assets/images/aerosenselogo.png')); ?>"
            alt="AeroSense Logo"
            class="navbar__logo-img"
        >
    </a>

    
    <ul class="navbar__nav" role="list">
        <li>
            <a href="<?php echo e(route('viewer.dashboard')); ?>"
               class="navbar__link <?php echo e(request()->routeIs('viewer.dashboard') ? 'active' : ''); ?>"
               id="nav-dashboard"
               aria-current="<?php echo e(request()->routeIs('viewer.dashboard') ? 'page' : 'false'); ?>">
                Dashboard
            </a>
        </li>
        <li>
            <a href="<?php echo e(route('viewer.education')); ?>"
               class="navbar__link <?php echo e(request()->routeIs('viewer.education') ? 'active' : ''); ?>"
               id="nav-education"
               aria-current="<?php echo e(request()->routeIs('viewer.education') ? 'page' : 'false'); ?>">
                Education
            </a>
        </li>
    </ul>

    
    <div class="navbar__right-actions">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->routeIs('viewer.dashboard')): ?>
            <a href="<?php echo e(url('/admin')); ?>" class="admin-login-link">
                Log in as admin
            </a>
        <?php else: ?>
            <div class="navbar__spacer" aria-hidden="true"></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

</nav>
<?php /**PATH C:\xampp\htdocs\aerosense-V2\resources\views/components/navbar.blade.php ENDPATH**/ ?>