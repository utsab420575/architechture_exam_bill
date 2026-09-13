@extends('layouts.app')
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Dashboard</h2>
            <div class="right-wrapper text-end">
                {{--<ol class="breadcrumbs">
                    <li>
                        <a href="index.html">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>
                    --}}{{--<li><span>Layouts</span></li>
                    <li><span>Dark Header</span></li>--}}{{--
                </ol>--}}
                {{--<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>--}}
            </div>
        </header>
        <!-- start: page -->
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="row mb-3">
                    <div class="col-12 col-md-6 col-xl-6">
                        <section class="card card-featured-left card-featured-primary mb-3">
                            <div class="card-body">
                                <div class="widget-summary d-flex align-items-center">
                                    <!-- Icon Column -->
                                    <div class="widget-summary-col widget-summary-col-icon">
                                        <div class="summary-icon bg-primary">
                                            <i class="fa-solid fa-person-chalkboard"></i>
                                        </div>
                                    </div>

                                    <!-- Text Column -->
                                    <div class="widget-summary-col flex-grow-1 d-flex align-items-center" style="padding-left: 100px;">
                                        <div class="summary">
                                            <h4 class="title">Architecture Department Teachers</h4>
                                            <div class="info">
                                                <strong class="amount">{{ $teachers_count }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-12 col-md-6 col-xl-6">
                        <section class="card card-featured-left card-featured-secondary">
                            <div class="card-body">
                                <div class="widget-summary d-flex">
                                    <div class="widget-summary-col widget-summary-col-icon">
                                        <div class="summary-icon bg-secondary">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                    </div>
                                    <div class="widget-summary-col d-flex align-items-center justify-content-start" style="padding-left: 100px;">
                                        <div class="summary">
                                            <h4 class="title">Employee Count </h4>
                                            <div class="info">
                                                <strong class="amount">{{$employee_count}}</strong>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

            </div>
        </div>
        <!-- end: page -->
    </section>
@endsection
