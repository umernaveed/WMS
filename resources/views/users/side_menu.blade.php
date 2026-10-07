<!-- Side Nav START -->
    <div class="side-nav">
        <div class="side-nav-inner">
            <div class="side-nav-logo">
                <a href="{{URL::to('user/dashboard')}}" style="max-width: 170px;padding: 6px 0 7px;">
                    @if(file_exists( public_path().'/settings/'.$getSettings->site_logo ))
                        <img class="img-responsive inline-block" src=" {{url('').'/public/settings/'.$getSettings->site_logo}}" style="width: 86px;height: 52px;object-fit: scale-down;" alt="">
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
                    <a class="" href="{{URL::to('user/dashboard')}}">
                        <span class="icon-holder">
                            <i class="ti-home"></i>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item dropdown <?php if($class == 'package'){ echo 'open'; } ?>">
                    <a class="dropdown-toggle" href="javascript:void(0);">
                        <span class="icon-holder">
                            <i class=" ti-archive"></i>
                        </span>
                        <span class="title">My Account</span>
                        <span class="arrow">
                            <i class="ti-angle-right"></i>
                        </span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{URL::to('user/packege/add')}}">Add Pre-Alert</a>
                        </li>
                        <li>
                            <a href="{{URL::to('user/packeges/track')}}"> Track Package</a>
                        </li>
                        <li>
                            <a href="{{URL::to('user/invoices')}}">  Invoices</a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-item dropdown <?php if($class == 'delivery'){ echo 'open'; } ?>">
                    <a class="dropdown-toggle" href="javascript:void(0);">
                        <span class="icon-holder">
                            <i class=" ti-truck"></i>
                        </span>
                        <span class="title">Delivery system</span>
                        <span class="arrow">
                            <i class="ti-angle-right"></i>
                        </span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{URL::to('user/delivery/packages')}}">Ready For Pickup</a>
                        </li>
                        <li>
                            <a href="{{URL::to('user/packages/delivery/history')}}">Delivery History</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown <?php if($class == 'purchase'){ echo 'open'; } ?>">
                    <a class="dropdown-toggle" href="javascript:void(0);">
                        <span class="icon-holder">
                            <i class=" ei-money"></i>
                        </span>
                        <span class="title">Purchase Request</span>
                        <span class="arrow">
                            <i class="ti-angle-right"></i>
                        </span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{URL::to('user/purchase/add')}}"> Create Purchase Request</a>
                        </li>
                        <li>
                            <a href="{{URL::to('user/purchase/request')}}"> Purchase Requests</a>
                        </li>
                    </ul>
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
