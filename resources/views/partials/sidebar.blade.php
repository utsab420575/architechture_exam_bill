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
                                <span>UGR Data Import/Export</span>
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
                        <li
                            class="nav-parent {{ request()->routeIs('committee.input.*') ? 'nav-expanded nav-active' : '' }}">
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-money-check-dollar" aria-hidden="true"></i>
                                <span>Data Entry</span>
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
                                @can('committee.input.review.session.extra')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('committee.input.review.session.extra') ? 'text-primary' : '' }}"
                                            href="{{ route('committee.input.review.session.extra') }}">
                                            All Review Session Extra
                                        </a>
                                    </li>
                                @endcan

                                @can('committee.input.special.session')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('committee.input.special.session') ? 'text-primary' : '' }}"
                                            href="{{ route('committee.input.special.session') }}">
                                            All Special Session
                                        </a>
                                    </li>
                                @endcan

                            </ul>
                        </li>
                    @endif

                    {{-- Committee Record Manage --}}{{--
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
                    @endif--}}

                    {{-- Held On Entry --}}
                    <li class="nav-parent {{ request()->routeIs('held.on.*') ? 'nav-expanded nav-active' : '' }}">
                        <a class="nav-link" href="#">
                            <i class="fa-regular fa-calendar-days" aria-hidden="true"></i>
                            <span>Held On Entry</span>
                        </a>
                        <ul class="nav nav-children">
                            <li>
                                <a class="nav-link {{ request()->routeIs('held.on.regular.session') ? 'text-primary' : '' }}"
                                    href="{{ route('held.on.regular.session') }}">
                                    All Regular Session
                                </a>
                            </li>
                            <li>
                                <a class="nav-link {{ request()->routeIs('held.on.review.session') ? 'text-primary' : '' }}"
                                    href="{{ route('held.on.review.session') }}">
                                    All Review Session
                                </a>
                            </li>
                            <li>
                                <a class="nav-link {{ request()->routeIs('held.on.special.session') ? 'text-primary' : '' }}"
                                    href="{{ route('held.on.special.session') }}">
                                    All Special Session
                                </a>
                            </li>
                        </ul>
                    </li>

                    @if(Auth::user()->can('statement.menu'))
                        <li class="nav-parent {{ request()->routeIs('statement.*') ? 'nav-expanded nav-active' : '' }}">
                            <a class="nav-link" href="#">
                                <i class="bx bx-file" aria-hidden="true"></i>
                                <span>Year-wise Statements</span>
                            </a>
                            <ul class="nav nav-children">
                                <li>
                                    <a class="nav-link {{ request()->routeIs('statement.regular.session') ? 'text-primary' : '' }}"
                                        href="{{ route('statement.regular.session') }}">
                                        All Regular Session
                                    </a>
                                </li>
                                <li>
                                    <a class="nav-link {{ request()->routeIs('statement.review.session') ? 'text-primary' : '' }}"
                                        href="{{ route('statement.review.session') }}">
                                        All Review Session
                                    </a>
                                </li>
                                <li>
                                    <a class="nav-link {{ request()->routeIs('statement.review.extra.session') ? 'text-primary' : '' }}"
                                        href="{{ route('statement.review.extra.session') }}">
                                        All Review Session Extra
                                    </a>
                                </li>
                                <li>
                                    <a class="nav-link {{ request()->routeIs('statement.special.session') ? 'text-primary' : '' }}"
                                        href="{{ route('statement.special.session') }}">
                                        All Special Session
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif


                    {{-- Committee Teacher Manage --}}
                    @if(Auth::user()->can('teacher.menu'))
                        <li class="nav-parent {{ request()->routeIs('teacher.*') ? 'nav-expanded nav-active' : '' }}">
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-user-tie" aria-hidden="true"></i>
                                <span>Committee Members (Faculty)</span>
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
                                <span>Committee Members (Staff)</span>
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
                                <span>Personal Statements</span>
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
                                @can('report.review.extra.session')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('report.review.extra.session') ? 'text-primary' : '' }}"
                                            href="{{ route('report.review.extra.session') }}">
                                            All Review Session Extra
                                        </a>
                                    </li>
                                @endcan
                                @can('report.special.session')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('report.special.session') ? 'text-primary' : '' }}"
                                            href="{{ route('report.special.session') }}">
                                            All Special Session
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif

                    {{-- Roles And Permission --}}
                    @if(Auth::user()->can('role_permission.menu'))
                        <li
                            class="nav-parent {{ request()->routeIs('permission.*') || request()->routeIs('roles.*') ? 'nav-expanded nav-active' : '' }}">
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
                        <li
                            class="nav-parent {{ request()->routeIs('role.assignments.*') ? 'nav-expanded nav-active' : '' }}">
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

                    {{-- Special Review --}}

                    @if(Auth::user()->can('session.menu'))
                        <li class="nav-parent {{ request()->routeIs('session.*') ? 'nav-expanded nav-active' : '' }}">
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                                <span>Session</span>
                            </a>
                            <ul class="nav nav-children">

                                @can('session.all')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('session.all') ? 'text-primary' : '' }}"
                                            href="{{ route('session.all') }}">
                                            All Session
                                        </a>
                                    </li>
                                @endcan
                                @can('session.add')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('session.add') ? 'text-primary' : '' }}"
                                            href="{{ route('session.add') }}">
                                            Add Session
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endif



                    @if(Auth::user()->can('contributors.menu'))
                        <li class="nav-parent {{ request()->routeIs('contributors.*') ? 'nav-expanded nav-active' : '' }}">
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-people-group" aria-hidden="true"></i>
                                <span>Contributors</span>
                            </a>
                            <ul class="nav nav-children">

                                @can('contributors.all')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('contributors.all') ? 'text-primary' : '' }}"
                                            href="{{ route('contributors.all') }}">
                                            All Contributors
                                        </a>
                                    </li>
                                @endcan
                                @can('contributors.add')
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('contributors.add') ? 'text-primary' : '' }}"
                                            href="{{ route('contributors.add') }}">
                                            Add Contributor
                                        </a>
                                    </li>
                                @endcan


                            </ul>
                        </li>
                    @endif

                    {{-- System Setting --}}
                    @if(Auth::user()->can('system_setting.menu') || Auth::user()->hasRole('SuperAdmin'))
                        <li class="nav-parent {{ request()->routeIs('rate_head.*') || request()->routeIs('github_deploy.*') || request()->routeIs('permission_sync.*') ? 'nav-expanded nav-active' : '' }}">
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                                <span>System Setting</span>
                            </a>
                            <ul class="nav nav-children">
                                @if(Auth::user()->can('rate_head.all') || Auth::user()->hasRole('SuperAdmin'))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('rate_head.*') ? 'text-primary' : '' }}"
                                            href="{{ route('rate_head.all') }}">
                                            Rate Head
                                        </a>
                                    </li>
                                @endif
                                @if(Auth::user()->can('github_deploy.view') || Auth::user()->hasRole('SuperAdmin'))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('github_deploy.*') ? 'text-primary' : '' }}"
                                            href="{{ route('github_deploy.index') }}">
                                            Github Deploy
                                        </a>
                                    </li>
                                @endif
                                @if(Auth::user()->can('permission_sync.view') || Auth::user()->hasRole('SuperAdmin'))
                                    <li>
                                        <a class="nav-link {{ request()->routeIs('permission_sync.*') ? 'text-primary' : '' }}"
                                            href="{{ route('permission_sync.index') }}">
                                            Permission Sync
                                        </a>
                                    </li>
                                @endif
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