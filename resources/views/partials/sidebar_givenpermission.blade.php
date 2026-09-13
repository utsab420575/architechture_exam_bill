<aside id="sidebar-left" class="sidebar-left">
    <div class="sidebar-header">
        <div class="sidebar-title">
            Prepare Exam Bill
        </div>
        <div class="sidebar-toggle d-none d-md-block" data-toggle-class="sidebar-left-collapsed" data-target="html"
             data-fire-event="sidebar-left-toggle">
            <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
        </div>
    </div>
    <div class="nano">
        <div class="nano-content">
            <nav id="menu" class="nav-main" role="navigation">


                <ul class="nav nav-main">

                    {{-- Dashboard --}}
                    <li>
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="bx bx-home-alt" aria-hidden="true"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    {{-- Import/Export Manage --}}
                    @if(Auth::user()->can('import.menu'))
                        <li class="nav-parent {{ request()->routeIs('import.table.*') ? 'nav-expanded nav-active' : '' }}">
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-file-import" aria-hidden="true"></i>
                                <span>Import/Export Manage</span>
                            </a>
                            <ul class="nav nav-children">
                                @can('import.table.all')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('import.table.all') ? 'text-primary' : '' }}"
                                           href="{{ route('import.table.all') }}">
                                            All Table Import
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif

                    {{-- Committee Input Manage --}}
                    @if(Auth::user()->can('committee_input.menu'))
                        <li class="nav-parent {{ request()->routeIs('committee.input.*') ? 'nav-expanded nav-active' : '' }}">
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-money-check-dollar" aria-hidden="true"></i>
                                <span>Committee Input Manage</span>
                            </a>
                            <ul class="nav nav-children">
                                @can('committee.input.regular.session')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('committee.input.regular.session') ? 'text-primary' : '' }}"
                                           href="{{ route('committee.input.regular.session') }}">
                                            All Regular Session
                                        </a>
                                    </li>
                                @endcan
                                @can('committee.input.review.session')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('committee.input.review.session') ? 'text-primary' : '' }}"
                                           href="{{ route('committee.input.review.session') }}">
                                            All Review Session
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif

                    {{-- Committee Record Manage --}}
                    @if(Auth::user()->can('committee_record.menu'))
                        <li class="nav-parent">
                            <a class="nav-link" href="#">
                                <i class="bx bx-file" aria-hidden="true"></i>
                                <span>Committee Record Manage</span>
                            </a>
                            <ul class="nav nav-children">
                                <li><a class="nav-link" href="#">All Regular Session</a></li>
                                <li><a class="nav-link" href="#">All Review Session</a></li>
                            </ul>
                        </li>
                    @endif

                    {{-- Committee Teacher Manage --}}
                    @if(Auth::user()->can('teacher.menu'))
                        <li class="nav-parent {{ request()->routeIs('teacher.*') ? 'nav-expanded nav-active' : '' }}">
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-user-tie" aria-hidden="true"></i>
                                <span>Committee Teacher Manage</span>
                            </a>
                            <ul class="nav nav-children">
                                @can('teacher.all')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('teacher.all') ? 'text-primary' : '' }}"
                                           href="{{ route('teacher.all') }}">
                                            All Teacher
                                        </a>
                                    </li>
                                @endcan
                                @can('teacher.add')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('teacher.add') ? 'text-primary' : '' }}"
                                           href="{{ route('teacher.add') }}">
                                            Add Teacher
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif

                    {{-- Committee Employee Manage --}}
                    @if(Auth::user()->can('employee.menu'))
                        <li class="nav-parent {{ request()->routeIs('employee.*') ? 'nav-expanded nav-active' : '' }}">
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-users" aria-hidden="true"></i>
                                <span>Committee Employee Manage</span>
                            </a>
                            <ul class="nav nav-children">
                                @can('employee.all')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('employee.all') ? 'text-primary' : '' }}"
                                           href="{{ route('employee.all') }}">
                                            All Employee
                                        </a>
                                    </li>
                                @endcan
                                @can('employee.add')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('employee.add') ? 'text-primary' : '' }}"
                                           href="{{ route('employee.add') }}">
                                            Add Employee
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif

                    {{-- Committee Report Manage --}}
                    @if(Auth::user()->can('report.menu'))
                        <li class="nav-parent {{ request()->routeIs('report.*') ? 'nav-expanded nav-active' : '' }}">
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
                                <span>Committee Report Manage</span>
                            </a>
                            <ul class="nav nav-children">
                                @can('report.regular.session')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('report.regular.session') ? 'text-primary' : '' }}"
                                           href="{{ route('report.regular.session') }}">
                                            All Regular Session
                                        </a>
                                    </li>
                                @endcan
                                @can('report.review.session')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('report.review.session') ? 'text-primary' : '' }}"
                                           href="{{ route('report.review.session') }}">
                                            All Review Session
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif

                    {{-- Roles And Permission --}}
                    @if(Auth::user()->can('role_permission.menu'))
                        <li class="nav-parent {{ request()->routeIs('permission.*') || request()->routeIs('roles.*') ? 'nav-expanded nav-active' : '' }}">
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-toolbox" aria-hidden="true"></i>
                                <span>Roles And Permission</span>
                            </a>
                            <ul class="nav nav-children">
                                @can('permission.all')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('permission.all') ? 'text-primary' : '' }}"
                                           href="{{ route('permission.all') }}">
                                            All Permission
                                        </a>
                                    </li>
                                @endcan
                                @can('roles.all')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('roles.all') ? 'text-primary' : '' }}"
                                           href="{{ route('roles.all') }}">
                                            All Roles
                                        </a>
                                    </li>
                                @endcan
                                @can('roles.permission.all')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('roles.permission.all') ? 'text-primary' : '' }}"
                                           href="{{ route('roles.permission.all') }}">
                                            All Roles in Permission
                                        </a>
                                    </li>
                                @endcan
                                @can('roles.permissions.add')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('roles.permissions.add') ? 'text-primary' : '' }}"
                                           href="{{ route('roles.permissions.add') }}">
                                            Roles in Permission
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif

                    {{-- Role Assignment --}}
                    @if(Auth::user()->can('role_assign.menu'))
                        <li class="nav-parent {{ request()->routeIs('role.assignments.*') ? 'nav-expanded nav-active' : '' }}">
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-lock-open" aria-hidden="true"></i>
                                <span>Setting Admin User</span>
                            </a>
                            <ul class="nav nav-children">
                                @can('role.assignments.all')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('role.assignments.all') ? 'text-primary' : '' }}"
                                           href="{{ route('role.assignments.all') }}">
                                            All User
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif

                </ul>

            </nav>
        </div>
        <script>
            // Maintain Scroll Position
            if (typeof localStorage !== 'undefined') {
                if (localStorage.getItem('sidebar-left-position') !== null) {
                    var initialPosition = localStorage.getItem('sidebar-left-position'),
                        sidebarLeft = document.querySelector('#sidebar-left .nano-content');
                    sidebarLeft.scrollTop = initialPosition;
                }
            }
        </script>
    </div>
</aside>
