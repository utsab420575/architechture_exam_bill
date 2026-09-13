@extends('layouts.app')
@section('styles')
    <style>
        #table-list-of-examination-committee td {
            transition: background-color 0.6s ease-in-out, opacity 0.6s ease-in-out;
        }

        .fade-green {
            background-color: #68a17a !important;
            opacity: 1;
        }

        .fade-out {
            opacity: 0.3;
        }

    </style>
    @stack('styles')

@endsection
@section('content')
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Review Session All Form(Session:{{$session_info['session']}}-{{$session_info['year']}}/{{$session_info['semester']}})</h2>
            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="index.html">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>
                    <li><span>Committee Input</span></li>
                    <li><span>Review Session Session</span></li>
                </ol>
                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>
        <!-- start: page -->

        @php
            use App\Models\RateAmount;
            $session = \App\Models\Session::where('ugr_id', $sid)->where('exam_type_id', 1)->first();
            $session_info=(object)$session_info;
        @endphp

        @if(!RateAmount::isRateAmountSaved($sid, 2, '1'))
            @include('committee_input.patritals.review.list_moderation_committe')
        @else
            <div class="alert alert-info">
                List of  Examination Committee already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}.
                If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif

        @if(!RateAmount::isRateAmountSaved($sid, 2, '2'))
            @include('committee_input.patritals.review.list_paper_setter_examineer')
        @else
            <div class="alert alert-info">
                List of Examiners Committee already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}.
                If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif


        @if(!RateAmount::isRateAmountSaved($sid, 2, '9'))
            @include('committee_input.patritals.review.list_scrutinizers')
        @else
            <div class="alert alert-info">
                List of  Scrutinizers already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}.
                If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif

        @if(!RateAmount::isRateAmountSaved($sid, 2, '8.a'))
            @include('committee_input.patritals.review.list_preparation_theory_grade_sheet')
        @else
            <div class="alert alert-info">
                List of  Preparation Theory Grade already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}.
                If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif

        @if(!RateAmount::isRateAmountSaved($sid, 2, '10.a'))
            @include('committee_input.patritals.review.list_scrutinizing_theory_grade_sheet')
        @else
            <div class="alert alert-info">
                List of  Scrutinizing of Grade Sheet(Theoretical) already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}.
                If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif


        @if(!RateAmount::isRateAmountSaved($sid, 2, '12.a'))
            @include('committee_input.patritals.review.list_stencil_cutting_question_paper')
        @else
            <div class="alert alert-info">
                List of  Stencill Cutting  already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}.
                If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif

        @if(!RateAmount::isRateAmountSaved($sid, 2, '12.b'))
            @include('committee_input.patritals.review.list_printing_question_paper')
        @else
            <div class="alert alert-info">
                List of  Printing of Question paper already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}.
                If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif

        @if(!RateAmount::isRateAmountSaved($sid, 2, '11'))
            @include('committee_input.patritals.review.list_comparison_question_paper')
        @else
            <div class="alert alert-info">
                List of  Comparison,Correction,sketching already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}.
                If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif
        @if(!RateAmount::isRateAmountSaved($sid, 2, '15'))
            @include('committee_input.patritals.review.list_honorarium_chairman')
        @else
            <div class="alert alert-info">
                List of  Honorarium for Chairman already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}.
                If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif























        <!-- end: page -->
    </section>

@endsection
<!-- Add Script Data(You can write it any javascript file and than just import this js) -->
<!-- this will be fire for any 'delete' class element[const target = event.target.closest('.delete');] -->
@push('scripts')

@endpush


