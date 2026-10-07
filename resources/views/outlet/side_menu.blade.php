<!-- Side Nav START -->
    <div class="side-nav">
        <div class="side-nav-inner">
            <div class="side-nav-logo">
                <a href="{{URL::to('admin/dashboard')}}" style="max-width: 170px;padding: 6px 0 7px;">
                    @if(file_exists( public_path().'/settings/'.$getSettings->site_logo ))
                        <img class="img-responsive inline-block" src=" {{url('').'/settings/'.$getSettings->site_logo}}" style="width: 86px;height: 52px;object-fit: scale-down;" alt="">
                    @else
                        <span class="brand-text" style="display:inline-block;font-weight:700;line-height:1.2;color:#1f2937;">{{ $getSettings->site_name ?? $getSettings->company_name ?? $getSettings->site_title }}</span>
                    @endif
                </a>
                <div class="mobile-toggle side-nav-toggle">
                    <a href="">
                        <i class="ti-arrow-circle-left"></i>
                    </a>
                </div>
            </div>
            <ul class="side-nav-menu scrollable">
                <li class="nav-item active">
                    <a class="" href="{{URL::to('admin/dashboard')}}">
                        <span class="icon-holder">
                            <i class="ti-home"></i>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item dropdown <?php if($class == 'users'){ echo 'open'; } ?>">
                    <a class="dropdown-toggle" href="javascript:void(0);">
                        <span class="icon-holder">
                            <i class="ti-user"></i>
                        </span>
                        <span class="title">Manage Users</span>
                        <span class="arrow">
                            <i class="ti-angle-right"></i>
                        </span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{URL::to('outlet/users')}}">View Outlet User</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown <?php if($class == 'package'){ echo 'open'; } ?>">
                    <a class="dropdown-toggle" href="javascript:void(0);">
                        <span class="icon-holder">
                            <i class=" ti-archive"></i>
                        </span>
                        <span class="title">Manage Package</span>
                        <span class="arrow">
                            <i class="ti-angle-right"></i>
                        </span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{URL::to('outlet/packeges')}}">Package log</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown <?php if($class == 'invoices'){ echo 'open'; } ?>">
                    <a class="dropdown-toggle" href="javascript:void(0);">
                        <span class="icon-holder">
                            <i class=" ei-money"></i>
                        </span>
                        <span class="title">Manage Invoices</span>
                        <span class="arrow">
                            <i class="ti-angle-right"></i>
                        </span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{URL::to('outlet/invoices')}}">view Invoices</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item active">
                    <a class="" href="{{URL::to('outlet/calculater')}}">
                        <span class="icon-holder">
                            <i class="fa fa-archive"></i>
                        </span>
                        <span class="title">Configure</span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="dropdown-toggle " href="{{URL::to('admin/logout')}}">
                        <span class="icon-holder">
                            <i class="ei-log-out"></i>
                        </span>
                        <span class="title">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
<!-- Side Nav END -->
