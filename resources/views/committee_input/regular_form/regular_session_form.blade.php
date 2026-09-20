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
            <h2>Regular Session All Form(Session:{{$session_info['session']}}-{{$session_info['year']}}
                /{{$session_info['semester']}})</h2>
            <div class="right-wrapper text-end">
                <ol class="breadcrumbs">
                    <li>
                        <a href="{{route('dashboard')}}">
                            <i class="bx bx-home-alt"></i>
                        </a>
                    </li>
                    <li><span>Committee Input</span></li>
                    <li><span>Regular Session</span></li>
                </ol>
                <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a>
            </div>
        </header>
        <!-- start: page -->

        {{--Examination Moderation Committee--}}
        @include('committee_input.patritals.regular.list_moderation_committe')




        {{-- 2,3 order are combined in a blade paper setter,examiner--}}
        @if($session_info->year==6&& $session_info->semester==3)
            @include('committee_input.patritals.regular.list_paper_setter_examineer_6_3')
        @else
            @include('committee_input.patritals.regular.list_paper_setter_examineer')
        @endif


        {{--4 Internal Assessment/Class Test @**/- per class test per student--}}
        @if($session_info->year!=6&& $session_info->semester!=3)
            @include('committee_input.patritals.regular.list_class_test_teacher')
        @endif

        {{--5 Sessional (@ ***/- per contact hour per week; min ****/- per examiner)--}}
        @if($session_info->year!=6&& $session_info->semester!=3)
            @include('committee_input.patritals.regular.list_sessional_course_teacher')
        @endif



        @include('committee_input.patritals.regular.list_scrutinizers')



        @include('committee_input.patritals.regular.list_preparation_theory_grade_sheet')



        @if($session_info->year!=6&& $session_info->semester!=3)
            @include('committee_input.patritals.regular.list_preparation_sessional_grade_sheet')
        @endif


        @include('committee_input.patritals.regular.list_default_input')

        {{--10.a List of Teachers for the Scrutinizing of Grade Sheet (Theoretical) (@**/- per student per subject)--}}
        @include('committee_input.patritals.regular.list_scrutinizing_theory_grade_sheet')



        {{--10.b List of Teachers for the Scrutinizing of Grade Sheet(Sessional) (@ **- per student per subject):--}}
        @if($session_info->year!=6&& $session_info->semester!=3)
            @include('committee_input.patritals.regular.list_scrutinizing_sessional_grade_sheet')
        @endif


        {{--8.d List of Teachers Prepared Computerized Result (@ **/- per student per subject)--}}
        @include('committee_input.patritals.regular.list_prepared_computerized_result')


        {{--8.c List of Teachers Verified Computerized Grade Sheets & GPA List (@**/- per student)--}}
        @include('committee_input.patritals.regular.list_verified_computerized_grade_sheet')

        {{--8.e Tabulation (@**/- per student)--}}
        @include('committee_input.patritals.regular.list_tabulation')



        @include('committee_input.patritals.regular.list_stencil_cutting_question_paper')


        @include('committee_input.patritals.regular.list_printing_question_paper')



        @include('committee_input.patritals.regular.list_comparison_question_paper')


        {{-- order-13:not done--}}
        @include('committee_input.patritals.regular.list_advisor_student')

        {{--order 16--}}
        @if($session_info->year == 5 && $session_info->semester == 2)
            @include('committee_input.patritals.regular.list_verified_final_graduation_result')
        @endif



        {{-- order-7.e--}}

        @include('committee_input.patritals.regular.list_conducted_central_oral_examination')


        {{-- order-7.f--}}

        @include('committee_input.patritals.regular.list_involved_survey')

        {{-- order-7.g--}}

        @include('committee_input.patritals.regular.list_involved_industrial_attachment')


        {{-- order-6.c--}}
        @if($session_info->year == 5 && $session_info->semester == 2)
            @include('committee_input.patritals.regular.list_conducted_priliminary_viva')
        @endif

        {{-- order-6.a--}}
        @if($session_info->year == 5 && $session_info->semester == 2)
            @include('committee_input.patritals.regular.list_examined_thesis_project')
        @endif

        {{-- order-6.d--}}
        @if($session_info->year == 5 && $session_info->semester == 2)
            @include('committee_input.patritals.regular.list_conducted_oral_examination')
        @endif

        {{-- order-6.b--}}
        @if($session_info->year == 5 && $session_info->semester == 2)
            @include('committee_input.patritals.regular.list_supervised_thesis_project')
        @endif





        {{-- order-17--}}
        @if(!($session_info->year == 5 && $session_info->semester == 1))
            @include('committee_input.patritals.regular.list_course_profile')
        @endif


        {{-- order-14--}}
        @include('committee_input.patritals.regular.list_honorarium_coordinator')


        {{-- order-15--}}
        @include('committee_input.patritals.regular.list_honorarium_chairman')




        {{--@include('committee_input.patritals.regular.list_moderation_committe_test')--}}

        <!-- end: page -->
    </section>

@endsection
<!-- Add Script Data(You can write it any javascript file and than just import this js) -->
<!-- this will be fire for any 'delete' class element[const target = event.target.closest('.delete');] -->
@push('scripts')

@endpush


