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

        @php
            $rate_head = \App\Models\RateHead::where('order_no', 1)->first();
            $session = \App\Models\Session::where('ugr_id', $sid)->where('exam_type_id', 1)->first();
            $rate_amount = $session
                ? \App\Models\RateAmount::where('session_id', $session->id)->where('saved', 1)->first()
                : null;
        @endphp

        @if($rate_amount)
            @include('committee_input.patritals.regular.list_moderation_committe')
        @else
            <div class="alert alert-info">
                Moderation Committee already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}. If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif

        {{--@include('committee_input.patritals.regular.list_moderation_committe_test')--}}
        @php
            $rate_head = \App\Models\RateHead::where('order_no', 2)->first();
            $session = \App\Models\Session::where('ugr_id', $sid)->where('exam_type_id', 1)->first();
            $rate_amount = $session
                ? \App\Models\RateAmount::where('session_id', $session->id)->where('saved', 1)->first()
                : null;
        @endphp

        @if($rate_amount)
            @include('committee_input.patritals.regular.list_paper_setter_examineer')
        @else
            <div class="alert alert-info">
                List of Examiners  Committee already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}. If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif


        @php
            $rate_head = \App\Models\RateHead::where('order_no', 4)->first();
            $session = \App\Models\Session::where('ugr_id', $sid)->where('exam_type_id', 1)->first();
            $rate_amount = $session
                ? \App\Models\RateAmount::where('session_id', $session->id)->where('saved', 1)->first()
                : null;
        @endphp

        @if($rate_amount)
            @include('committee_input.patritals.regular.list_class_test_teacher')
        @else
            <div class="alert alert-info">
                List of Class Test  Committee already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}. If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif

        @php
            $rate_head = \App\Models\RateHead::where('order_no', 5)->first();
            $session = \App\Models\Session::where('ugr_id', $sid)->where('exam_type_id', 1)->first();
            $rate_amount = $session
                ? \App\Models\RateAmount::where('session_id', $session->id)->where('saved', 1)->first()
                : null;
        @endphp

        @if(!$rate_amount)
            @include('committee_input.patritals.regular.list_sessional_course_teacher')
        @else
            <div class="alert alert-info">
                List of Sessional  Committee already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}. If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif


        {{--@include('committee_input.patritals.regular.list_moderation_committe_test')--}}

        @php
            $rate_head = \App\Models\RateHead::where('order_no', 9)->first();
            $session = \App\Models\Session::where('ugr_id', $sid)->where('exam_type_id', 1)->first();
            $rate_amount = $session
                ? \App\Models\RateAmount::where('session_id', $session->id)->where('saved', 1)->first()
                : null;
        @endphp

        @if(!$rate_amount)
            @include('committee_input.patritals.regular.list_scrutinizers')
        @else
            <div class="alert alert-info">
                List of Scrutinizers  Committee already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}. If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif


        @php
            $rate_head = \App\Models\RateHead::where('order_no', '=','8.a')->first();
            $session = \App\Models\Session::where('ugr_id', $sid)->where('exam_type_id', 1)->first();
            $rate_amount = $session
                ? \App\Models\RateAmount::where('session_id', $session->id)
                ->where('saved', 1)
                ->where('rate_head_id',$rate_head->id)
                ->where('exam_type_id', 1)->first()
                : null;
        @endphp


        @if($rate_amount)
            @include('committee_input.patritals.regular.list_preparation_theory_grade_sheet')
        @else
            <div class="alert alert-info">
                List of Theory Grade Sheet Committee already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}. If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif


        @php
            $rate_head = \App\Models\RateHead::where('order_no', '=', '8.b')->first();
            $session = \App\Models\Session::where('ugr_id', $sid)->where('exam_type_id', 1)->first();

           $rate_amount = ($session && $rate_head)
               ? \App\Models\RateAmount::where('session_id', $session->id)
                   ->where('saved', 1)
                   ->where('rate_head_id', $rate_head->id)
                   ->where('exam_type_id', 1)
                   ->first()
               : null;
        @endphp


        @if(!$rate_amount)
            @include('committee_input.patritals.regular.list_preparation_sessional_grade_sheet')
        @else
            <div class="alert alert-info">
                List of Sessional Grade Sheet Committee already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}. If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif


        @php
            $rate_head = \App\Models\RateHead::where('order_no', '=', '10.a')->first();
            $session = \App\Models\Session::where('ugr_id', $sid)->where('exam_type_id', 1)->first();

           $rate_amount = ($session && $rate_head)
               ? \App\Models\RateAmount::where('session_id', $session->id)
                   ->where('saved', 1)
                   ->where('rate_head_id', $rate_head->id)
                   ->where('exam_type_id', 1)
                   ->first()
               : null;
        @endphp


        @if(!$rate_amount)
            @include('committee_input.patritals.regular.list_scrutinizing_theory_grade_sheet')
        @else
            <div class="alert alert-info">
                List of Scrutinizing Theoritical Grade Sheet Committee already saved for {{$session->session}} Year/{{$session->year}} Semester/{{$session->semester}}. If any update is needed, go to <strong>Committee Record Manage → Select Session</strong>.
            </div>
        @endif


        @include('committee_input.patritals.regular.list_scrutinizing_sessional_grade_sheet')

        @include('committee_input.patritals.regular.list_prepared_computerized_result')

        @include('committee_input.patritals.regular.list_verified_computerized_grade_sheet')

        @include('committee_input.patritals.regular.list_stencil_cutting_question_paper')
        @include('committee_input.patritals.regular.list_printing_question_paper')
        @include('committee_input.patritals.regular.list_comparison_question_paper')
        @include('committee_input.patritals.regular.list_advisor_student')
        @include('committee_input.patritals.regular.list_verified_final_graduation_result')
        @include('committee_input.patritals.regular.list_conducted_central_oral_examination')
        @include('committee_input.patritals.regular.list_involved_survey')
        @include('committee_input.patritals.regular.list_conducted_priliminary_viva')
        @include('committee_input.patritals.regular.list_examined_thesis_project')
        @include('committee_input.patritals.regular.list_conducted_oral_examination')
        @include('committee_input.patritals.regular.list_supervised_thesis_project')
        @include('committee_input.patritals.regular.list_honorarium_coordinator')
        @include('committee_input.patritals.regular.list_honorarium_chairman')



        <!-- end: page -->
    </section>

@endsection
<!-- Add Script Data(You can write it any javascript file and than just import this js) -->
<!-- this will be fire for any 'delete' class element[const target = event.target.closest('.delete');] -->
@push('scripts')

@endpush


