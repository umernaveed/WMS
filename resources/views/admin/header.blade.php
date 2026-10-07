<!-- Header START -->
<div class="header navbar">
    <div class="portal-header">
        <div class="portal-header-left">
            <a class="side-nav-toggle portal-icon-btn" href="javascript:void(0);" title="Toggle menu">
                <i class="ti-view-grid"></i>
            </a>

            <div class="portal-search">
                <i class="ti-search"></i>
                <input id="portal-global-search" type="search" autocomplete="off" placeholder="Global search tracking, mailbox, invoice, customer...">
                <div id="portal-search-results" class="portal-search-results"></div>
            </div>
        </div>

        <div class="portal-header-right">
            <span class="portal-role">System Admin</span>

            <button type="button" class="portal-action-btn" onclick="openBulkUploadModal()">
                <i class="ti-upload"></i>
                <span>Bulk Upload</span>
            </button>

            <button type="button" class="portal-icon-btn portal-notification-btn" title="Notifications">
                <i class="ti-bell"></i>
                <span></span>
            </button>

            <div class="user-profile dropdown">
                <a href="" class="dropdown-toggle portal-profile" data-toggle="dropdown">
                    @if(Session::get('avater') == '')
                        <img src="{{asset('dummy_round.png')}}" alt="Admin">
                    @else
                        <img src="{{asset('users/'.Session::get('avater'))}}" alt="Admin">
                    @endif
                    <span class="name">{{Session::get('admin_name')}}</span>
                    <i class="ti-angle-down font-size-10"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li>
                        <a href="{{URL::to('admin/profile')}}">
                            <i class="ti-user pdd-right-10"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{URL::to('admin/change/password')}}">
                            <i class="ti-lock pdd-right-10"></i>
                            <span>Change Password</span>
                        </a>
                    </li>
                    <li role="separator" class="divider"></li>
                    <li>
                        <a href="{{URL::to('admin/logout')}}">
                            <i class="ti-power-off pdd-right-10"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- Header END -->
