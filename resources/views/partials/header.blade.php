
<header class="header">
    <style>
        /* Simple dark button: black + gray */
        /* Simple dark pill with animated border fill */
        /* Base: subtle border, dark pill */
        .btn-contributors{
            --bg:#1a1a1a;
            --bg-hover: #ab2c2c;
            --bg-active:#141414;
            --text:#f5f5f5;
            --border:rgba(255,255,255,.16);   /* always-visible thin border */
            --border-strong:rgba(255,255,255,.55); /* animated border color */
            --ring:rgba(255,255,255,.18);

            position:relative;
            display:inline-flex;
            align-items:center;
            gap:.5rem;

            background:var(--bg);
            color:var(--text) !important;
            border:1px solid var(--border);        /* <-- stays visible */
            border-radius:999px;
            padding:.6rem 1.1rem;
            font-weight:700;
            letter-spacing:.2px;
            line-height:1;
            box-shadow:0 1px 2px rgba(0,0,0,.35);
            transition:background .15s ease, transform .12s ease, box-shadow .15s ease;
        }

        /* Icon */
        .btn-contributors .bx{ font-size:1.1rem; vertical-align:middle; }

        /* Animated border layer (hidden by default) */
        .btn-contributors::before{
            content:"";
            position:absolute;
            inset:-2px;                     /* sit just outside the base border */
            border:2px solid var(--border-strong);
            border-radius:inherit;
            pointer-events:none;
            opacity:1;
            transform:scaleX(0);            /* start collapsed */
            transform-origin:left center;   /* draw left -> right */
            transition:transform .35s ease;
        }

        /* Fill effect on hover/focus/active page */
        .btn-contributors:hover{ background:var(--bg-hover); }
        .btn-contributors:hover::before{ transform:scaleX(1); }

        .btn-contributors:active{ background:var(--bg-active); }
        .btn-contributors:focus-visible,
        .btn-contributors.is-active{
            outline:2px solid var(--ring);
            outline-offset:2px;
        }
        .btn-contributors:focus-visible::before,
        .btn-contributors.is-active::before{ transform:scaleX(1); }

        /* Compact on xs */
        @media (max-width:576px){
            .btn-contributors span{ display:none; }
            .btn-contributors{ padding:.55rem .7rem; }
        }



    </style>

    <div class="logo-container">
        <a href="../4.3.0" class="logo">
            <img src="{{asset('backend/assets/img/logo3.png')}}" width="75" height="35" alt="Porto Admin" />
        </a>
        <div class="d-md-none toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
            <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
        </div>
    </div>


    <!-- start: search & user box -->
    <div class="header-right">


        @php
            $id = Auth::user()->id;
            $userData = App\Models\User::find($id);
        @endphp
        {{--<span class="separator"></span>--}}


        <div id="userbox" class="userbox pt-2 text-center">
            <a href="{{route('contributors.view')}}" class="btn-contributors">
                <i class="fa fa-users me-2"></i> Contributors
            </a>
        </div>

        <div id="userbox" class="userbox pt-2">
            <a href="#" data-bs-toggle="dropdown">
                <figure class="profile-picture">
                    <img src="{{ (!empty($userData->photo)) ? url($userData->photo) : url('upload/no_image.jpg') }}" alt="user-image" class="rounded-circle">
                    {{--<img src="{{asset('backend/assets/img/!logged-user.jpg')}}" alt="Joseph Doe" class="rounded-circle" data-lock-picture="{{asset('backend/assets/img/!logged-user.jpg')}}" />--}}
                </figure>
                <div class="profile-info" data-lock-name="Exam Bill" data-lock-email="johndoe@okler.com">
                    <span class="name">{{$userData->name}}</span>
                    {{--<span class="role">Administrator</span>--}}
                </div>
                <i class="fa custom-caret"></i>
            </a>
            <div class="dropdown-menu">
                <ul class="list-unstyled mb-2">

                    <li   class="pt-4">
                        <a role="menuitem" tabindex="-1" href="{{route('user.profile')}}"><i class="bx bx-user-circle"></i> My Profile</a>
                    </li>

                    <li>
                        <a role="menuitem" tabindex="-1" href="{{route('user.password.change')}}"><i class="bx bx-user-circle"></i>Change Password</a>
                    </li>
                    <li class="divider"></li>
                    {{--<li>
                        <a role="menuitem" tabindex="-1" href="#" data-lock-screen="true"><i class="bx bx-lock"></i> Lock Screen</a>
                    </li>--}}
                    <li>
                        <a role="menuitem" tabindex="-1" href="{{route('user.logout')}}"><i class="bx bx-power-off"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>


    </div>
    <!-- end: search & user box -->
</header>
