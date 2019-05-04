<ul class="navbar-nav">
    {{--<li class="nav-item">--}}
        {{--<a class="nav-link" data-widget="pushmenu" href="#"><i class="fa fa-bars"></i></a>--}}
    {{--</li>--}}
    <li class="nav-item d-none d-sm-inline-block">
        <a href="/" class="nav-link"><i class="fa fa-home"></i> داشبورد</a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
        <a href="/tasks" class="nav-link">کارهای من</a>
    </li>
    @role('admin')
    <li class="nav-item d-none d-sm-inline-block">
        <a href="/comments" class="nav-link">گفتگوها</a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
        <a href="/pending" class="nav-link">در انتظار</a>
    </li>
    @endrole
    @role('admin|modir')
    <li class="nav-item d-none d-sm-inline-block">
        <a href="/jobs" class="nav-link">مشاهده کارها</a>
    </li>
    @endrole

    @role('modir')

    <li class="nav-item d-none d-sm-inline-block">
        <a href="/pending?nouser" class="nav-link">در انتظار</a>
    </li>


    @endrole

</ul>