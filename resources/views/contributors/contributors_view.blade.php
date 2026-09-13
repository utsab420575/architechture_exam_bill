@extends('layouts.app')

@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Team Members</h2>
            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li><span>About</span></li>
                    <li><span>Contributors</span></li>
                </ol>
                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>

        <div class="row mb-4">
            <div class="col-12 text-center">
                <h3 class="mb-2" style="letter-spacing:.5px;">TEAM MEMBER</h3>
                <div style="width:80px;height:2px;background:#d9d9d9;margin:10px auto 16px;"></div>
                <p class="text-muted" style="max-width:720px;margin:0 auto;">
                    The people who contributed to building this project.
                </p>
            </div>
        </div>

        <div class="row">
            @foreach($contributors as $c)
                @php
                    $src = $c->photo
                        ? ( Str::startsWith($c->photo, ['http://','https://']) ? $c->photo
                            : (file_exists(public_path($c->photo)) ? asset($c->photo) : asset('upload/no_image.jpg')) )
                        : asset('upload/no_image.jpg');
                @endphp

                <div class="col-12 col-md-4 mb-3">
                    <div class="contrib card border-0 shadow-sm h-100">
                        <div class="contrib__top">
                            <img class="contrib__avatar" src="{{ $src }}" alt="{{ $c->name }}">
                        </div>

                        <div class="contrib__body">
                            <h4 class="contrib__name mb-1">{{ $c->name }}</h4>
                            <div class="contrib__designation mb-3">{{ $c->designation }}</div>

                            @if($c->speech)
                                <p class="contrib__speech mb-3">
                                    {{ \Illuminate\Support\Str::limit($c->speech, 160) }}
                                </p>
                            @else
                                <p class="contrib__speech text-muted mb-3">
                                    &ldquo;Thanks for visiting the contributors page.&rdquo;
                                </p>
                            @endif

                            <div class="d-flex align-items-center gap-2">
                                @if($c->profile)
                                    <a href="{{ $c->profile }}" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-outline-light">
                                        <i class="bx bx-link-external"></i> Profile
                                    </a>
                                @endif
                                {{--<span class="badge bg-secondary">Contributor</span>--}}
                            </div>
                        </div>

                        <div class="contrib__shape"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection

@push('styles')
    <style>
        /* Card shell */
        .contrib{
            position: relative;
            background: #0b1630;      /* deep navy */
            color: #f2f5ff;
            border-radius: 28px;
            overflow: hidden;         /* safe now, avatar is fully inside */
        }

        /* Decorative blob (optional) */
        .contrib__shape{
            position: absolute;
            left: -60px;
            bottom: -60px;
            width: 220px;
            height: 220px;
            background: rgba(255,255,255,.03);
            border-radius: 48px 120px 120px 120px;
            pointer-events: none;
        }

        /* Top section now just adds padding */
        .contrib__top{
            padding: 18px 24px 0 24px;   /* top/left padding for avatar */
        }

        /* Avatar sits in normal flow (fully visible) */
        .contrib__avatar{
            display: block;
            width: 96px;
            height: 96px;
            border-radius: 999px;
            object-fit: cover;
            background: #0b1630;
            outline: 6px solid #e9eef9;  /* halo ring */
            box-shadow: 0 8px 16px rgba(6,12,30,.25);
        }

        /* Body */
        .contrib__body{
            padding: 14px 24px 24px 24px;
            min-height: 200px;
        }

        /* Typography */
        .contrib__name{ font-weight: 800; letter-spacing: .2px; }
        .contrib__designation{ opacity: .9; font-weight: 600; letter-spacing: .3px; }
        .contrib__speech{ font-size: .95rem; line-height: 1.55; }

        /* Button on dark card */
        .btn-outline-light{ border-color: rgba(255,255,255,.35); color: #fff; }
        .btn-outline-light:hover{ background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.55); color:#fff; }

        /* Hover lift */
        .contrib:hover{
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(6,12,30,.28);
            transition: transform .15s ease, box-shadow .15s ease;
        }

        /* Responsive tweaks */
        @media (max-width: 575.98px){
            .contrib__top{ padding: 16px 16px 0 16px; }
            .contrib__avatar{ width: 84px; height: 84px; }
            .contrib__body{ padding: 12px 16px 18px 16px; }
        }

    </style>
@endpush