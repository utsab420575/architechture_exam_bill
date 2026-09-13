<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Bill Review</title>
    <style>
        @page {
            /*top right bottom left*/
            margin: 5mm 12mm 5mm 12mm;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 12px;
        }

        .header_table, .body_table_1, .footer_table_1 {
            width: 100%;
            border-collapse: collapse;
        }

        .header_table td {
            text-align: center;
            font-size: 13px;
        }

        .body_table_1 th, .body_table_1 td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
        }

        .footer_table_1 {
            margin-top: 50px;
            font-size: 12px;
        }

        .pt-20 { padding-top: 20px; }
        .pt-30 { padding-top: 30px; }
        .pt-40 { padding-top: 40px; }

        td.textstart{
            text-align: left;
        }
        td.textend{
            text-align: right;
        }
        td.textcenter{
            text-align: center;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

@php
    $user = auth()->user();
@endphp

@php
    if ($user->hasRole('Teacher') || $user->hasRole('Admin') || $user->hasRole('SuperAdmin')) {
@endphp
@foreach($teachers as  $teacher)
    @php
        $global_sum=0;
    @endphp

    {{-- Repeatable Header --}}
    <table class="header_table " style=" table-layout: fixed;">
        <colgroup>
            <col style="width: 15%;">
            <col style="width: 35%;">
            <col style="width: 20%;">
            <col style="width: 30%;">
        </colgroup>

        <!-- Header with Logo and University Info -->
        <tr>
            <td colspan="1" style="text-align: right; padding: 20px 0px 0px 0px;">
                <img src="{{ public_path('images/logo_duet.png') }}" style="width: 50px;">
            </td>
            <td colspan="3" style="text-align: left; padding: 20px 0 0 35px;">
                <strong>Dhaka University of Engineering & Technology, Gazipur</strong><br>
                <span style="display: inline-block; margin-left:100px; margin-top: 5px;">
                Gazipur-1707
            </span>
            </td>
        </tr>

        <!-- Section Title -->
        <tr>
            <td colspan="4" style="padding: 10px 0;">
                <div style="margin-left: 5px; font-weight: bold;">
                    (Examination Related Remuneration)
                </div>
            </td>
        </tr>

        <!-- Session Info -->
        @php
            $ordinals = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th', 5 => '5th'];
            $yearText = $ordinals[$session_info->year] ?? $session_info->year . 'th';
            $semesterText = $ordinals[$session_info->semester] ?? $session_info->semester . 'th';
        @endphp
        <tr>
            <td style="text-align: right;padding-right: 10px;">
                B.Arch.
            </td>
            <td>
                <div style="display: flex; justify-content: space-between;">
                    <span>{{ $yearText }} year {{ $semesterText }} semester</span>
                    <span style="font-weight: bold; padding-left: 10px;">Regular</span>
                </div>
            </td>
            <td style="text-align: left;padding-left: 20px;">
                {{ $session_info->session }}
            </td>
            <td style="text-align: left;">
                (Held on: _____________)
            </td>
        </tr>

        <!-- Teacher Info -->
        <tr>
            <td colspan="1" style="padding-top: 15px; text-align: left;padding-left: 10px;">
                <strong>Name:</strong> {{ $teacher->user->name }}
            </td>
            <td colspan="2" style="padding-top: 15px;padding-right:5px;">
                <strong>Designation:</strong> {{ $teacher->designation->designation }}
            </td>
            <td style="padding-top: 15px;">
                <strong>Department:</strong> {{ $teacher->department->shortname }}, DUET
            </td>
        </tr>

        <!-- Section Header -->
        <tr>
            <td colspan="4" style="padding-top: 30px; font-weight: bold;">
                Details of Examination Related Works
            </td>
        </tr>


    </table>

    {{-- Body Table --}}
    <table class="body_table_1" style="margin-top: 10px;">
        <thead>
        <tr>
            <th>Sl. No.</th>
            <th colspan="2">Description of work</th>
            <th>Subject/Course</th>
            <th>Nos. of script/Students</th>
            <th>Rate</th>
            <th>Taka</th>
        </tr>
        </thead>
        <tbody>


        {{-- Order=1 --}}
        @php
            //$assigns_order_1 = $teacher->rateAssigns->where('rateHead.order_no', '1');
            $assigns_order_1 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 2 &&
                       $assign->rateHead &&
                       $assign->rateHead->order_no == '1';
            });
            $total_taka = 0;
            $no_of_item = 0;

            if ($assigns_order_1->isNotEmpty()) {
                foreach ($assigns_order_1 as $assign) {
                    $global_sum += $assign->total_amount ?? 0;
                    $total_taka += $assign->total_amount ?? 0;
                    $no_of_item = $assign->no_of_item ?? 0;
                }
            }

            // Always show default RateHead and RateAmount
            $head = $rateHead_order_1->head ?? 'Moderation';
            $max_rate = $rateAmount_order_1->max_rate ?? ($rateAmount_order_1->default_rate ?? '');
            $min_rate = $rateAmount_order_1->min_rate ?? ($rateAmount_order_1->default_rate ?? '');
        @endphp

        <tr>
            <td rowspan="2">1</td>
            <td class="textstart" colspan="2" rowspan="2">{{ $head }}</td>
            <td rowspan="2"></td>
            <td rowspan="2">{{ $no_of_item == 0 ? '' : $no_of_item }}</td>
            <td class="textend">max. {{ $max_rate !== '' ? number_format($max_rate, 0) : '' }}</td>
            <td rowspan="2" class="textend">{{ $total_taka == 0 ? '' : number_format($total_taka, 2) }}</td>
        </tr>
        <tr>
            <td class="textend">min. {{ $min_rate !== '' ? number_format($min_rate, 0) : '' }}</td>
        </tr>



        {{-- Order = 2 --}}
        @php
             $assigns_order_2 = App\Models\RateAssign::where('teacher_id', $teacher->id)
                                ->where('session_id', $session_info->id)
                                ->whereHas('rateHead', function ($q) {
                                    $q->where('order_no', '2');
                                })->get();
             $total_assigns = $assigns_order_2->count();
             $loopIndex = 0;

             $head = $rateHead_order_2->head ?? 'Paper Setters';
             $default_rate = $rateAmount_order_2->default_rate ?? 0;

             //dd($assigns_order_2);
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assigns_order_2 as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                @endphp
                <tr>
                    @if ($loopIndex == 0)
                        <td rowspan="{{ $total_assigns }}">2</td>
                        <td class="textstart" colspan="2" rowspan="{{ $total_assigns }}">{{ $head }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    <td></td>
                    <td class="textend">{{ isset($default_rate) ? number_format($default_rate, 2) : '' }}</td>
                    <td class="textend">{{ isset($assign->total_amount) ? number_format($assign->total_amount, 2) : '' }}</td>
                </tr>
                @php $loopIndex++; @endphp
            @endforeach
        @else
            {{-- Show default row if no assign exists --}}
            <tr>
                <td rowspan="1">2</td>
                <td class="textstart" colspan="2" rowspan="1">{{ $head }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ isset($default_rate) ? number_format($default_rate, 2) : '' }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif



        {{-- Order = 3 --}}
        @php
            //$assigns_order_3 = $teacher->rateAssigns->where('rateHead.order_no', '3');
             $assigns_order_3 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '3';
               });
            $total_assigns = $assigns_order_3->count();
            $loopIndex = 0;

            $head = $rateHead_order_3->head ?? 'Examiner';
            $default_rate = $rateAmount_order_3->default_rate ?? 0;
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assigns_order_3 as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                @endphp
                <tr>
                    @if ($loopIndex == 0)
                        <td rowspan="{{ $total_assigns }}">3</td>
                        <td class="textstart" colspan="2" rowspan="{{ $total_assigns }}">{{ $head }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    <td>{{$assign->total_students}}/{{ $assign->total_teachers ?? '' }}</td>
                    <td class="textend">{{ isset($default_rate) ? number_format($default_rate, 2) : '' }}</td>
                    <td class="textend">{{ isset($assign->total_amount) ? number_format($assign->total_amount, 2) : '' }}</td>
                </tr>
                @php $loopIndex++; @endphp
            @endforeach
        @else
            {{-- Show default row if no assign exists --}}
            <tr>
                <td rowspan="1">3</td>
                <td class="textstart" colspan="2" rowspan="1">{{ $head }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ number_format($default_rate, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif



        {{-- Order = 4 --}}
        @php
            $head = $rateHead_order_4->head ?? 'Class Test';
        @endphp
            {{-- Fallback row if no data --}}
            <tr>
                <td rowspan="1">4</td>
                <td class="textstart" colspan="2" rowspan="1">{{ $head }}</td>
                <td></td>
                <td></td>
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>






        {{-- Order = 5 --}}
        @php
            $head = $rateHead_order_5->head ?? 'Laboratory/Survey works';
        @endphp
        {{-- Fallback row if no data --}}
        <tr>
            <td rowspan="1">5</td>
            <td class="textstart" colspan="2" rowspan="1">{{ $head }}</td>
            <td></td>
            <td></td>
            {{-- <td class="textend">{{ number_format($default_rate, 2) }}</td>--}}
            <td class="textend"></td>
            <td class="textend"></td>
        </tr>





        {{-- Order 6.a/b/c/d --}}
        @php
           $head = $rateHead_order_6a->head ?? '';
           $sub_head_6a = $rateHead_order_6a->sub_head ?? '';

        @endphp
        <tr>
            <td rowspan="4">6</td>
            <td class="textstart" rowspan="4">{{ $head }}</td>
            <td class="textstart">{{$sub_head_6a}}</td>
            <td></td>
            <td></td>
            <td></td>
            <td class="textend"></td>
        </tr>

        {{-- Order 6.b --}}
        @php
            $sub_head_6b = $rateHead_order_6b->sub_head ?? '6.B';
        @endphp
        <tr>
            <td class="textstart">{{ $sub_head_6b }}</td>
            <td></td>
            <td></td>
            <td class="textend">
            </td>

            <td class="textend"></td>
        </tr>

        {{-- Order 6.c --}}
        @php
            $sub_head_6c = $rateHead_order_6c->sub_head ?? '';
        @endphp
        <tr>
            <td class="textstart">{{ $sub_head_6c }}</td>
            <td></td>
            <td></td>
            <td class="textend">

            </td>
            <td class="textend"></td>
        </tr>

        {{-- Order 6.d --}}
        @php
            $sub_head_6d = $rateHead_order_6d->sub_head ?? '';
        @endphp
        <tr>
            <td class="textstart">{{ $sub_head_6d }}</td>
            <td></td>
            <td></td>
            <td>

            </td>
            <td class="textend"></td>
        </tr>




        {{-- Order 7.e/7.f --}}
        @php
            $head = $rateHead_order_7e->head ?? '';
            $sub_head_7e = $rateHead_order_7e->sub_head ?? '';
        @endphp
        <tr>
            <td rowspan="2">7</td>
            <td class="textstart" rowspan="2">{{ $head }}</td>
            <td class="textstart">{{$sub_head_7e}}</td>
            <td></td>
            <td></td>
            <td class="textend">
            </td>

            <td class="textend"></td>
        </tr>

        {{-- Order 7.f --}}
        @php
            $sub_head_7f = $rateHead_order_7f->sub_head ?? '';
        @endphp
        <tr>
            <td class="textstart">{{ $sub_head_7f }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>






        @php
            //$assigns_order_8a = $teacher->rateAssigns->where('rateHead.order_no', '8.a');
            $assigns_order_8a = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '8.a';
               });
           /* //$assigns_order_8b = $teacher->rateAssigns->where('rateHead.order_no', '8.b');
             $assigns_order_8b = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '8.b';
               });

             $assigns_order_8c = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '8.c';
               })->first();*/




            $total_assigns_8a = $assigns_order_8a->count();


            // Total number of rows for section 8 (8.a + 8.b + 8.c + 8.d)
            $rowspan_8_block = max(1, $total_assigns_8a) + 1 + 1 + 1;

            $head_8a = $rateHead_order_8a->head ?? 'Gradesheet Preparation--';
            $sub_head_8a = $rateHead_order_8a->sub_head ?? 'Theoretical*';
            $rateAmount_8a_default_rate = $rateAmount_order_8a->default_rate ?? '';

            $head_8b = $rateHead_order_8b->head ?? 'Gradesheet Preparation--';
            $sub_head_8b = $rateHead_order_8b->sub_head ?? 'Sessional*';




            $head_8c = $rateHead_order_8c->head ?? 'Empty';


            $head_8d = $rateHead_order_8d->head ?? 'Empty';

        @endphp

        {{-- 8.a rows --}}
        @if ($total_assigns_8a > 0)
            @foreach ($assigns_order_8a as $assign)
                <tr>
                    @if ($loop->first)
                        <td rowspan="{{ $rowspan_8_block }}">8</td>
                        <td class="textstart" rowspan="{{ max(1, $total_assigns_8a) +1 }}">{{ $head_8a }}</td>
                        <td class="textstart" rowspan="{{ $total_assigns_8a }}">{{ $sub_head_8a }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    <td>{{$assign->total_students}}/{{$assign->total_teachers}}</td>
                    <td class="textend">{{ number_format((float)$rateAmount_8a_default_rate, 2) }}</td>
                    <td class="textend">{{ number_format((float)($assign->total_amount ?? 0), 2) }}</td>
                    @php $global_sum += $assign->total_amount ?? 0; @endphp
                </tr>
            @endforeach
        @else
            <tr>
                <td rowspan="{{ $rowspan_8_block }}">8</td>
                <td rowspan="{{ max(1, $total_assigns_8a) + 1 }}" class="textstart">{{ $head_8a }}</td>
                <td class="textstart">{{ $sub_head_8a }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ number_format((float)$rateAmount_8a_default_rate, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif



        {{-- 8.b rows --}}
        <tr>
            <td class="textstart">{{ $sub_head_8b }}</td>
            <td></td>
            <td></td>
            <td class="textend"></td>
            <td class="textend"></td>
        </tr>


        {{-- Order = 8.c --}}
        <tr>
            <td class="textstart" colspan="2">{{ $head_8c }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td class="textend"></td>
        </tr>



        {{-- Order = 8.d --}}
        @php
            $rateHead=\App\Models\RateHead::where('order_no','8.d')->first();
            $head = $rateHead->head;
        @endphp

        {{-- Show default row if no assign exists --}}
        <tr>
            <td class="textstart" colspan="2">{{ $head }}</td>
            <td></td>
            <td></td>
            <td class="textend"></td>
            <td class="textend"></td>
        </tr>







        {{-- Order = 9 --}}
        @php
            //$assigns_order_9 = $teacher->rateAssigns->where('rateHead.order_no', '9');
            $assigns_order_9 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '9';
               });
            $total_assigns = $assigns_order_9->count();
            $loopIndex = 0;

            $head = $rateHead_order_9->head ?? 'Scrutinizing ( Answre Script--)';
            $default_rate = $rateAmount_order_9->default_rate ?? 0;
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assigns_order_9 as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                @endphp
                <tr>
                    @if ($loopIndex == 0)
                        <td rowspan="{{ $total_assigns }}">9</td>
                        <td class="textstart" colspan="2" rowspan="{{ $total_assigns }}">{{ $head }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    <td>{{$assign->total_students}}/{{$assign->total_teachers}}</td>
                    <td class="textend">{{ isset($default_rate) ? number_format($default_rate, 2) : '' }}</td>
                    <td class="textend">{{ isset($assign->total_amount) ? number_format($assign->total_amount, 2) : '' }}</td>
                </tr>
                @php $loopIndex++; @endphp
            @endforeach
        @else
            {{-- Show default row if no assign exists --}}
            <tr>
                <td rowspan="1">9</td>
                <td class="textstart" colspan="2" rowspan="1">{{ $head }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ isset($default_rate) ? number_format($default_rate, 2) : '' }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif






        {{-- Order = 10.a --}}
        @php
            $assign_10_a = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 2 &&
                       $assign->rateHead &&
                       $assign->rateHead->order_no == '10.a';
            });

            $total_assigns = $assign_10_a->count();

            $rateHead=\App\Models\RateHead::where('order_no','10.a')->first();
            $head = $rateHead->head;
            $sub_head_10_a = $rateHead->sub_head;
            $rateAmount_10_a = $rateAmount_order_10_a ?? null;
            $default_rate_10_a = $rateAmount_10_a->default_rate ?? 0;

            $total_student_all_course = 0;
            $total_amount_all_course = 0;
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assign_10_a as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                   /* $total_student_all_course += $assign->total_students ?? 0;*/
                    $total_student_all_course += $assign->no_of_items ?? 0;
                    $total_amount_all_course += $assign->total_amount ?? 0;
                @endphp
            @endforeach
            <tr>
                <td rowspan="2">10</td>
                <td class="textstart" rowspan="2">{{ $head }}</td>
                <td class="textstart">(a) {{ $sub_head_10_a }}</td>
                <td>{{ $total_assigns }} courses</td>
                {{--<td>{{ $total_student_all_course }}/2</td>--}}
                <td>{{ $total_student_all_course }}</td>
                <td class="textend">{{ number_format($default_rate_10_a, 2) }}</td>
                <td class="textend">{{ number_format($total_amount_all_course, 2) }}</td>
            </tr>
        @else
            {{-- Show default row if no assign exists --}}
            <tr>
                <td rowspan="2">10</td>
                <td class="textstart" rowspan="2">{{ $head }}</td>
                <td class="textstart">(a) {{ $sub_head_10_a }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ number_format($default_rate_10_a, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
            $assign->exam_type_id == 2 &&
        @endif




        {{-- Order = 10.b --}}
        @php

            $rateHead=\App\Models\RateHead::where('order_no','10.b')->first();
            $head = $rateHead->head;
            $sub_head_10_b = $rateHead->sub_head;
        @endphp

        {{-- Show default row if no assign exists --}}
        <tr>
            <td class="textstart">(a) {{ $sub_head_10_b }}</td>
            <td></td>
            <td></td>
            <td class="textend"></td>
            <td class="textend"></td>
        </tr>








        {{--Order 11--}}
        @php
            //$assigns_order_11 = $teacher->rateAssigns->where('rateHead.order_no', '11')->first();
            $assigns_order_11 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                 return $assign->session_id == $session_info->id &&
                        $assign->exam_type_id == 2 &&
                        $assign->rateHead &&
                        $assign->rateHead->order_no == '11';
             })->first();
            $total_assigns = $assigns_order_11 ? $assigns_order_11->count() : 0;
            $head_order_11 = $rateHead_order_11->head ?? '';
            $default_rate = $rateAmount_order_11->default_rate ?? 0;
            if ($assigns_order_11 && $assigns_order_11->total_amount) {
                $global_sum += $assigns_order_11->total_amount;
            }
        @endphp
        <tr>
            <td>11</td>
            <td class="textstart" colspan="2">{{ $head_order_11 }}</td>
            <td></td>
            <td>{{ $assigns_order_11->no_of_items ?? '' }}</td>
            <td class="textend">
                @if($total_assigns > 0 && isset($rateAmount_order_11->default_rate))
                    {{ $rateAmount_order_11->default_rate }}
                @endif
            </td>
            <td class="textend">{{ isset($assigns_order_11->total_amount) ? number_format($assigns_order_11->total_amount, 2) : '' }}</td>
        </tr>




        {{-- Order 12.a/12.b --}}
        @php
            //$assign_12_a = $teacher->rateAssigns->where('rateHead.order_no', '12.a')->first();
             $assign_12_a = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '12.a';
               })->first();

            $total_assigns = $assign_12_a ? $assign_12_a->count() : 0;

            $rateAmount_12_a = $rateAmount_order_12_a ?? null;
            $head = $rateHead_order_12_a->head ?? '';
            $sub_head_12_a = $rateHead_order_12_a->sub_head ?? 'Error';
            $default_rate_12_a = $rateAmount_12_a->default_rate ?? 0;

            if ($assign_12_a && $assign_12_a->total_amount) {
                $global_sum += $assign_12_a->total_amount;
            }
        @endphp
        <tr>
            <td rowspan="2">12</td>
            <td class="textstart" rowspan="2">{{ $head }}</td>
            <td class="textstart">(a) {{ $sub_head_12_a }}</td>
            {{-- <td>{{ $assign_12_a->course_code ?? '' }}</td>--}}
            <td></td>
            <td>{{ $assign_12_a->no_of_items ?? '' }}</td>
            {{--<td class="textend">{{ number_format($default_rate_12_a, 2) }}</td>--}}
            <td class="textend">
                @if($total_assigns > 0 && isset($rateAmount_order_12_a->default_rate))
                    {{ $rateAmount_order_12_a->default_rate }}
                @endif
            </td>
            <td class="textend">{{ isset($assign_12_a->total_amount) ? number_format($assign_12_a->total_amount, 2) : '' }}</td>
        </tr>

        {{-- Order 12.b --}}
        @php
            //$assign_12_b = $teacher->rateAssigns->where('rateHead.order_no', '12.b')->first();
             $assign_12_b = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '12.b';
               })->first();

             $total_assigns = $assign_12_b ? $assign_12_b->count() : 0;
            $rateAmount_12_b = $rateAmount_order_12_b ?? null;
            $sub_head_12_b = $rateHead_order_12_b->sub_head ?? '6.B';
            $default_rate_12_b = $rateAmount_12_b->default_rate ?? 0;

            if ($assign_12_b && $assign_12_b->total_amount) {
                $global_sum += $assign_12_b->total_amount;
            }
        @endphp
        <tr>
            <td class="textstart">(b) {{ $sub_head_12_b }}</td>
            <td></td>
            <td>{{ $assign_12_b->no_of_items ?? '' }}</td>
            <td class="textend">
                @if($total_assigns > 0 && isset($rateAmount_order_12_b->default_rate))
                    {{ $rateAmount_order_12_b->default_rate }}
                @endif
            </td>
            <td class="textend">{{ isset($assign_12_b->total_amount) ? number_format($assign_12_b->total_amount, 2) : '' }}</td>
        </tr>



        {{-- Order 13 --}}
        @php
            $head_order_13 = $rateHead_order_13->head ?? 'Error';
        @endphp
        <tr>
            <td>13</td>
            <td class="textstart" colspan="2">{{ $head_order_13 }}</td>
            <td></td>
            <td></td>
            <td class="textend"></td>
            <td class="textend"></td>
        </tr>



        {{-- Order 14 --}}
        @php
            $head_order_14 = $rateHead_order_14->head ?? 'Error';
        @endphp
        <tr>
            <td>14</td>
            <td class="textstart" colspan="2">{{ $head_order_14 }}</td>
            <td></td>
            <td></td>
            <td class="textend"></td>
            <td class="textend"></td>
        </tr>

        {{-- Order 15 --}}
        @php
            //$assigns_order_15 = $teacher->rateAssigns->where('rateHead.order_no', '15')->first();
            $assigns_order_15 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '15';
               })->first();
            $head_order_15 = $rateHead_order_15->head ?? 'Error';
            $default_rate_15 = $rateAmount_order_15->default_rate ?? 0;
            if ($assigns_order_15 && $assigns_order_15->total_amount) {
                $global_sum += $assigns_order_15->total_amount;
            }
        @endphp
        <tr>
            <td>15</td>
            <td class="textstart" colspan="2">{{ $head_order_15 }}</td>
            <td></td>
            <td></td>
            <td class="textend"></td>
            <td class="textend">{{ isset($assigns_order_15->total_amount) ? number_format($assigns_order_15->total_amount, 2) : '' }}</td>
        </tr>








        {{-- Order 16 --}}
        @php
            $head_order_16 = $rateHead_order_16->head ?? 'Error';
        @endphp
        <tr>
            <td>16</td>
            <td class="textstart" colspan="2">{{ $head_order_16 }}</td>
            <td></td>
            <td></td>
            <td class="textend">
            </td>
            <td class="textend"></td>
        </tr>




        //Final Calculation
        <tr>
            <td colspan="6" class="textend">Total:</td>
            <td class="textend">{{ isset($global_sum) ? number_format($global_sum, 2) : '' }}</td>
        </tr>











        </tbody>
    </table>

    {{-- Footer --}}
    <table class="footer_table_1">
        <tr>
            <td colspan="2" style="text-align: left;">---------------------------------------------------</td>
            <td colspan="2" style="text-align: right;">---------------------------------------------------</td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: left;">
                <span style="padding-left: 40px;">Countersigned<br></span>
                Chairman, Examination Committee
            </td>
            <td colspan="2" style="text-align: right;">
                <span style="padding-right: 30px;">Signature of Examiner and Date</span>
            </td>
        </tr>
        <tr>
            <td style="text-align: center" colspan="4" class="pt-20">
                ---------------------------------------------------------------------------------------------------------------------------------------------
            </td>
        </tr>
        <tr>
            <td style="text-align: center" colspan="4">(For Comptroller office use only)</td>
        </tr>
        <tr>
            <td style="width: 20%;" class="pt-20">Taka ---<br>Received</td>
            <td style="width: 20%;" class="pt-20">------------ In words</td>
            <td style="width: 30%;" class="pt-20">----------------------------------------------------------------------</td>
            <td style="width: 30%;" class="pt-20" style="text-align: right">-----------approved</td>
        </tr>
        <tr>
            <td class="pt-40">Signature of Examiner</td>
            <td class="pt-40">Prepared by</td>
            <td class="pt-40">Assistant Comptroller</td>
            <td class="pt-40">Comptroller (In Charge)</td>
        </tr>
    </table>

    @if (!$loop->last)
        <div class="page-break"></div>
    @endif

