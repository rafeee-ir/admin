<aside class="main-sidebar sidebar-light-danger elevation-4 overflow-hidden">
    <!-- Brand Logo -->
    <a href="/" class="brand-link">
        <img src="/img/logo.png" alt="Logo" class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text">سامانه مدیریت</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <!-- Sidebar user panel (optional) -->

            @include('admincore.sidebar.user')

            <!-- Sidebar Menu -->

            @include('admincore.sidebar.menu')
            <!-- /.sidebar-menu -->
        </div>
    </div>
    <!-- /.sidebar -->
</aside>
