<!-- Side Nav START -->
@php
    $pendingCount = DB::table('packages')->where('status', 3)->where('is_temprary_logs', 0)->count();
    $adminName = Session::get('admin_name') ?: 'Admin';
    $avatar = Session::get('avater') ? asset('users/'.Session::get('avater')) : asset('dummy_round.png');
    $can = function($permissions) {
        return commonHelper::canAccessAny((array) $permissions);
    };
@endphp
<div class="side-nav">
    <div class="side-nav-inner">
        <div class="portal-logo">
            <a href="{{URL::to('admin/dashboard')}}">
                @if(file_exists(public_path().'/settings/'.$getSettings->site_logo))
                    <img src="{{url('').'/public/settings/'.$getSettings->site_logo}}" alt="{{$getSettings->site_name}}">
                @else
                    <span class="portal-logo-text">{{ $getSettings->site_name ?? $getSettings->company_name ?? $getSettings->site_title }}</span>
                @endif
            </a>
        </div>

        <div class="portal-scope">
            <label for="warehouse-selector">Global Scope</label>
            <select id="warehouse-selector" onchange="if(this.value){ window.location=this.value; }">
                <option value="">All Warehouses</option>
                <option value="{{URL::to('admin/packeges/logs/0')}}">USA / Warehouse Logs</option>
                <option value="{{URL::to('admin/packeges/logs/0?warehouse=China')}}">China / Warehouse Logs</option>
                <option value="{{URL::to('admin/packeges/logs/0?status=In%20Transit')}}">In Transit</option>
            </select>
        </div>

        <ul class="portal-menu">
            <li>
                <a class="portal-link {{request()->is('admin/dashboard') ? 'is-active' : ''}}" href="{{URL::to('admin/dashboard')}}">
                    <i class="ti-pie-chart"></i> Admin Dashboard
                </a>
            </li>

            @php
                $showPackages = $can(['Log Packages', 'Package Management', 'Packages']);
                $showUnknowns = $can(['General Unknowns', 'Assigned Claims', 'Unknowns']);
                $showCrm = $can(['Support', 'Send Email', 'CRM']);
                $showManifest = $can(['Create Manifest', 'Manage Manifest', 'Build Master Container', 'Manifest']);
                $showBookings = $can(['Shipment (Air/Sea)', 'Flight Schedule', 'Sea Vessel Schedules', 'Bookings']);
                $showOperations = $can(['Warehouse Supplies', 'Internal Inventory', 'Log Internal Waste', 'Shipping Partners', 'Tracking Status', 'Operations']);
                $showFinance = $can(['Email Unpaid', 'Income / Expense', 'Finance']);
                $showAccounts = $can(['Clients', 'Couriers', 'Employee', 'Employees', 'Accounts']);
                $showReports = $can(['Reports (All Modules)', 'Profit / Loss', 'Balance Sheet']);
                $showSettings = $can(['General Settings', 'SMTP Integration', 'API Integration', 'Roles & Permissions', 'Permission Matrix', 'Weight Groups', 'Package Types', 'Settings']);
                $showActivity = $can(['Activity Log']);
            @endphp

            @if($showPackages || $showUnknowns)
                <li class="portal-menu-title">Packages</li>
            @endif
            @if($showPackages)
                <li>
                    <button class="portal-toggle collapsed" type="button" data-target="#portal-packages">
                        <i class="ti-package"></i> Packages <i class="ti-angle-down menu-caret"></i>
                    </button>
                    <ul id="portal-packages" class="portal-submenu" style="display:none;">
                        @if($can(['Log Packages', 'Packages']))<li><a href="{{URL::to('admin/packege/add')}}">Log Packages</a></li>@endif
                        @if($can(['Package Management', 'Packages']))<li><a href="{{URL::to('admin/packeges/logs/0')}}">Package Management</a></li>@endif
                    </ul>
                </li>
            @endif

            @if($showUnknowns)
                <li>
                    <button class="portal-toggle collapsed" type="button" data-target="#portal-unknowns">
                        <i class="ti-help-alt"></i> Unknowns <i class="ti-angle-down menu-caret"></i>
                    </button>
                    <ul id="portal-unknowns" class="portal-submenu" style="display:none;">
                        @if($can(['General Unknowns', 'Unknowns']))<li><a href="{{URL::to('admin/packeges/unknown/0')}}">General Unknowns</a></li>@endif
                        @if($can(['Assigned Claims', 'Package Requests', 'Unknowns']))<li><a href="{{URL::to('admin/packeges/assigned-claims/0')}}">Assigned Claims</a></li>@endif
                    </ul>
                </li>
            @endif

            @if($showCrm)
                <li class="portal-menu-title">CRM</li>
                <li>
                    <button class="portal-toggle collapsed" type="button" data-target="#portal-crm">
                        <i class="ti-user"></i> CRM <i class="ti-angle-down menu-caret"></i>
                    </button>
                    <ul id="portal-crm" class="portal-submenu" style="display:none;">
                        @if($can(['Support', 'CRM']))<li><a href="{{URL::to('admin/portal/support')}}">Support</a></li>@endif
                        @if($can(['Send Email', 'CRM']))<li><a href="{{URL::to('send/email')}}">Send Email</a></li>@endif
                    </ul>
                </li>
            @endif

            @if($showManifest || $showBookings || $showOperations)
                <li class="portal-menu-title">Operations</li>
            @endif
            @if($showManifest)
                <li>
                    <button class="portal-toggle collapsed" type="button" data-target="#portal-manifest">
                        <i class="ti-clipboard"></i> Manifest <i class="ti-angle-down menu-caret"></i>
                    </button>
                    <ul id="portal-manifest" class="portal-submenu" style="display:none;">
                        @if($can(['Create Manifest', 'Manifest']))<li><a href="{{URL::to('admin/list/manifest/courier')}}">Create Manifest</a></li>@endif
                        @if($can(['Manage Manifest', 'Manifest']))<li><a href="{{URL::to('admin/package/manifest/0')}}">Manage Manifest</a></li>@endif
                        @if($can(['Build Master Container', 'Manifest']))<li><a href="{{URL::to('admin/create/manifest')}}">Build Master Container</a></li>@endif
                    </ul>
                </li>
            @endif
            @if($showBookings)
                <li>
                    <button class="portal-toggle collapsed" type="button" data-target="#portal-bookings">
                        <i class="ti-calendar"></i> Bookings <i class="ti-angle-down menu-caret"></i>
                    </button>
                    <ul id="portal-bookings" class="portal-submenu" style="display:none;">
                        @if($can(['Shipment (Air/Sea)', 'Bookings']))<li><a href="{{URL::to('admin/portal/shipment-bookings')}}">Shipment (Air/Sea)</a></li>@endif
                        @if($can(['Flight Schedule', 'Bookings']))<li><a href="{{URL::to('admin/portal/flight-schedules')}}">Flight Schedule</a></li>@endif
                        @if($can(['Sea Vessel Schedules', 'Bookings']))<li><a href="{{URL::to('admin/portal/sea-vessel-schedules')}}">Sea Vessel Schedules</a></li>@endif
                    </ul>
                </li>
            @endif
            @if($showOperations)
                <li>
                    <button class="portal-toggle collapsed" type="button" data-target="#portal-ops">
                        <i class="ti-settings"></i> Operations <i class="ti-angle-down menu-caret"></i>
                    </button>
                    <ul id="portal-ops" class="portal-submenu" style="display:none;">
                        @if($can(['Warehouse Supplies', 'Operations']))<li><a href="{{URL::to('admin/portal/warehouse-supplies')}}">Warehouse Supplies</a></li>@endif
                        @if($can(['Internal Inventory', 'Operations']))<li><a href="{{URL::to('admin/portal/internal-inventory')}}">Internal Inventory</a></li>@endif
                        @if($can(['Log Internal Waste', 'Operations']))<li><a href="{{URL::to('admin/portal/internal-waste')}}">Log Internal Waste</a></li>@endif
                        @if($can(['Shipping Partners', 'Operations']))<li><a href="{{URL::to('admin/carriers')}}">Shipping Partners</a></li>@endif
                        @if($can(['Tracking Status', 'Operations']))<li><a href="{{URL::to('admin/package/status')}}">Tracking Status</a></li>@endif
                    </ul>
                </li>
            @endif

            @if($showFinance)
                <li class="portal-menu-title">Finance</li>
                <li>
                    <button class="portal-toggle collapsed" type="button" data-target="#portal-finance">
                        <i class="ti-money"></i> Finance <i class="ti-angle-down menu-caret"></i>
                    </button>
                    <ul id="portal-finance" class="portal-submenu" style="display:none;">
                        @if($can(['Email Unpaid', 'Finance']))<li><a href="{{URL::to('admin/invoices/email/0?start_date=&end_date=')}}">Email Unpaid</a></li>@endif
                        @if($can(['Income / Expense', 'Finance']))<li><a href="{{URL::to('admin/portal/finance-entries')}}">Income / Expense</a></li>@endif
                    </ul>
                </li>
            @endif

            @if($showAccounts)
                <li class="portal-menu-title">Accounts</li>
                <li>
                    <button class="portal-toggle collapsed" type="button" data-target="#portal-accounts">
                        <i class="ti-id-badge"></i> Accounts <i class="ti-angle-down menu-caret"></i>
                    </button>
                    <ul id="portal-accounts" class="portal-submenu" style="display:none;">
                        @if($can(['Clients', 'Accounts']))<li><a href="{{URL::to('admin/users')}}">Clients</a></li>@endif
                        @if($can(['Couriers', 'Accounts']))<li><a href="{{URL::to('admin/couriers')}}">Couriers</a></li>@endif
                        @if($can(['Employee', 'Employees', 'Accounts']))<li><a href="{{URL::to('admin/users/list')}}">Employee</a></li>@endif
                    </ul>
                </li>
            @endif

            @if($showReports)
                <li>
                    <a class="portal-link" href="{{URL::to('admin/portal/reports')}}">
                        <i class="ti-files"></i> Reports (All Modules)
                    </a>
                </li>
            @endif

            @if($showSettings)
                <li class="portal-menu-title">Settings</li>
                <li>
                    <button class="portal-toggle collapsed" type="button" data-target="#portal-settings">
                        <i class="ti-panel"></i> Settings <i class="ti-angle-down menu-caret"></i>
                    </button>
                    <ul id="portal-settings" class="portal-submenu" style="display:none;">
                        @if($can(['General Settings', 'Settings']))<li><a href="{{URL::to('admin/settings')}}">General Settings</a></li>@endif
                        @if($can(['SMTP Integration', 'Settings']))<li><a href="{{URL::to('admin/portal-settings')}}">SMTP Integration</a></li>@endif
                        @if($can(['API Integration', 'Settings']))<li><a href="{{URL::to('admin/portal-settings')}}">API Integration</a></li>@endif
                        @if($can(['Roles & Permissions', 'Settings']))<li><a href="{{URL::to('admin/roles/new')}}">Roles & Permissions</a></li>@endif
                        @if($can(['Permission Matrix', 'Settings']))<li><a href="{{URL::to('admin/permission/assign')}}">Permission Matrix</a></li>@endif
                        @if($can(['Weight Groups', 'Settings']))<li><a href="{{URL::to('admin/weight')}}">Weight Groups</a></li>@endif
                        @if($can(['Package Types', 'Settings']))<li><a href="{{URL::to('admin/package/type')}}">Package Types</a></li>@endif
                    </ul>
                </li>
            @endif

            @if($showActivity)
                <li>
                    <a class="portal-link" href="{{URL::to('/admin/activies')}}">
                        <i class="ti-time"></i> Activity Log
                    </a>
                </li>
            @endif
            <li>
                <a class="portal-link side-nav-toggle" href="javascript:void(0)">
                    <i class="ti-layout-sidebar-left"></i> Auto Hide Menu
                </a>
            </li>
            <li>
                <a class="portal-link" href="{{URL::to('admin/logout')}}">
                    <i class="ti-power-off"></i> Logout
                </a>
            </li>
        </ul>

        <div class="portal-user">
            <img src="{{$avatar}}" alt="{{$adminName}}">
            <div>
                <strong>{{$adminName}}</strong>
                <span>Online</span>
            </div>
        </div>
    </div>
</div>
<!-- Side Nav END -->
