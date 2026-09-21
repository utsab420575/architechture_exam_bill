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

        {{--order -1--}}
       @include('committee_input.patritals.review.list_moderation_committe')


        {{--order-2,3--}}

            @include('committee_input.patritals.review.list_paper_setter_examineer')



        {{--order-9--}}
        @include('committee_input.patritals.review.list_scrutinizers')


        {{--order-8.a--}}
        @include('committee_input.patritals.review.list_preparation_theory_grade_sheet')


        {{--order-10.a--}}
        @include('committee_input.patritals.review.list_scrutinizing_theory_grade_sheet')

        {{--8.d List of Teachers Prepared Computerized Result (@ **/- per student per subject)--}}
        @include('committee_input.patritals.review.list_prepared_computerized_result')

        {{--8.c List of Teachers Verified Computerized Grade Sheets & GPA List (@**/- per student)--}}
        @include('committee_input.patritals.review.list_verified_computerized_grade_sheet')

        {{--8.e Tabulation (@**/- per student)--}}
        @include('committee_input.patritals.review.list_tabulation')


        {{--order-12.a--}}
        @include('committee_input.patritals.review.list_stencil_cutting_question_paper')


        {{--order-12.b--}}

        @include('committee_input.patritals.review.list_printing_question_paper')


        {{--order-11--}}
        @include('committee_input.patritals.review.list_comparison_question_paper')

        {{--order-15--}}
        @include('committee_input.patritals.review.list_honorarium_chairman')























   <!-- end: page -->
</section>

@endsection
<!-- Add Script Data(You can write it any javascript file and than just import this js) -->
<!-- this will be fire for any 'delete' class element[const target = event.target.closest('.delete');] -->
@push('scripts')

@endpush


