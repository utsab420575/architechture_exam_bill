<aside id="sidebar-left" class="sidebar-left">
    <div class="sidebar-header">
        <div class="sidebar-title">Prepare Exam Bill</div>
        <div class="sidebar-toggle d-none d-md-block" data-toggle-class="sidebar-left-collapsed" data-target="html"
             data-fire-event="sidebar-left-toggle">
            <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
        </div>
    </div>
    <div class="nano">
        <div class="nano-content">
            <nav id="menu" class="nav-main" role="navigation">
                <ul class="nav nav-main">
                    <li>
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="bx bx-home-alt" aria-hidden="true"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-parent">
                        <a class="nav-link" href="#">
                            <i class="fa-solid fa-file-import" aria-hidden="true"></i>
                            <span>Import/Export Manage</span>
                        </a>
                        <ul class="nav nav-children">
                            <li><a class="nav-link" href="{{ route('import.table.all') }}">All Table Import</a></li>
                        </ul>
                    </li>

                    <li class="nav-parent">
                        <a class="nav-link" href="#">
                            <i class="fa-solid fa-money-check-dollar" aria-hidden="true"></i>
                            <span>Committee Input Manage</span>
                        </a>
                        <ul class="nav nav-children">
                            <li><a class="nav-link" href="{{ route('committee.input.regular.session') }}">All Regular Session</a></li>
                            <li><a class="nav-link" href="{{ route('committee.input.review.session') }}">All Review Session</a></li>
                        </ul>
                    </li>

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

                    <li class="nav-parent">
                        <a class="nav-link" href="#">
                            <i class="fa-solid fa-user-tie" aria-hidden="true"></i>
                            <span>Committee Teacher Manage</span>
                        </a>
                        <ul class="nav nav-children">
                            <li><a class="nav-link" href="{{ route('teacher.all') }}">All Teacher</a></li>
                            <li><a class="nav-link" href="{{ route('teacher.add') }}">Add Teacher</a></li>
                        </ul>
                    </li>

                    <li class="nav-parent">
                        <a class="nav-link" href="#">
                            <i class="fa-solid fa-users" aria-hidden="true"></i>
                            <span>Committee Employee Manage</span>
                        </a>
                        <ul class="nav nav-children">
                            <li><a class="nav-link" href="{{ route('employee.all') }}">All Employee</a></li>
                            <li><a class="nav-link" href="{{ route('employee.add') }}">Add Employee</a></li>
                        </ul>
                    </li>

                    <li class="nav-parent">
                        <a class="nav-link" href="#">
                            <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
                            <span>Committee Report Manage</span>
                        </a>
                        <ul class="nav nav-children">
                            <li><a class="nav-link" href="{{ route('report.regular.session') }}">All Regular Session</a></li>
                            <li><a class="nav-link" href="{{ route('report.review.session') }}">All Review Session</a></li>
                        </ul>
                    </li>

                    <li class="nav-parent">
                        <a class="nav-link" href="#">
                            <i class="fa-solid fa-toolbox" aria-hidden="true"></i>
                            <span>Roles And Permission</span>
                        </a>
                        <ul class="nav nav-children">
                            <li><a class="nav-link" href="{{ route('permission.all') }}">All Permission</a></li>
                            <li><a class="nav-link" href="{{ route('roles.all') }}">All Roles</a></li>
                            <li><a class="nav-link" href="{{ route('roles.permission.all') }}">All Roles in Permission</a></li>
                            <li><a class="nav-link" href="{{ route('roles.permissions.add') }}">Roles in Permission</a></li>
                        </ul>
                    </li>

                    <li class="nav-parent">
                        <a class="nav-link" href="#">
                            <i class="fa-solid fa-lock-open" aria-hidden="true"></i>
                            <span>Setting Admin User</span>
                        </a>
                        <ul class="nav nav-children">
                            <li><a class="nav-link" href="{{ route('role.assignments.all') }}">All User</a></li>
                        </ul>
                    </li>
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
