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
            <h2>Special Session All Form(Session:{{$session_info['session']}}-{{$session_info['year']}}/{{$session_info['semester']}})</h2>
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
       @include('committee_input.patritals.special.list_moderation_committe')


        {{--order-2,3--}}

            @include('committee_input.patritals.special.list_paper_setter_examineer')



        {{--order-9--}}
        @include('committee_input.patritals.special.list_scrutinizers')


        {{--order-8.a--}}
        @include('committee_input.patritals.special.list_preparation_theory_grade_sheet')


        {{--order-10.a--}}
        @include('committee_input.patritals.special.list_scrutinizing_theory_grade_sheet')



        {{--order-12.a--}}
        @include('committee_input.patritals.special.list_stencil_cutting_question_paper')


        {{--order-12.b--}}

        @include('committee_input.patritals.special.list_printing_question_paper')


        {{--order-12.b--}}
        @include('committee_input.patritals.special.list_comparison_question_paper')

        {{-- order-14--}}
        @include('committee_input.patritals.special.list_honorarium_coordinator')

        {{--order-15--}}
        @include('committee_input.patritals.special.list_honorarium_chairman')























   <!-- end: page -->
</section>

@endsection
<!-- Add Script Data(You can write it any javascript file and than just import this js) -->
<!-- this will be fire for any 'delete' class element[const target = event.target.closest('.delete');] -->
@push('scripts')

@endpush