@endforeach
@php } @endphp


{{-- ✅ For Employee, Admin, SuperAdmin --}}
@php
    if ($user->hasRole('Employee') || $user->hasRole('Admin') || $user->hasRole('SuperAdmin')) {
@endphp
    @foreach($employees as  $employee)
    @php
        $global_sum=0;
    @endphp

    {{-- Repeatable Header --}}
    <table class="header_table " style=" table-layout: fixed;">
        <colgroup>
            <col style="width: 15%;">
            <col style="width: 35%;">
            <col style="width: 20%;">
            <col style="width: 30%;">
        </colgroup>

        <!-- Header with Logo and University Info -->
        <tr>
            <td colspan="1" style="text-align: right; padding: 20px 0px 0px 0px;">
                <img src="{{ public_path('images/logo_duet.png') }}" style="width: 50px;">
            </td>
            <td colspan="3" style="text-align: left; padding: 20px 0 0 35px;">
                <strong>Dhaka University of Engineering & Technology, Gazipur</strong><br>
                <span style="display: inline-block; margin-left:100px; margin-top: 5px;">
                Gazipur-1707
            </span>
            </td>
        </tr>

        <!-- Section Title -->
        <tr>
            <td colspan="4" style="padding: 10px 0;">
                <div style="margin-left: 5px; font-weight: bold;">
                    (Examination Related Remuneration)
                </div>
            </td>
        </tr>

        <!-- Session Info -->
        @php
            $ordinals = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th', 5 => '5th'];
            $yearText = $ordinals[$session_info->year] ?? $session_info->year . 'th';
            $semesterText = $ordinals[$session_info->semester] ?? $session_info->semester . 'th';
        @endphp
        <tr>
            <td style="text-align: right;padding-right: 10px;">
                B.Arch.
            </td>
            <td>
                <div style="display: flex; justify-content: space-between;">
                    <span>{{ $yearText }} year {{ $semesterText }} semester</span>
                    <span style="font-weight: bold; padding-left: 10px;">Regular</span>
                </div>
            </td>
            <td style="text-align: left;padding-left: 20px;">
                {{ $session_info->session }}
            </td>
            <td style="text-align: left;">
                (Held on: _____________)
            </td>
        </tr>

        <!-- Teacher Info -->
        <tr>
            <td colspan="1" style="padding-top: 15px; text-align: left;padding-left: 10px;">
                <strong>Name:</strong> {{ $teacher->user->name }}
            </td>
            <td colspan="2" style="padding-top: 15px;padding-right:5px;">
                <strong>Designation:</strong> {{ $teacher->designation->designation }}
            </td>
            <td style="padding-top: 15px;">
                <strong>Department:</strong> {{ $teacher->department->shortname }}, DUET
            </td>
        </tr>

        <!-- Section Header -->
        <tr>
            <td colspan="4" style="padding-top: 30px; font-weight: bold;">
                Details of Examination Related Works
            </td>
        </tr>


    </table>

    {{-- Body Table --}}
    <table class="body_table_1" style="margin-top: 10px;">
        <thead>
        <tr>
            <th>Sl. No.</th>
            <th colspan="2">Description of work</th>
            <th>Subject/Course</th>
            <th>Nos. of script/Students</th>
            <th>Rate</th>
            <th>Taka</th>
        </tr>
        </thead>
        <tbody>


        {{-- Order=1 --}}
        @php
            //$assigns_order_1 = $teacher->rateAssigns->where('rateHead.order_no', '1');
            $assigns_order_1 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 2 &&
                       $assign->rateHead &&
                       $assign->rateHead->order_no == '1';
            });
            $total_taka = 0;
            $no_of_item = 0;

            if ($assigns_order_1->isNotEmpty()) {
                foreach ($assigns_order_1 as $assign) {
                    $global_sum += $assign->total_amount ?? 0;
                    $total_taka += $assign->total_amount ?? 0;
                    $no_of_item = $assign->no_of_item ?? 0;
                }
            }

            // Always show default RateHead and RateAmount
            $head = $rateHead_order_1->head ?? 'Moderation';
            $max_rate = $rateAmount_order_1->max_rate ?? ($rateAmount_order_1->default_rate ?? '');
            $min_rate = $rateAmount_order_1->min_rate ?? ($rateAmount_order_1->default_rate ?? '');
        @endphp

        <tr>
            <td rowspan="2">1</td>
            <td class="textstart" colspan="2" rowspan="2">{{ $head }}</td>
            <td rowspan="2"></td>
            <td rowspan="2">{{ $no_of_item == 0 ? '' : $no_of_item }}</td>
            <td class="textend">max. {{ $max_rate !== '' ? number_format($max_rate, 0) : '' }}</td>
            <td rowspan="2" class="textend">{{ $total_taka == 0 ? '' : number_format($total_taka, 2) }}</td>
        </tr>
        <tr>
            <td class="textend">min. {{ $min_rate !== '' ? number_format($min_rate, 0) : '' }}</td>
        </tr>



        {{-- Order = 2 --}}
        @php
            $assigns_order_2 = App\Models\RateAssign::where('teacher_id', $teacher->id)
                               ->where('session_id', $session_info->id)
                               ->whereHas('rateHead', function ($q) {
                                   $q->where('order_no', '2');
                               })->get();
            $total_assigns = $assigns_order_2->count();
            $loopIndex = 0;

            $head = $rateHead_order_2->head ?? 'Paper Setters';
            $default_rate = $rateAmount_order_2->default_rate ?? 0;

            //dd($assigns_order_2);
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assigns_order_2 as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                @endphp
                <tr>
                    @if ($loopIndex == 0)
                        <td rowspan="{{ $total_assigns }}">2</td>
                        <td class="textstart" colspan="2" rowspan="{{ $total_assigns }}">{{ $head }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    <td></td>
                    <td class="textend">{{ isset($default_rate) ? number_format($default_rate, 2) : '' }}</td>
                    <td class="textend">{{ isset($assign->total_amount) ? number_format($assign->total_amount, 2) : '' }}</td>
                </tr>
                @php $loopIndex++; @endphp
            @endforeach
        @else
            {{-- Show default row if no assign exists --}}
            <tr>
                <td rowspan="1">2</td>
                <td class="textstart" colspan="2" rowspan="1">{{ $head }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ isset($default_rate) ? number_format($default_rate, 2) : '' }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif



        {{-- Order = 3 --}}
        @php
            //$assigns_order_3 = $teacher->rateAssigns->where('rateHead.order_no', '3');
             $assigns_order_3 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '3';
               });
            $total_assigns = $assigns_order_3->count();
            $loopIndex = 0;

            $head = $rateHead_order_3->head ?? 'Examiner';
            $default_rate = $rateAmount_order_3->default_rate ?? 0;
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assigns_order_3 as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                @endphp
                <tr>
                    @if ($loopIndex == 0)
                        <td rowspan="{{ $total_assigns }}">3</td>
                        <td class="textstart" colspan="2" rowspan="{{ $total_assigns }}">{{ $head }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    <td>{{$assign->total_students}}/{{ $assign->total_teachers ?? '' }}</td>
                    <td class="textend">{{ isset($default_rate) ? number_format($default_rate, 2) : '' }}</td>
                    <td class="textend">{{ isset($assign->total_amount) ? number_format($assign->total_amount, 2) : '' }}</td>
                </tr>
                @php $loopIndex++; @endphp
            @endforeach
        @else
            {{-- Show default row if no assign exists --}}
            <tr>
                <td rowspan="1">3</td>
                <td class="textstart" colspan="2" rowspan="1">{{ $head }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ number_format($default_rate, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif



        {{-- Order = 4 --}}
        @php
            $head = $rateHead_order_4->head ?? 'Class Test';
        @endphp
        {{-- Fallback row if no data --}}
        <tr>
            <td rowspan="1">4</td>
            <td class="textstart" colspan="2" rowspan="1">{{ $head }}</td>
            <td></td>
            <td></td>
            <td class="textend"></td>
            <td class="textend"></td>
        </tr>






        {{-- Order = 5 --}}
        @php
            $head = $rateHead_order_5->head ?? 'Laboratory/Survey works';
        @endphp
        {{-- Fallback row if no data --}}
        <tr>
            <td rowspan="1">5</td>
            <td class="textstart" colspan="2" rowspan="1">{{ $head }}</td>
            <td></td>
            <td></td>
            {{-- <td class="textend">{{ number_format($default_rate, 2) }}</td>--}}
            <td class="textend"></td>
            <td class="textend"></td>
        </tr>





        {{-- Order 6.a/b/c/d --}}
        @php
            $head = $rateHead_order_6a->head ?? '';
            $sub_head_6a = $rateHead_order_6a->sub_head ?? '';

        @endphp
        <tr>
            <td rowspan="4">6</td>
            <td class="textstart" rowspan="4">{{ $head }}</td>
            <td class="textstart">{{$sub_head_6a}}</td>
            <td></td>
            <td></td>
            <td></td>
            <td class="textend"></td>
        </tr>

        {{-- Order 6.b --}}
        @php
            $sub_head_6b = $rateHead_order_6b->sub_head ?? '6.B';
        @endphp
        <tr>
            <td class="textstart">{{ $sub_head_6b }}</td>
            <td></td>
            <td></td>
            <td class="textend">
            </td>

            <td class="textend"></td>
        </tr>

        {{-- Order 6.c --}}
        @php
            $sub_head_6c = $rateHead_order_6c->sub_head ?? '';
        @endphp
        <tr>
            <td class="textstart">{{ $sub_head_6c }}</td>
            <td></td>
            <td></td>
            <td class="textend">

            </td>
            <td class="textend"></td>
        </tr>

        {{-- Order 6.d --}}
        @php
            $sub_head_6d = $rateHead_order_6d->sub_head ?? '';
        @endphp
        <tr>
            <td class="textstart">{{ $sub_head_6d }}</td>
            <td></td>
            <td></td>
            <td>

            </td>
            <td class="textend"></td>
        </tr>




        {{-- Order 7.e/7.f --}}
        @php
            $head = $rateHead_order_7e->head ?? '';
            $sub_head_7e = $rateHead_order_7e->sub_head ?? '';
        @endphp
        <tr>
            <td rowspan="2">7</td>
            <td class="textstart" rowspan="2">{{ $head }}</td>
            <td class="textstart">{{$sub_head_7e}}</td>
            <td></td>
            <td></td>
            <td class="textend">
            </td>

            <td class="textend"></td>
        </tr>

        {{-- Order 7.f --}}
        @php
            $sub_head_7f = $rateHead_order_7f->sub_head ?? '';
        @endphp
        <tr>
            <td class="textstart">{{ $sub_head_7f }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>






        @php
            //$assigns_order_8a = $teacher->rateAssigns->where('rateHead.order_no', '8.a');
            $assigns_order_8a = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '8.a';
               });
           /* //$assigns_order_8b = $teacher->rateAssigns->where('rateHead.order_no', '8.b');
             $assigns_order_8b = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '8.b';
               });

             $assigns_order_8c = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '8.c';
               })->first();*/




            $total_assigns_8a = $assigns_order_8a->count();


            // Total number of rows for section 8 (8.a + 8.b + 8.c + 8.d)
            $rowspan_8_block = max(1, $total_assigns_8a) + 1 + 1 + 1;

            $head_8a = $rateHead_order_8a->head ?? 'Gradesheet Preparation--';
            $sub_head_8a = $rateHead_order_8a->sub_head ?? 'Theoretical*';
            $rateAmount_8a_default_rate = $rateAmount_order_8a->default_rate ?? '';

            $head_8b = $rateHead_order_8b->head ?? 'Gradesheet Preparation--';
            $sub_head_8b = $rateHead_order_8b->sub_head ?? 'Sessional*';




            $head_8c = $rateHead_order_8c->head ?? 'Empty';


            $head_8d = $rateHead_order_8d->head ?? 'Empty';

        @endphp

        {{-- 8.a rows --}}
        @if ($total_assigns_8a > 0)
            @foreach ($assigns_order_8a as $assign)
                <tr>
                    @if ($loop->first)
                        <td rowspan="{{ $rowspan_8_block }}">8</td>
                        <td class="textstart" rowspan="{{ max(1, $total_assigns_8a) +1 }}">{{ $head_8a }}</td>
                        <td class="textstart" rowspan="{{ $total_assigns_8a }}">{{ $sub_head_8a }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    <td>{{$assign->total_students}}/{{$assign->total_teachers}}</td>
                    <td class="textend">{{ number_format((float)$rateAmount_8a_default_rate, 2) }}</td>
                    <td class="textend">{{ number_format((float)($assign->total_amount ?? 0), 2) }}</td>
                    @php $global_sum += $assign->total_amount ?? 0; @endphp
                </tr>
            @endforeach
        @else
            <tr>
                <td rowspan="{{ $rowspan_8_block }}">8</td>
                <td rowspan="{{ max(1, $total_assigns_8a) + 1 }}" class="textstart">{{ $head_8a }}</td>
                <td class="textstart">{{ $sub_head_8a }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ number_format((float)$rateAmount_8a_default_rate, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif



        {{-- 8.b rows --}}
        <tr>
            <td class="textstart">{{ $sub_head_8b }}</td>
            <td></td>
            <td></td>
            <td class="textend"></td>
            <td class="textend"></td>
        </tr>


        {{-- Order = 8.c --}}
        <tr>
            <td class="textstart" colspan="2">{{ $head_8c }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td class="textend"></td>
        </tr>



        {{-- Order = 8.d --}}
        @php
            $rateHead=\App\Models\RateHead::where('order_no','8.d')->first();
            $head = $rateHead->head;
        @endphp

        {{-- Show default row if no assign exists --}}
        <tr>
            <td class="textstart" colspan="2">{{ $head }}</td>
            <td></td>
            <td></td>
            <td class="textend"></td>
            <td class="textend"></td>
        </tr>







        {{-- Order = 9 --}}
        @php
            //$assigns_order_9 = $teacher->rateAssigns->where('rateHead.order_no', '9');
            $assigns_order_9 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '9';
               });
            $total_assigns = $assigns_order_9->count();
            $loopIndex = 0;

            $head = $rateHead_order_9->head ?? 'Scrutinizing ( Answre Script--)';
            $default_rate = $rateAmount_order_9->default_rate ?? 0;
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assigns_order_9 as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                @endphp
                <tr>
                    @if ($loopIndex == 0)
                        <td rowspan="{{ $total_assigns }}">9</td>
                        <td class="textstart" colspan="2" rowspan="{{ $total_assigns }}">{{ $head }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    <td>{{$assign->total_students}}/{{$assign->total_teachers}}</td>
                    <td class="textend">{{ isset($default_rate) ? number_format($default_rate, 2) : '' }}</td>
                    <td class="textend">{{ isset($assign->total_amount) ? number_format($assign->total_amount, 2) : '' }}</td>
                </tr>
                @php $loopIndex++; @endphp
            @endforeach
        @else
            {{-- Show default row if no assign exists --}}
            <tr>
                <td rowspan="1">9</td>
                <td class="textstart" colspan="2" rowspan="1">{{ $head }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ isset($default_rate) ? number_format($default_rate, 2) : '' }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif






        {{-- Order = 10.a --}}
        @php
            $assign_10_a = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 2 &&
                       $assign->rateHead &&
                       $assign->rateHead->order_no == '10.a';
            });

            $total_assigns = $assign_10_a->count();

            $rateHead=\App\Models\RateHead::where('order_no','10.a')->first();
            $head = $rateHead->head;
            $sub_head_10_a = $rateHead->sub_head;
            $rateAmount_10_a = $rateAmount_order_10_a ?? null;
            $default_rate_10_a = $rateAmount_10_a->default_rate ?? 0;

            $total_student_all_course = 0;
            $total_amount_all_course = 0;
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assign_10_a as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                   /* $total_student_all_course += $assign->total_students ?? 0;*/
                    $total_student_all_course += $assign->no_of_items ?? 0;
                    $total_amount_all_course += $assign->total_amount ?? 0;
                @endphp
            @endforeach
            <tr>
                <td rowspan="2">10</td>
                <td class="textstart" rowspan="2">{{ $head }}</td>
                <td class="textstart">(a) {{ $sub_head_10_a }}</td>
                <td>{{ $total_assigns }} courses</td>
                {{--<td>{{ $total_student_all_course }}/2</td>--}}
                <td>{{ $total_student_all_course }}</td>
                <td class="textend">{{ number_format($default_rate_10_a, 2) }}</td>
                <td class="textend">{{ number_format($total_amount_all_course, 2) }}</td>
            </tr>
        @else
            {{-- Show default row if no assign exists --}}
            <tr>
                <td rowspan="2">10</td>
                <td class="textstart" rowspan="2">{{ $head }}</td>
                <td class="textstart">(a) {{ $sub_head_10_a }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ number_format($default_rate_10_a, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
            $assign->exam_type_id == 2 &&
        @endif




        {{-- Order = 10.b --}}
        @php

            $rateHead=\App\Models\RateHead::where('order_no','10.b')->first();
            $head = $rateHead->head;
            $sub_head_10_b = $rateHead->sub_head;
        @endphp

        {{-- Show default row if no assign exists --}}
        <tr>
            <td class="textstart">(a) {{ $sub_head_10_b }}</td>
            <td></td>
            <td></td>
            <td class="textend"></td>
            <td class="textend"></td>
        </tr>








        {{--Order 11--}}
        @php
            //$assigns_order_11 = $teacher->rateAssigns->where('rateHead.order_no', '11')->first();
            $assigns_order_11 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                 return $assign->session_id == $session_info->id &&
                        $assign->exam_type_id == 2 &&
                        $assign->rateHead &&
                        $assign->rateHead->order_no == '11';
             })->first();
            $total_assigns = $assigns_order_11 ? $assigns_order_11->count() : 0;
            $head_order_11 = $rateHead_order_11->head ?? '';
            $default_rate = $rateAmount_order_11->default_rate ?? 0;
            if ($assigns_order_11 && $assigns_order_11->total_amount) {
                $global_sum += $assigns_order_11->total_amount;
            }
        @endphp
        <tr>
            <td>11</td>
            <td class="textstart" colspan="2">{{ $head_order_11 }}</td>
            <td></td>
            <td>{{ $assigns_order_11->no_of_items ?? '' }}</td>
            <td class="textend">
                @if($total_assigns > 0 && isset($rateAmount_order_11->default_rate))
                    {{ $rateAmount_order_11->default_rate }}
                @endif
            </td>
            <td class="textend">{{ isset($assigns_order_11->total_amount) ? number_format($assigns_order_11->total_amount, 2) : '' }}</td>
        </tr>




        {{-- Order 12.a/12.b --}}
        @php
            //$assign_12_a = $teacher->rateAssigns->where('rateHead.order_no', '12.a')->first();
             $assign_12_a = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '12.a';
               })->first();

            $total_assigns = $assign_12_a ? $assign_12_a->count() : 0;

            $rateAmount_12_a = $rateAmount_order_12_a ?? null;
            $head = $rateHead_order_12_a->head ?? '';
            $sub_head_12_a = $rateHead_order_12_a->sub_head ?? 'Error';
            $default_rate_12_a = $rateAmount_12_a->default_rate ?? 0;

            if ($assign_12_a && $assign_12_a->total_amount) {
                $global_sum += $assign_12_a->total_amount;
            }
        @endphp
        <tr>
            <td rowspan="2">12</td>
            <td class="textstart" rowspan="2">{{ $head }}</td>
            <td class="textstart">(a) {{ $sub_head_12_a }}</td>
            {{-- <td>{{ $assign_12_a->course_code ?? '' }}</td>--}}
            <td></td>
            <td>{{ $assign_12_a->no_of_items ?? '' }}</td>
            {{--<td class="textend">{{ number_format($default_rate_12_a, 2) }}</td>--}}
            <td class="textend">
                @if($total_assigns > 0 && isset($rateAmount_order_12_a->default_rate))
                    {{ $rateAmount_order_12_a->default_rate }}
                @endif
            </td>
            <td class="textend">{{ isset($assign_12_a->total_amount) ? number_format($assign_12_a->total_amount, 2) : '' }}</td>
        </tr>

        {{-- Order 12.b --}}
        @php
            //$assign_12_b = $teacher->rateAssigns->where('rateHead.order_no', '12.b')->first();
             $assign_12_b = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '12.b';
               })->first();

             $total_assigns = $assign_12_b ? $assign_12_b->count() : 0;
            $rateAmount_12_b = $rateAmount_order_12_b ?? null;
            $sub_head_12_b = $rateHead_order_12_b->sub_head ?? '6.B';
            $default_rate_12_b = $rateAmount_12_b->default_rate ?? 0;

            if ($assign_12_b && $assign_12_b->total_amount) {
                $global_sum += $assign_12_b->total_amount;
            }
        @endphp
        <tr>
            <td class="textstart">(b) {{ $sub_head_12_b }}</td>
            <td></td>
            <td>{{ $assign_12_b->no_of_items ?? '' }}</td>
            <td class="textend">
                @if($total_assigns > 0 && isset($rateAmount_order_12_b->default_rate))
                    {{ $rateAmount_order_12_b->default_rate }}
                @endif
            </td>
            <td class="textend">{{ isset($assign_12_b->total_amount) ? number_format($assign_12_b->total_amount, 2) : '' }}</td>
        </tr>



        {{-- Order 13 --}}
        @php
            $head_order_13 = $rateHead_order_13->head ?? 'Error';
        @endphp
        <tr>
            <td>13</td>
            <td class="textstart" colspan="2">{{ $head_order_13 }}</td>
            <td></td>
            <td></td>
            <td class="textend"></td>
            <td class="textend"></td>
        </tr>



        {{-- Order 14 --}}
        @php
            $head_order_14 = $rateHead_order_14->head ?? 'Error';
        @endphp
        <tr>
            <td>14</td>
            <td class="textstart" colspan="2">{{ $head_order_14 }}</td>
            <td></td>
            <td></td>
            <td class="textend"></td>
            <td class="textend"></td>
        </tr>

        {{-- Order 15 --}}
        @php
            //$assigns_order_15 = $teacher->rateAssigns->where('rateHead.order_no', '15')->first();
            $assigns_order_15 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 2 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '15';
               })->first();
            $head_order_15 = $rateHead_order_15->head ?? 'Error';
            $default_rate_15 = $rateAmount_order_15->default_rate ?? 0;
            if ($assigns_order_15 && $assigns_order_15->total_amount) {
                $global_sum += $assigns_order_15->total_amount;
            }
        @endphp
        <tr>
            <td>15</td>
            <td class="textstart" colspan="2">{{ $head_order_15 }}</td>
            <td></td>
            <td></td>
            <td class="textend"></td>
            <td class="textend">{{ isset($assigns_order_15->total_amount) ? number_format($assigns_order_15->total_amount, 2) : '' }}</td>
        </tr>








        {{-- Order 16 --}}
        @php
            $head_order_16 = $rateHead_order_16->head ?? 'Error';
        @endphp
        <tr>
            <td>16</td>
            <td class="textstart" colspan="2">{{ $head_order_16 }}</td>
            <td></td>
            <td></td>
            <td class="textend">
            </td>
            <td class="textend"></td>
        </tr>




        //Final Calculation
        <tr>
            <td colspan="6" class="textend">Total:</td>
            <td class="textend">{{ isset($global_sum) ? number_format($global_sum, 2) : '' }}</td>
        </tr>











        </tbody>
    </table>

    {{-- Footer --}}
    <table class="footer_table_1">
        <tr>
            <td colspan="2" style="text-align: left;">---------------------------------------------------</td>
            <td colspan="2" style="text-align: right;">---------------------------------------------------</td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: left;">
                <span style="padding-left: 40px;">Countersigned<br></span>
                Chairman, Examination Committee
            </td>
            <td colspan="2" style="text-align: right;">
                <span style="padding-right: 30px;">Signature of Examiner and Date</span>
            </td>
        </tr>
        <tr>
            <td style="text-align: center" colspan="4" class="pt-20">
                ---------------------------------------------------------------------------------------------------------------------------------------------
            </td>
        </tr>
        <tr>
            <td style="text-align: center" colspan="4">(For Comptroller office use only)</td>
        </tr>
        <tr>
            <td style="width: 20%;" class="pt-20">Taka ---<br>Received</td>
            <td style="width: 20%;" class="pt-20">------------ In words</td>
            <td style="width: 30%;" class="pt-20">----------------------------------------------------------------------</td>
            <td style="width: 30%;" class="pt-20" style="text-align: right">-----------approved</td>
        </tr>
        <tr>
            <td class="pt-40">Signature of Examiner</td>
            <td class="pt-40">Prepared by</td>
            <td class="pt-40">Assistant Comptroller</td>
            <td class="pt-40">Comptroller (In Charge)</td>
        </tr>
    </table>

    @if (!$loop->last)
        <div class="page-break"></div>
    @endif

@endforeach
@php } @endphp

</body>
</html>
