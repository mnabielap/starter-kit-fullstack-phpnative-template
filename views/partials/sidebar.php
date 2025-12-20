<?php use App\Utils\Helper; ?>
<div class="app-menu">
    <div class="navbar-brand-box">
        TEMPLATE
    </div>
    <div id="scrollbar">
        <div class="container-fluid">
            <ul class="navbar-nav" id="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo Helper::baseUrl('/'); ?>">
                        <i class="bi bi-speedometer2 me-2"></i> <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo Helper::baseUrl('users'); ?>">
                        <i class="bi bi-people me-2"></i> <span>Users</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>