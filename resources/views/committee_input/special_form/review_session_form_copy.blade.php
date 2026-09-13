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
            <h2>Light Sidebar Layout</h2>
            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="index.html">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>
                    <li><span>Layouts</span></li>
                    <li><span>Light Sidebar</span></li>
                </ol>
                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>
        <!-- start: page -->

        {{--@php
            $rate_head = \App\Models\RateHead::where('order_no', 1)->first();
            $session = \App\Models\Session::where('ugr_id', $sid)->where('exam_type_id', 2)->first();
            $rate_amount = $session
                ? \App\Models\RateAmount::where('session_id', $session->id)->where('saved', 1)->first()
                : null;
        @endphp
        @if($rate_amount)
            @include('committee_input.patritals.review.list_moderation_committe')
        @else
            <div class="alert alert-info">
                Moderation Committee already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}. If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif--}}


        @include('committee_input.patritals.review.list_moderation_committe')

            @include('committee_input.patritals.review.list_paper_setter_examineer')
        @else
            <div class="alert alert-info">
                List of Examiners/Paper Setter  Committee already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}. If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif


        @php
            $rate_head = \App\Models\RateHead::where('order_no', 2)->first();
            $session = \App\Models\Session::where('ugr_id', $sid)->where('exam_type_id', 2)->first();
            $rate_amount = $session
                ? \App\Models\RateAmount::where('session_id', $session->id)->where('saved', 1)->first()
                : null;
        @endphp

        @if($rate_amount)
            @include('committee_input.patritals.review.list_scrutinizers')
        @else
            <div class="alert alert-info">
                List of Scrutinizer  Committee already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}. If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif


        @php
            $rate_head = \App\Models\RateHead::where('order_no', '=','8.a')->first();
            $session = \App\Models\Session::where('ugr_id', $sid)->where('exam_type_id', 2)->first();
            $rate_amount = ($session && $rate_head)
               ? \App\Models\RateAmount::where('session_id', $session->id)
                   ->where('saved', 1)
                   ->where('rate_head_id', $rate_head->id)
                   ->where('exam_type_id', 2)
                   ->first()
               : null;
        @endphp

        @if(!$rate_amount)
            @include('committee_input.patritals.review.list_preparation_theory_grade_sheet')
        @else
            <div class="alert alert-info">
                List of  Theory Grade Sheet  Committee already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}. If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif

        @php
            $rate_head = \App\Models\RateHead::where('order_no', '=','10.a')->first();
            $session = \App\Models\Session::where('ugr_id', $sid)->where('exam_type_id', 2)->first();
            $rate_amount = ($session && $rate_head)
               ? \App\Models\RateAmount::where('session_id', $session->id)
                   ->where('saved', 1)
                   ->where('rate_head_id', $rate_head->id)
                   ->where('exam_type_id', 2)
                   ->first()
               : null;
        @endphp

        @if(!$rate_amount)
            @include('committee_input.patritals.review.list_scrutinizing_theory_grade_sheet')
        @else
            <div class="alert alert-info">
                List of Scrutinizing  Theory Grade Sheet  Committee already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}. If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif


        @include('committee_input.patritals.review.list_stencil_cutting_question_paper')
        @include('committee_input.patritals.review.list_printing_question_paper')
        @include('committee_input.patritals.review.list_comparison_question_paper')

        @include('committee_input.patritals.review.list_honorarium_chairman')





        <!-- end: page -->
    </section>

@endsection
<!-- Add Script Data(You can write it any javascript file and than just import this js) -->
<!-- this will be fire for any 'delete' class element[const target = event.target.closest('.delete');] -->
@push('scripts')

@endpush


