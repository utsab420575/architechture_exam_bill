<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Bill Regular</title>
    <style>
        @page {
            /*top right bottom left*/
            margin: 5mm 12mm 2mm 12mm;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 12px;
        }

        .header_table, .body_table_1, .footer_table_1 {
            width: 100%;
            border-collapse: collapse;

        }

        /*  ADD THIS BLOCK FOR Revenue Table */
        .revenue_table {
            width: 100%;
            margin-top: 15px;
            font-size: 11px;
        }
        .revenue-stamp-box {
            border: 1px solid #000;
            width: 90px;
            height: 55px;
            text-align: center;
            vertical-align: middle;
        }
        .revenue-stamp-box span {
            display: inline-block;
            margin-top: 15px;
            line-height: 1.2;
        }
        /* Revenue Table End here*/


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
            margin-top: 35px;
            font-size: 12px;
        }

        .pt-20 {
            padding-top: 20px;
        }

        .pt-30 {
            padding-top: 30px;
        }

        .pt-40 {
            padding-top: 40px;
        }

        td.textstart {
            text-align: left;
        }

        td.textend {
            text-align: right;
        }

        td.textcenter {
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
        $user = auth()->user();
        $isAdmin = $user->hasAnyRole(['Admin','SuperAdmin']); // or 'Admin|SuperAdmin' if using Spatie
        $isTeacherOnly = $user->hasRole('Teacher') && !$isAdmin;
    @endphp
    @php
        // Skip other teachers if the user is a teacher
         if ($isTeacherOnly && $user->id !== $teacher->user_id) {
            continue; // only teachers (not admins) are restricted to their own row
        }
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
            <td colspan="2" style="padding-top: 15px; text-align: left;padding-left: 5px;">
                <strong>Name:</strong> {{ $teacher->user->name }}
            </td>
            <td colspan="1" style="padding-top: 15px;">
                <div style="transform: translateX(-90px);">
                    <strong>Designation:</strong> {{ $teacher->designation->designation }}
                </div>
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
                       $assign->exam_type_id == 1 &&
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
            @if($assigns_order_1->isNotEmpty())
                <td class="textend">max. {{ number_format($max_rate, 0) }}</td>
            @else
                <td></td>
            @endif
            <td rowspan="2" class="textend">{{ $total_taka == 0 ? '' : number_format($total_taka, 2) }}</td>
        </tr>
        <tr>
            @if($assigns_order_1->isNotEmpty())
                <td class="textend">min. {{ number_format($min_rate, 0) }}</td>
            @else
                <td></td>
            @endif
        </tr>


        {{-- Order = 2 --}}
        @php
            //$assigns_order_2 = $teacher->rateAssigns->where('rateHead.order_no', '2');
            /* $assigns_order_2 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                 return $assign->session_id == $session_info->id &&
            $assign->exam_type_id == 1 &&
            $assign->rateHead &&
                        $assign->rateHead->order_no == '2';
             });*/
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
                          $assign->exam_type_id == 1 &&
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
            //$assigns_order_4 = $teacher->rateAssigns->where('rateHead.order_no', '4');
            $assigns_order_4 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '4';
               });
            $total_assigns = $assigns_order_4->count();
            $loopIndex = 0;

            $head = $rateHead_order_4->head ?? 'Class Test';
            $default_rate = $rateAmount_order_4->default_rate ?? 0;
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assigns_order_4 as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                @endphp
                <tr>
                    @if ($loopIndex == 0)
                        <td rowspan="{{ $total_assigns }}">4</td>
                        <td class="textstart" colspan="2" rowspan="{{ $total_assigns }}">{{ $head }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    <td>{{$assign->total_students}}*2</td>
                    <td class="textend">{{ number_format($default_rate, 2) }}</td>
                    <td class="textend">{{ isset($assign->total_amount) ? number_format($assign->total_amount, 2) : '' }}</td>
                </tr>
                @php $loopIndex++; @endphp
            @endforeach
        @else
            {{-- Fallback row if no data --}}
            <tr>
                <td rowspan="1">4</td>
                <td class="textstart" colspan="2" rowspan="1">{{ $head }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ number_format($default_rate, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif





        {{-- Order = 5 --}}
        @php
            //$assigns_order_5 = $teacher->rateAssigns->where('rateHead.order_no', '5');
            $assigns_order_5 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '5';
               });
            $total_assigns = $assigns_order_5->count();
            $loopIndex = 0;

            $head = $rateHead_order_5->head ?? 'Laboratory/Survey works';
            $default_rate = $rateAmount_order_5->default_rate ?? 0;
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assigns_order_5 as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                @endphp
                <tr>
                    @if ($loopIndex == 0)
                        <td rowspan="{{ $total_assigns }}">5</td>
                        <td class="textstart" colspan="2" rowspan="{{ $total_assigns }}">{{ $head }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    {{--here we show total week--}}
                    <td>{{$assign->total_students}} weeks</td>
                    {{--<td class="textend">{{ number_format($default_rate, 2) }}</td>--}}
                    <td class="textend">{{ number_format($default_rate * $assign->no_of_items, 0) }}</td>
                    <td class="textend">{{ isset($assign->total_amount) ? number_format($assign->total_amount, 2) : '' }}</td>
                </tr>
                @php $loopIndex++; @endphp
            @endforeach
        @else
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
        @endif






        {{-- Order 6.a/b/c/d --}}
        @php
            // ALL 6.a rows for this teacher (session + exam type)
            $assigns_6a = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id
                    && $assign->exam_type_id == 1
                    && $assign->rateHead
                    && $assign->rateHead->order_no == '6.a';
            });

            $rateAmount_6a   = $rateAmount_order_6a ?? null;
            $head            = $rateHead_order_6a->head ?? '';
            $sub_head_6a     = $rateHead_order_6a->sub_head ?? '6.A';
            $default_rate_6a = $rateAmount_6a->default_rate ?? 0;

            // Accurate sums with BCMath (fall back to float if BCMath missing)
            $scale = 10;
            $sum_no_of_items = '0';
            $sum_total_amount = '0';

            foreach ($assigns_6a as $a) {
                if (function_exists('bcadd')) {
                    $sum_no_of_items  = bcadd($sum_no_of_items,  (string)$a->no_of_items,  $scale);
                    $sum_total_amount = bcadd($sum_total_amount, (string)$a->total_amount, $scale);
                } else {
                    $sum_no_of_items  = (string)((float)$sum_no_of_items  + (float)$a->no_of_items);
                    $sum_total_amount = (string)((float)$sum_total_amount + (float)$a->total_amount);
                }
            }

            // Pretty display: no_of_items without trailing zeros, currency with 2 dp
            $sum_no_of_items_disp = ($sum_no_of_items === '0') ? '' : rtrim(rtrim($sum_no_of_items, '0'), '.');
            $sum_total_amount_disp = ($sum_total_amount === '0')
                ? ''
                : number_format((float)$sum_total_amount, 2);

            // Update global
            if ($sum_total_amount !== '0') {
                $global_sum = isset($global_sum) ? $global_sum + (float)$sum_total_amount : (float)$sum_total_amount;
            }
        @endphp

        <tr>
            <td rowspan="4">6</td>
            <td class="textstart" rowspan="4">{{ $head }}</td>
            <td class="textstart">{{ $sub_head_6a }}</td>
            <td></td>

            {{-- sum(no_of_items) shown neatly --}}
            <td>{{ $sum_no_of_items_disp }}</td>

            <td class="textend">
                @if($assigns_6a->isNotEmpty())
                    {{ number_format((float)$default_rate_6a, 0) }}
                @endif
            </td>

            {{-- sum(total_amount) with 2 decimals --}}
            <td class="textend">{{ $sum_total_amount_disp }}</td>
        </tr>





        {{-- Order 6.b --}}
        @php
            //$assign_6b = $teacher->rateAssigns->where('rateHead.order_no', '6.b')->first();
             $assign_6b = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '6.b';
               })->first();
            $rateAmount_6b = $rateAmount_order_6b ?? null;
            $sub_head_6b = $rateHead_order_6b->sub_head ?? '6.B';
            $default_rate_6b = $rateAmount_6b->default_rate ?? 0;

            if ($assign_6b && $assign_6b->total_amount) {
                $global_sum += $assign_6b->total_amount;
            }
        @endphp
        <tr>
            <td class="textstart">{{ $sub_head_6b }}</td>
            <td></td>
            <td>{{ $assign_6b->total_students ?? '' }}</td>
            <td class="textend">
                @if(isset($default_rate_6b) && $assign_6b)
                    {{ number_format($default_rate_6b, 0) }}
                @endif
            </td>
            {{--<td class="textend">{{ number_format($default_rate_6b, 2) }}</td>--}}
            <td class="textend">{{ isset($assign_6b->total_amount) ? number_format($assign_6b->total_amount, 2) : '' }}</td>
        </tr>

        {{-- Order 6.c --}}
        @php
            //$assign_6c = $teacher->rateAssigns->where('rateHead.order_no', '6.c')->first();
             $assign_6c = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '6.c';
               })->first();
            $rateAmount_6c = $rateAmount_order_6c ?? null;
            $sub_head_6c = $rateHead_order_6c->sub_head ?? '';
            $default_rate_6c = $rateAmount_6c->default_rate ?? 0;

            if ($assign_6c && $assign_6c->total_amount) {
                $global_sum += $assign_6c->total_amount;
            }
        @endphp
        <tr>
            <td class="textstart">{{ $sub_head_6c }}</td>
            <td></td>
            <td>{{ $assign_6c->total_students ?? '' }}</td>
            <td class="textend">
                @if(isset($default_rate_6c) && $assign_6c)
                    {{ number_format($default_rate_6c, 0) }}
                @endif
            </td>
            {{--<td class="textend">{{ number_format($default_rate_6c, 2) }}</td>--}}
            <td class="textend">{{ isset($assign_6c->total_amount) ? number_format($assign_6c->total_amount, 2) : '' }}</td>
        </tr>

        {{-- Order 6.d --}}
        @php
            //$assign_6d = $teacher->rateAssigns->where('rateHead.order_no', '6.d')->first();
             $assign_6d = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '6.d';
               })->first();
            $rateAmount_6d = $rateAmount_order_6d ?? null;
            $sub_head_6d = $rateHead_order_6d->sub_head ?? '';
            $default_rate_6d = $rateAmount_6d->default_rate ?? 0;

            if ($assign_6d && $assign_6d->total_amount) {
                $global_sum += $assign_6d->total_amount;
            }
        @endphp
        <tr>
            <td class="textstart">{{ $sub_head_6d }}</td>
            <td></td>
            <td>{{ $assign_6d->total_students ?? '' }}</td>
            <td class="textend">
                @if(isset($default_rate_6d) && $assign_6d)
                    {{ number_format($default_rate_6d, 2) }}
                @endif
            </td>
            {{--<td class="textend">{{ number_format($default_rate_6d, 2) }}</td>--}}
            <td class="textend">{{ isset($assign_6d->total_amount) ? number_format($assign_6d->total_amount, 2) : '' }}</td>
        </tr>


        {{-- Order 7.e/7.f --}} {{--for teacher--}}
        @php
            // 7.e (usually single)
            $assign_7e = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 1 &&
                       $assign->rateHead &&
                       $assign->rateHead->order_no == '7.e';
            });

             // Sum totals for this teacher across all 7.e rows (groups)
            $sum_students_7e = $assign_7e->sum('no_of_items');   // sum of group totals the teacher participated in
            $sum_amount_7e   = $assign_7e->sum('total_amount');     // sum of amounts for this teacher

            $rateAmount_7e   = $rateAmount_order_7e ?? null;
            $head_7          = $rateHead_order_7e->head ?? '';      // common head for section 7
            $sub_head_7e     = $rateHead_order_7e->sub_head ?? '';
            $default_rate_7e = $rateAmount_7e->default_rate ?? 0;

            if ($sum_amount_7e) {
                 $global_sum += $sum_amount_7e;
            }

            // 7.f (can be multiple like 8.b)
            $assigns_7f = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 1 &&
                       $assign->rateHead &&
                       $assign->rateHead->order_no == '7.f';
            });

            $total_assigns_7f = $assigns_7f->count();

            $rateAmount_7f   = $rateAmount_order_7f ?? null;
            $sub_head_7f     = $rateHead_order_7f->sub_head ?? '';
            $default_rate_7f = $rateAmount_7f->default_rate ?? 0;

            // total rows under section 7 = one row for 7.e + one-or-more for 7.f
            $rowspan_7_block = 1 + max(1, $total_assigns_7f);
        @endphp

        {{-- 7.e row (first row of section 7) --}}
        <tr>
            <td rowspan="{{ $rowspan_7_block }}">7</td>
            <td class="textstart" rowspan="{{ $rowspan_7_block }}">{{ $head_7 }}</td>
            <td class="textstart">{{ $sub_head_7e }}</td>
            <td></td>
            <td>
                {{ $sum_students_7e ? $sum_students_7e : '' }}
            </td>
            <td class="textend">
                {{ $assign_7e->isNotEmpty() ? number_format((float)$default_rate_7e, 2) : '' }}
            </td>
            <td class="textend">{{ $sum_amount_7e ? number_format((float)$sum_amount_7e, 2) : '' }}</td>
        </tr>

        {{-- 7.f rows (multi like 8.b) --}}
        @if ($total_assigns_7f > 0)
            @foreach ($assigns_7f as $assign)
                <tr>
                    @if ($loop->first)
                        {{-- sub-head cell spans all 7.f rows --}}
                        <td class="textstart" rowspan="{{ $total_assigns_7f }}">{{ $sub_head_7f }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    <td>{{ $assign->total_students ?? '' }}{{ isset($assign->total_teachers) ? '/'.$assign->total_teachers : '' }}</td>
                    <td class="textend">{{ number_format((float)$default_rate_7f, 2) }}</td>
                    <td class="textend">{{ number_format((float)($assign->total_amount ?? 0), 2) }}</td>
                </tr>
                @php $global_sum += $assign->total_amount ?? 0; @endphp
            @endforeach
        @else
            {{-- Fallback when no 7.f data --}}
            <tr>
                <td class="textstart">{{ $sub_head_7f }}</td>
                <td></td>
                <td></td>
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif



        @php
            //$assigns_order_8a = $teacher->rateAssigns->where('rateHead.order_no', '8.a');
            $assigns_order_8a = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '8.a';
               });
            //$assigns_order_8b = $teacher->rateAssigns->where('rateHead.order_no', '8.b');
             $assigns_order_8b = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '8.b';
               });

             $assigns_order_8c = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '8.c';
               })->first();


            /*$assigns_order_8d = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
            $assign->exam_type_id == 1 &&
            $assign->rateHead &&
                          $assign->rateHead->order_no == '8.d';
               })->first();*/

            $total_assigns_8a = $assigns_order_8a->count();
            $total_assigns_8b = $assigns_order_8b->count();

            // Total number of rows for section 8 (8.a + 8.b + 8.c + 8.d)
            $rowspan_8_block = max(1, $total_assigns_8a) + max(1, $total_assigns_8b) + 1 + 1;

            $head_8a = $rateHead_order_8a->head ?? 'Gradesheet Preparation--';
            $sub_head_8a = $rateHead_order_8a->sub_head ?? 'Theoretical*';
            $rateAmount_8a_default_rate = $rateAmount_order_8a->default_rate ?? '';

            $head_8b = $rateHead_order_8b->head ?? 'Gradesheet Preparation--';
            $sub_head_8b = $rateHead_order_8b->sub_head ?? 'Sessional*';
            $rateAmount_8b_default_rate = $rateAmount_order_8b->default_rate ?? '';



            $head_8c = $rateHead_order_8c->head ?? 'Empty';
            $rateAmount_8c_default_rate = $rateAmount_order_8c->default_rate ?? '';

            $head_8d = $rateHead_order_8d->head ?? 'Empty';
            $rateAmount_8d_default_rate = $rateAmount_order_8d->default_rate ?? '';
        @endphp

        {{-- 8.a rows --}}
        @if ($total_assigns_8a > 0)
            @foreach ($assigns_order_8a as $assign)
                <tr>
                    @if ($loop->first)
                        <td rowspan="{{ $rowspan_8_block }}">8</td>
                        <td class="textstart"
                            rowspan="{{ max(1, $total_assigns_8a) + max(1, $total_assigns_8b) }}">{{ $head_8a }}</td>
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
                <td rowspan="{{ max(1, $total_assigns_8a) + max(1, $total_assigns_8b) }}"
                    class="textstart">{{ $head_8a }}</td>
                <td class="textstart">{{ $sub_head_8a }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ number_format((float)$rateAmount_8a_default_rate, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif

        {{-- 8.b rows --}}
        @if ($total_assigns_8b > 0)
            @foreach ($assigns_order_8b as $assign)
                <tr>
                    @if ($loop->first)
                        <td class="textstart" rowspan="{{ $total_assigns_8b }}">{{ $sub_head_8b }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    <td>{{$assign->total_students}}/{{$assign->total_teachers}}</td>
                    <td class="textend">{{ number_format((float)$rateAmount_8b_default_rate, 2) }}</td>
                    <td class="textend">{{ number_format((float)($assign->total_amount ?? 0), 2) }}</td>
                    @php $global_sum += $assign->total_amount ?? 0; @endphp
                </tr>
            @endforeach
        @else
            <tr>
                <td class="textstart">{{ $sub_head_8b }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ number_format((float)$rateAmount_8b_default_rate, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif

        {{-- Order = 8.c --}}
        @php
            if ($assigns_order_8c && $assigns_order_8c->total_amount) {
                $global_sum += $assigns_order_8c->total_amount;
            }
        @endphp
        <tr>
            <td class="textstart" colspan="2">{{ $head_8c }}</td>
            <td></td>
            @if($assigns_order_8c)
                <td>
                    {{ $assigns_order_8c->total_students ?? '' }}/{{ $assigns_order_8c->total_teachers ?? '' }}
                </td>
                <td class="textend">
                    {{ is_numeric($rateAmount_8c_default_rate) ? number_format((float) $rateAmount_8c_default_rate, 2) : '' }}
                </td>
            @else
                <td></td>
                <td></td>
            @endif
            {{-- <td>{{ $assigns_order_8c->total_students ?? '' }}/{{ $assigns_order_8c->total_teachers ??'' }}</td>
             <td class="textend">
                 {{ is_numeric($rateAmount_8c_default_rate) ? number_format((float) $rateAmount_8c_default_rate, 2) : '' }}
             </td>--}}
            <td class="textend">{{ isset($assigns_order_8c->total_amount) ? number_format((float)$assigns_order_8c->total_amount, 2) : '' }}</td>
        </tr>


        {{-- Order = 8.d --}}
        @php
            $assign_8_d = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 1 &&
                       $assign->rateHead &&
                       $assign->rateHead->order_no == '8.d';
            });
             // just the course_code values (unique, trimmed)
            $course_codes = $assign_8_d
                ->pluck('course_code')
                ->filter(fn($c) => filled($c))
                ->map(fn($c) => trim($c))
                ->unique()
                ->values()
                ->all();
            // collect "s/t" per course for THIS teacher
            $fraction_parts = [];

            $total_assigns = $assign_8_d->count();

            $rateHead=\App\Models\RateHead::where('order_no','8.d')->first();
            $head = $rateHead->head;
            $rateAmount_8_d = $rateAmount_order_8d ?? null;
            $default_rate_8_d = $rateAmount_8_d->default_rate ?? 0;

            $total_student_all_course = 0;
            $total_amount_all_course = 0;
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assign_8_d as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                   /* $total_student_all_course += $assign->total_students ?? 0;*/
                    $total_student_all_course += $assign->no_of_items ?? 0;
                    $total_amount_all_course += $assign->total_amount ?? 0;

                    // build "students/teachers" text from the row (no extra queries)
                    $s = (int)   ($assign->total_students  ?? 0);
                    $t = max(1, (int) ($assign->total_teachers ?? 0)); // guard divide-by-zero
                    $fraction_parts[] = "{$s}/{$t}";
                @endphp
            @endforeach

            @php
                $chunks = array_chunk($fraction_parts, 3);
                $pretty_total = rtrim(rtrim(number_format($total_student_all_course, 2, '.', ''), '0'), '.');
            @endphp
            <tr>
                <td class="textstart" colspan="2">{{ $head }}</td>
                <td class="textstart">{{ implode(', ', $course_codes) }}</td>
                {{--<td>{{ $total_student_all_course }}/2</td>--}}
                <td class="textcenter">
                    @foreach ($chunks as $i => $chunk)
                        {{ implode(' + ', $chunk) }}@if ($i < count($chunks) - 1) +<br>@endif
                    @endforeach
                </td>
                <td class="textend">{{ number_format($default_rate_8_d, 2) }}</td>
                <td class="textend">{{ number_format($total_amount_all_course, 2) }}</td>
            </tr>
        @else
            {{-- Show default row if no assign exists --}}
            <tr>
                <td class="textstart" colspan="2">{{ $head }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ number_format($default_rate_8_d, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif






        {{-- Order = 9 --}}
        @php
            //$assigns_order_9 = $teacher->rateAssigns->where('rateHead.order_no', '9');
            $assigns_order_9 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
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
                    <td class="textend">{{ isset($default_rate) ? number_format($default_rate, 0) : '' }}</td>
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
                       $assign->exam_type_id == 1 &&
                       $assign->rateHead &&
                       $assign->rateHead->order_no == '10.a';
            });

             // just the course_code values (unique, trimmed)
            $course_codes = $assign_10_a
                ->pluck('course_code')
                ->filter(fn($c) => filled($c))
                ->map(fn($c) => trim($c))
                ->unique()
                ->values()
                ->all();
            // collect "s/t" per course for THIS teacher
            $fraction_parts = [];

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

                     // build "students/teachers" text from the row (no extra queries)
                    $s = (int)   ($assign->total_students  ?? 0);
                    $t = max(1, (int) ($assign->total_teachers ?? 0)); // guard divide-by-zero
                    $fraction_parts[] = "{$s}/{$t}";
                @endphp
            @endforeach

            @php
                $chunks = array_chunk($fraction_parts, 3);
                $pretty_total = rtrim(rtrim(number_format($total_student_all_course, 2, '.', ''), '0'), '.');
            @endphp
            <tr>
                <td rowspan="2">10</td>
                <td class="textstart" rowspan="2">{{ $head }}</td>
                <td class="textstart">(a) {{ $sub_head_10_a }}</td>
                <td class="textstart">{{ implode(', ', $course_codes) }}</td>
                {{--<td>{{ $total_student_all_course }}/2</td>--}}
                <td class="textcenter">
                    @foreach ($chunks as $i => $chunk)
                        {{ implode(' + ', $chunk) }}@if ($i < count($chunks) - 1) +<br>@endif
                    @endforeach
                </td>
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
            $assign->exam_type_id == 1 &&
        @endif





        {{-- Order 10.b --}}
        {{--@php
            //$assign_10_b = $teacher->rateAssigns->where('rateHead.order_no', '10.b')->first();
            $assign_10_b = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '10.b';
               })->first();
            $rateAmount_10_b = $rateAmount_order_10_b ?? null;
            $sub_head_10_b = $rateHead_order_10_b->sub_head ?? '6.B';
            $default_rate_10_b = $rateAmount_10_b->default_rate ?? 0;

            if ($assign_10_b && $assign_10_b->total_amount) {
                $global_sum += $assign_10_b->total_amount;
            }
        @endphp
        <tr>
            <td class="textstart">(b) {{ $sub_head_10_b }}</td>
            <td></td>
            <td>{{ $assign_10_b->no_of_items ?? '' }}</td>
            <td class="textend">{{ number_format($default_rate_10_b, 2) }}</td>
            <td class="textend">{{ isset($assign_10_b->total_amount) ? number_format($assign_10_b->total_amount, 2) : '' }}</td>
        </tr>--}}


        {{-- Order = 10.b --}}
        @php
            $assign_10_b = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 1 &&
                       $assign->rateHead &&
                       $assign->rateHead->order_no == '10.b';
            });

            // just the course_code values (unique, trimmed)
            $course_codes = $assign_10_b
                ->pluck('course_code')
                ->filter(fn($c) => filled($c))
                ->map(fn($c) => trim($c))
                ->unique()
                ->values()
                ->all();
            // collect "s/t" per course for THIS teacher
            $fraction_parts = [];

            $total_assigns = $assign_10_b->count();

            $rateHead=\App\Models\RateHead::where('order_no','10.b')->first();
            $head = $rateHead->head;
            $sub_head_10_b = $rateHead->sub_head;
            $rateAmount_10_b = $rateAmount_order_10_b ?? null;
            $default_rate_10_b = $rateAmount_10_b->default_rate ?? 0;

            $total_student_all_course = 0;
            $total_amount_all_course = 0;
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assign_10_b as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                    /*$total_student_all_course += $assign->total_students ?? 0;*/
                    $total_student_all_course += $assign->no_of_items ?? 0;
                    $total_amount_all_course += $assign->total_amount ?? 0;

                    // build "students/teachers" text from the row (no extra queries)
                    $s = (int)   ($assign->total_students  ?? 0);
                    $t = max(1, (int) ($assign->total_teachers ?? 0)); // guard divide-by-zero
                    $fraction_parts[] = "{$s}/{$t}";
                @endphp
            @endforeach

            @php
                $chunks = array_chunk($fraction_parts, 3);
                $pretty_total = rtrim(rtrim(number_format($total_student_all_course, 2, '.', ''), '0'), '.');
            @endphp
            <tr>
                <td class="textstart">(b) {{ $sub_head_10_b }}</td>
                <td class="textstart">{{ implode(', ', $course_codes) }}</td>
                {{--<td>{{ $total_student_all_course }}/2</td>--}}
                <td class="textcenter">
                    @foreach ($chunks as $i => $chunk)
                        {{ implode(' + ', $chunk) }}@if ($i < count($chunks) - 1) +<br>@endif
                    @endforeach
                </td>
                <td class="textend">{{ number_format($default_rate_10_b, 2) }}</td>
                <td class="textend">{{ number_format($total_amount_all_course, 2) }}</td>
            </tr>
        @else
            {{-- Show default row if no assign exists --}}
            <tr>
                <td class="textstart">(a) {{ $sub_head_10_b }}</td>
                <td></td>
                <td></td>
                {{-- <td class="textend">{{ number_format($default_rate_10_b, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif







        {{--Order 11--}}
        {{-- Order 11 --}}
        @php
            $assigns_order_11 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                 return $assign->session_id == $session_info->id &&
                        $assign->exam_type_id == 1 &&
                        $assign->rateHead &&
                        $assign->rateHead->order_no == '11';
             });

            $head_order_11   = $rateHead_order_11->head ?? '';
            $default_rate_11 = $rateAmount_order_11->default_rate ?? 0;

            $sum_questions_11 = $assigns_order_11->sum('no_of_items');   // per-teacher share of questions
            $sum_amount_11    = $assigns_order_11->sum('total_amount');  // total money

            if ($sum_amount_11 > 0) {
                $global_sum += $sum_amount_11;
            }
        @endphp
        <tr>
            <td>11</td>
            <td class="textstart" colspan="2">{{ $head_order_11 }}</td>
            <td></td>
            <td>
                {{ $sum_questions_11 > 0 ? number_format($sum_questions_11, 2) : '' }}
            </td>
            <td class="textend">
                @if($sum_questions_11 > 0)
                    {{ number_format($default_rate_11, 0) }}
                @endif
            </td>
            <td class="textend">{{ $sum_amount_11 > 0 ? number_format($sum_amount_11, 2) : '' }}</td>
        </tr>



        {{-- Order 12.a/12.b --}}
        @php
            // Collect ALL 12.a assigns for this teacher (no ->first)
            $assigns_12_a = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 1 &&
                       $assign->rateHead &&
                       $assign->rateHead->order_no == '12.a';
            });

            // Totals (sum across groups/rows)
            $sum_stencils_12_a = $assigns_12_a->sum('no_of_items');
            $sum_amount_12_a   = $assigns_12_a->sum('total_amount');

            // RateHead / RateAmount
            $rateAmount_12_a   = $rateAmount_order_12_a ?? null;
            $head_12           = $rateHead_order_12_a->head ?? '';
            $sub_head_12_a     = $rateHead_order_12_a->sub_head ?? '12.a';
            $default_rate_12_a = $rateAmount_12_a->default_rate ?? 0;

            // Add to global total
            if ($sum_amount_12_a > 0) {
                $global_sum += $sum_amount_12_a;
            }
        @endphp
        <tr>
            <td rowspan="2">12</td>
            <td class="textstart" rowspan="2">{{ $head_12 }}</td>

            <td class="textstart">(a) {{ $sub_head_12_a }}</td>
            <td></td>
            <td>{{ $sum_stencils_12_a ? number_format((float)$sum_stencils_12_a, 2) : '' }}</td>

            <td class="textend">
                {{ $assigns_12_a->isNotEmpty() ? number_format((float) $default_rate_12_a, 2) : '' }}
            </td>

            <td class="textend">
                {{ $sum_amount_12_a ? number_format((float) $sum_amount_12_a, 2) : '' }}
            </td>
        </tr>


        {{-- Order 12.b --}}
        @php
            //$assign_12_b = $teacher->rateAssigns->where('rateHead.order_no', '12.b')->first();
             $assign_12_b = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '12.b';
               });


              // Totals (if you saved equal-split, no_of_items is the per-teacher share; sum is total for this teacher)
            $sum_stencils_12_b = $assign_12_b->sum('no_of_items');
            $sum_amount_12_b   = $assign_12_b->sum('total_amount');


            // RateHead / RateAmount for 12.b (provided by controller like you did for 7.e)
            $rateAmount_12_b   = $rateAmount_order_12_b ?? null;
            $sub_head_12_b     = $rateHead_order_12_b->sub_head ?? '12.b';
            $default_rate_12_b = $rateAmount_12_b->default_rate ?? 0;


             // Add to global total
            if ($sum_amount_12_b) {
                $global_sum += $sum_amount_12_b;
            }
        @endphp
        <tr>
            <td class="textstart">(b) {{ $sub_head_12_b }}</td>
            <td></td>
            <td>{{ $sum_stencils_12_b ? number_format((float)$sum_stencils_12_b, 2) : '' }}</td>
            <td class="textend">
                {{ $assign_12_b->isNotEmpty() ? number_format((float) $default_rate_12_b, 2) : '' }}
            </td>
            <td class="textend">
                {{ $sum_amount_12_b ? number_format((float) $sum_amount_12_b, 2) : '' }}
            </td>
        </tr>


        {{-- Order 13 --}}
        @php
            //$assign_13 = $teacher->rateAssigns->where('rateHead.order_no', '13')->first();
            $assign_13 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '13';
               })->first();
            $head_order_13 = $rateHead_order_13->head ?? 'Error';
            $rateAmount_13 = $rateAmount_order_13 ?? null;
            $default_rate_13 = $rateAmount_13->default_rate ?? 0;

            if ($assign_13 && $assign_13->total_amount) {
                $global_sum += $assign_13->total_amount;
            }
        @endphp
        <tr>
            <td>13</td>
            <td class="textstart" colspan="2">{{ $head_order_13 }}</td>
            <td></td>
            <td>{{ $assign_13->total_students ?? '' }}</td>
            @if($assign_13)
                <td class="textend">{{ isset($default_rate_13) ? number_format($default_rate_13, 2) : '' }}</td>
            @else
                <td class="textend"></td>
            @endif
            <td class="textend">{{ isset($assign_13->total_amount) ? number_format($assign_13->total_amount, 2) : '' }}</td>
        </tr>


        {{-- Order 14 --}}
        @php
            //$assigns_order_14 = $teacher->rateAssigns->where('rateHead.order_no', '14')->first();
             $assigns_order_14 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '14';
               })->first();
            $head_order_14 = $rateHead_order_14->head ?? 'Error';
            $default_rate_14 = $rateAmount_order_14->default_rate ?? 0;
            if ($assigns_order_14 && $assigns_order_14->total_amount) {
                $global_sum += $assigns_order_14->total_amount;
            }
        @endphp
        <tr>
            <td>14</td>
            <td class="textstart" colspan="2">{{ $head_order_14 }}</td>
            <td></td>
            <td></td>
            {{--<td class="textend">{{ number_format($default_rate_14, 2) }}</td>--}}
            <td class="textend"></td>
            <td class="textend">{{ isset($assigns_order_14->total_amount) ? number_format($assigns_order_14->total_amount, 2) : '' }}</td>
        </tr>

        {{-- Order 15 --}}
        @php
            //$assigns_order_15 = $teacher->rateAssigns->where('rateHead.order_no', '15')->first();
            $assigns_order_15 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
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
            //$assign_16 = $teacher->rateAssigns->where('rateHead.order_no', '16')->first();
            $assign_16 = $teacher->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '16';
               })->first();
            $head_order_16 = $rateHead_order_16->head ?? 'Error';
            $rateAmount_16 = $rateAmount_order_16 ?? null;
            $default_rate_16 = $rateAmount_16->default_rate ?? 0;

            if ($assign_16 && $assign_16->total_amount) {
                $global_sum += $assign_16->total_amount;
            }
        @endphp
        <tr>
            <td>16</td>
            <td class="textstart" colspan="2">{{ $head_order_16 }}</td>
            <td></td>
            <td>{{ $assign_16->total_students ?? '' }}</td>
            <td class="textend">
                @if(isset($default_rate_16) && $assign_16)
                    {{ number_format($default_rate_16, 2) }}
                @endif
            </td>
            <td class="textend">{{ isset($assign_16->total_amount) ? number_format($assign_16->total_amount, 2) : '' }}</td>
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
            <td style="width: 30%;" class="pt-20">
                ----------------------------------------------------------------------
            </td>
            <td style="width: 30%;" class="pt-20" style="text-align: right">-----------approved</td>
        </tr>
        <tr>
            <td class="pt-40">Signature of Examiner</td>
            <td class="pt-40">Prepared by</td>
            <td class="pt-40">Assistant Comptroller</td>
            <td class="pt-40">Comptroller (In Charge)</td>
        </tr>
    </table>

    {{-- Revenue Stamp note --}}
    <table class="revenue_table">
        <tr>
            <td style="width: 20%;">
                <div class="revenue-stamp-box">
                    <span>Revenue<br>Stamp</span>
                </div>
            </td>
            <td style="width: 60%; text-align: center; vertical-align: bottom; padding-bottom: 0;">
                N.B. For bill 400 Tk or above use Revenue Stamp of 10 Tk
            </td>
            <td style="width: 20%; text-align: right; vertical-align: bottom; padding-bottom: 0;">
                # MRMR
            </td>
        </tr>
    </table>

    @if (!$loop->last)
        <div class="page-break"></div>
    @endif

@endforeach
@php } @endphp

<div class="page-break"></div>
@php
    if ($user->hasRole('Employee') || $user->hasRole('Admin') || $user->hasRole('SuperAdmin')) {
@endphp
@foreach($employees as  $employee)

    @php
        $user = auth()->user();
        $isAdmin = $user->hasAnyRole(['Admin','SuperAdmin']);
        $isEmployeeOnly = $user->hasRole('Employee') && !$isAdmin;
        $uid = $user->id;
    @endphp
    @php
        // Skip other employee if the user is a teacher
        if ($isEmployeeOnly && $uid !== $employee->user_id) {
            continue;
        }
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
            <td colspan="2" style="padding-top: 15px; text-align: left;padding-left: 5px;">
                <strong>Name:</strong> {{ $employee->user->name }}
            </td>
            <td colspan="1" style="padding-top: 15px;">
                <div style="transform: translateX(-90px);">
                    <strong>Designation:</strong> {{ $employee->designation->designation }}
                </div>
            </td>
            <td style="padding-top: 15px;">
                <strong>Department:</strong> {{ $employee->department->shortname }}, DUET
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
            //$assigns_order_1 = $employee->rateAssigns->where('rateHead.order_no', '1');
            $assigns_order_1 = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 1 &&
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
            @if($assigns_order_1->isNotEmpty())
                <td class="textend">max. {{ number_format($max_rate, 0) }}</td>
            @else
                <td></td>
            @endif
            <td rowspan="2" class="textend">{{ $total_taka == 0 ? '' : number_format($total_taka, 2) }}</td>
        </tr>
        <tr>
            @if($assigns_order_1->isNotEmpty())
                <td class="textend">min. {{ number_format($min_rate, 0) }}</td>
            @else
                <td></td>
            @endif
        </tr>


        {{-- Order = 2 --}}
        @php
            //$assigns_order_2 = $employee->rateAssigns->where('rateHead.order_no', '2');
            /* $assigns_order_2 = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                 return $assign->session_id == $session_info->id &&
            $assign->exam_type_id == 1 &&
            $assign->rateHead &&
                        $assign->rateHead->order_no == '2';
             });*/
             $assigns_order_2 = App\Models\RateAssign::where('employee_id', $employee->id)
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
            //$assigns_order_3 = $employee->rateAssigns->where('rateHead.order_no', '3');
             $assigns_order_3 = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
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
            //$assigns_order_4 = $employee->rateAssigns->where('rateHead.order_no', '4');
            $assigns_order_4 = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '4';
               });
            $total_assigns = $assigns_order_4->count();
            $loopIndex = 0;

            $head = $rateHead_order_4->head ?? 'Class Test';
            $default_rate = $rateAmount_order_4->default_rate ?? 0;
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assigns_order_4 as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                @endphp
                <tr>
                    @if ($loopIndex == 0)
                        <td rowspan="{{ $total_assigns }}">4</td>
                        <td class="textstart" colspan="2" rowspan="{{ $total_assigns }}">{{ $head }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    <td>{{$assign->total_students}}*2</td>
                    <td class="textend">{{ number_format($default_rate, 2) }}</td>
                    <td class="textend">{{ isset($assign->total_amount) ? number_format($assign->total_amount, 2) : '' }}</td>
                </tr>
                @php $loopIndex++; @endphp
            @endforeach
        @else
            {{-- Fallback row if no data --}}
            <tr>
                <td rowspan="1">4</td>
                <td class="textstart" colspan="2" rowspan="1">{{ $head }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ number_format($default_rate, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif





        {{-- Order = 5 --}}
        @php
            //$assigns_order_5 = $employee->rateAssigns->where('rateHead.order_no', '5');
            $assigns_order_5 = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '5';
               });
            $total_assigns = $assigns_order_5->count();
            $loopIndex = 0;

            $head = $rateHead_order_5->head ?? 'Laboratory/Survey works';
            $default_rate = $rateAmount_order_5->default_rate ?? 0;
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assigns_order_5 as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                @endphp
                <tr>
                    @if ($loopIndex == 0)
                        <td rowspan="{{ $total_assigns }}">5</td>
                        <td class="textstart" colspan="2" rowspan="{{ $total_assigns }}">{{ $head }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    {{--here we show total week--}}
                    <td>{{$assign->total_students}} weeks</td>
                    {{--<td class="textend">{{ number_format($default_rate, 2) }}</td>--}}
                    <td class="textend">{{ number_format($default_rate * $assign->no_of_items, 0) }}</td>
                    <td class="textend">{{ isset($assign->total_amount) ? number_format($assign->total_amount, 2) : '' }}</td>
                </tr>
                @php $loopIndex++; @endphp
            @endforeach
        @else
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
        @endif






        {{-- Order 6.a/b/c/d --}}
        @php
            // ALL 6.a rows for this teacher (session + exam type)
            $assigns_6a = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id
                    && $assign->exam_type_id == 1
                    && $assign->rateHead
                    && $assign->rateHead->order_no == '6.a';
            });

            $rateAmount_6a   = $rateAmount_order_6a ?? null;
            $head            = $rateHead_order_6a->head ?? '';
            $sub_head_6a     = $rateHead_order_6a->sub_head ?? '6.A';
            $default_rate_6a = $rateAmount_6a->default_rate ?? 0;

            // Accurate sums with BCMath (fall back to float if BCMath missing)
            $scale = 10;
            $sum_no_of_items = '0';
            $sum_total_amount = '0';

            foreach ($assigns_6a as $a) {
                if (function_exists('bcadd')) {
                    $sum_no_of_items  = bcadd($sum_no_of_items,  (string)$a->no_of_items,  $scale);
                    $sum_total_amount = bcadd($sum_total_amount, (string)$a->total_amount, $scale);
                } else {
                    $sum_no_of_items  = (string)((float)$sum_no_of_items  + (float)$a->no_of_items);
                    $sum_total_amount = (string)((float)$sum_total_amount + (float)$a->total_amount);
                }
            }

            // Pretty display: no_of_items without trailing zeros, currency with 2 dp
            $sum_no_of_items_disp = ($sum_no_of_items === '0') ? '' : rtrim(rtrim($sum_no_of_items, '0'), '.');
            $sum_total_amount_disp = ($sum_total_amount === '0')
                ? ''
                : number_format((float)$sum_total_amount, 2);

            // Update global
            if ($sum_total_amount !== '0') {
                $global_sum = isset($global_sum) ? $global_sum + (float)$sum_total_amount : (float)$sum_total_amount;
            }
        @endphp

        <tr>
            <td rowspan="4">6</td>
            <td class="textstart" rowspan="4">{{ $head }}</td>
            <td class="textstart">{{ $sub_head_6a }}</td>
            <td></td>

            {{-- sum(no_of_items) shown neatly --}}
            <td>{{ $sum_no_of_items_disp }}</td>

            <td class="textend">
                @if($assigns_6a->isNotEmpty())
                    {{ number_format((float)$default_rate_6a, 0) }}
                @endif
            </td>

            {{-- sum(total_amount) with 2 decimals --}}
            <td class="textend">{{ $sum_total_amount_disp }}</td>
        </tr>





        {{-- Order 6.b --}}
        @php
            //$assign_6b = $employee->rateAssigns->where('rateHead.order_no', '6.b')->first();
             $assign_6b = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '6.b';
               })->first();
            $rateAmount_6b = $rateAmount_order_6b ?? null;
            $sub_head_6b = $rateHead_order_6b->sub_head ?? '6.B';
            $default_rate_6b = $rateAmount_6b->default_rate ?? 0;

            if ($assign_6b && $assign_6b->total_amount) {
                $global_sum += $assign_6b->total_amount;
            }
        @endphp
        <tr>
            <td class="textstart">{{ $sub_head_6b }}</td>
            <td></td>
            <td>{{ $assign_6b->total_students ?? '' }}</td>
            <td class="textend">
                @if(isset($default_rate_6b) && $assign_6b)
                    {{ number_format($default_rate_6b, 0) }}
                @endif
            </td>
            {{--<td class="textend">{{ number_format($default_rate_6b, 2) }}</td>--}}
            <td class="textend">{{ isset($assign_6b->total_amount) ? number_format($assign_6b->total_amount, 2) : '' }}</td>
        </tr>

        {{-- Order 6.c --}}
        @php
            //$assign_6c = $employee->rateAssigns->where('rateHead.order_no', '6.c')->first();
             $assign_6c = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '6.c';
               })->first();
            $rateAmount_6c = $rateAmount_order_6c ?? null;
            $sub_head_6c = $rateHead_order_6c->sub_head ?? '';
            $default_rate_6c = $rateAmount_6c->default_rate ?? 0;

            if ($assign_6c && $assign_6c->total_amount) {
                $global_sum += $assign_6c->total_amount;
            }
        @endphp
        <tr>
            <td class="textstart">{{ $sub_head_6c }}</td>
            <td></td>
            <td>{{ $assign_6c->total_students ?? '' }}</td>
            <td class="textend">
                @if(isset($default_rate_6c) && $assign_6c)
                    {{ number_format($default_rate_6c, 0) }}
                @endif
            </td>
            {{--<td class="textend">{{ number_format($default_rate_6c, 2) }}</td>--}}
            <td class="textend">{{ isset($assign_6c->total_amount) ? number_format($assign_6c->total_amount, 2) : '' }}</td>
        </tr>

        {{-- Order 6.d --}}
        @php
            //$assign_6d = $employee->rateAssigns->where('rateHead.order_no', '6.d')->first();
             $assign_6d = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '6.d';
               })->first();
            $rateAmount_6d = $rateAmount_order_6d ?? null;
            $sub_head_6d = $rateHead_order_6d->sub_head ?? '';
            $default_rate_6d = $rateAmount_6d->default_rate ?? 0;

            if ($assign_6d && $assign_6d->total_amount) {
                $global_sum += $assign_6d->total_amount;
            }
        @endphp
        <tr>
            <td class="textstart">{{ $sub_head_6d }}</td>
            <td></td>
            <td>{{ $assign_6d->total_students ?? '' }}</td>
            <td class="textend">
                @if(isset($default_rate_6d) && $assign_6d)
                    {{ number_format($default_rate_6d, 2) }}
                @endif
            </td>
            {{--<td class="textend">{{ number_format($default_rate_6d, 2) }}</td>--}}
            <td class="textend">{{ isset($assign_6d->total_amount) ? number_format($assign_6d->total_amount, 2) : '' }}</td>
        </tr>


        {{-- Order 7.e/7.f --}} {{--for teacher--}}
        @php
            // 7.e (usually single)
            $assign_7e = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 1 &&
                       $assign->rateHead &&
                       $assign->rateHead->order_no == '7.e';
            });

             // Sum totals for this teacher across all 7.e rows (groups)
            $sum_students_7e = $assign_7e->sum('no_of_items');   // sum of group totals the teacher participated in
            $sum_amount_7e   = $assign_7e->sum('total_amount');     // sum of amounts for this teacher

            $rateAmount_7e   = $rateAmount_order_7e ?? null;
            $head_7          = $rateHead_order_7e->head ?? '';      // common head for section 7
            $sub_head_7e     = $rateHead_order_7e->sub_head ?? '';
            $default_rate_7e = $rateAmount_7e->default_rate ?? 0;

            if ($sum_amount_7e) {
                 $global_sum += $sum_amount_7e;
            }

            // 7.f (can be multiple like 8.b)
            $assigns_7f = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 1 &&
                       $assign->rateHead &&
                       $assign->rateHead->order_no == '7.f';
            });

            $total_assigns_7f = $assigns_7f->count();

            $rateAmount_7f   = $rateAmount_order_7f ?? null;
            $sub_head_7f     = $rateHead_order_7f->sub_head ?? '';
            $default_rate_7f = $rateAmount_7f->default_rate ?? 0;

            // total rows under section 7 = one row for 7.e + one-or-more for 7.f
            $rowspan_7_block = 1 + max(1, $total_assigns_7f);
        @endphp

        {{-- 7.e row (first row of section 7) --}}
        <tr>
            <td rowspan="{{ $rowspan_7_block }}">7</td>
            <td class="textstart" rowspan="{{ $rowspan_7_block }}">{{ $head_7 }}</td>
            <td class="textstart">{{ $sub_head_7e }}</td>
            <td></td>
            <td>
                {{ $sum_students_7e ? $sum_students_7e : '' }}
            </td>
            <td class="textend">
                {{ $assign_7e->isNotEmpty() ? number_format((float)$default_rate_7e, 2) : '' }}
            </td>
            <td class="textend">{{ $sum_amount_7e ? number_format((float)$sum_amount_7e, 2) : '' }}</td>
        </tr>

        {{-- 7.f rows (multi like 8.b) --}}
        @if ($total_assigns_7f > 0)
            @foreach ($assigns_7f as $assign)
                <tr>
                    @if ($loop->first)
                        {{-- sub-head cell spans all 7.f rows --}}
                        <td class="textstart" rowspan="{{ $total_assigns_7f }}">{{ $sub_head_7f }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    <td>{{ $assign->total_students ?? '' }}{{ isset($assign->total_teachers) ? '/'.$assign->total_teachers : '' }}</td>
                    <td class="textend">{{ number_format((float)$default_rate_7f, 2) }}</td>
                    <td class="textend">{{ number_format((float)($assign->total_amount ?? 0), 2) }}</td>
                </tr>
                @php $global_sum += $assign->total_amount ?? 0; @endphp
            @endforeach
        @else
            {{-- Fallback when no 7.f data --}}
            <tr>
                <td class="textstart">{{ $sub_head_7f }}</td>
                <td></td>
                <td></td>
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif



        @php
            //$assigns_order_8a = $employee->rateAssigns->where('rateHead.order_no', '8.a');
            $assigns_order_8a = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '8.a';
               });
            //$assigns_order_8b = $employee->rateAssigns->where('rateHead.order_no', '8.b');
             $assigns_order_8b = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '8.b';
               });

             $assigns_order_8c = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '8.c';
               })->first();


            /*$assigns_order_8d = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
            $assign->exam_type_id == 1 &&
            $assign->rateHead &&
                          $assign->rateHead->order_no == '8.d';
               })->first();*/

            $total_assigns_8a = $assigns_order_8a->count();
            $total_assigns_8b = $assigns_order_8b->count();

            // Total number of rows for section 8 (8.a + 8.b + 8.c + 8.d)
            $rowspan_8_block = max(1, $total_assigns_8a) + max(1, $total_assigns_8b) + 1 + 1;

            $head_8a = $rateHead_order_8a->head ?? 'Gradesheet Preparation--';
            $sub_head_8a = $rateHead_order_8a->sub_head ?? 'Theoretical*';
            $rateAmount_8a_default_rate = $rateAmount_order_8a->default_rate ?? '';

            $head_8b = $rateHead_order_8b->head ?? 'Gradesheet Preparation--';
            $sub_head_8b = $rateHead_order_8b->sub_head ?? 'Sessional*';
            $rateAmount_8b_default_rate = $rateAmount_order_8b->default_rate ?? '';



            $head_8c = $rateHead_order_8c->head ?? 'Empty';
            $rateAmount_8c_default_rate = $rateAmount_order_8c->default_rate ?? '';

            $head_8d = $rateHead_order_8d->head ?? 'Empty';
            $rateAmount_8d_default_rate = $rateAmount_order_8d->default_rate ?? '';
        @endphp

        {{-- 8.a rows --}}
        @if ($total_assigns_8a > 0)
            @foreach ($assigns_order_8a as $assign)
                <tr>
                    @if ($loop->first)
                        <td rowspan="{{ $rowspan_8_block }}">8</td>
                        <td class="textstart"
                            rowspan="{{ max(1, $total_assigns_8a) + max(1, $total_assigns_8b) }}">{{ $head_8a }}</td>
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
                <td rowspan="{{ max(1, $total_assigns_8a) + max(1, $total_assigns_8b) }}"
                    class="textstart">{{ $head_8a }}</td>
                <td class="textstart">{{ $sub_head_8a }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ number_format((float)$rateAmount_8a_default_rate, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif

        {{-- 8.b rows --}}
        @if ($total_assigns_8b > 0)
            @foreach ($assigns_order_8b as $assign)
                <tr>
                    @if ($loop->first)
                        <td class="textstart" rowspan="{{ $total_assigns_8b }}">{{ $sub_head_8b }}</td>
                    @endif
                    <td>{{ $assign->course_code ?? '' }}</td>
                    <td>{{$assign->total_students}}/{{$assign->total_teachers}}</td>
                    <td class="textend">{{ number_format((float)$rateAmount_8b_default_rate, 2) }}</td>
                    <td class="textend">{{ number_format((float)($assign->total_amount ?? 0), 2) }}</td>
                    @php $global_sum += $assign->total_amount ?? 0; @endphp
                </tr>
            @endforeach
        @else
            <tr>
                <td class="textstart">{{ $sub_head_8b }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ number_format((float)$rateAmount_8b_default_rate, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif

        {{-- Order = 8.c --}}
        @php
            if ($assigns_order_8c && $assigns_order_8c->total_amount) {
                $global_sum += $assigns_order_8c->total_amount;
            }
        @endphp
        <tr>
            <td class="textstart" colspan="2">{{ $head_8c }}</td>
            <td></td>
            @if($assigns_order_8c)
                <td>
                    {{ $assigns_order_8c->total_students ?? '' }}/{{ $assigns_order_8c->total_teachers ?? '' }}
                </td>
                <td class="textend">
                    {{ is_numeric($rateAmount_8c_default_rate) ? number_format((float) $rateAmount_8c_default_rate, 2) : '' }}
                </td>
            @else
                <td></td>
                <td></td>
            @endif
            {{-- <td>{{ $assigns_order_8c->total_students ?? '' }}/{{ $assigns_order_8c->total_teachers ??'' }}</td>
             <td class="textend">
                 {{ is_numeric($rateAmount_8c_default_rate) ? number_format((float) $rateAmount_8c_default_rate, 2) : '' }}
             </td>--}}
            <td class="textend">{{ isset($assigns_order_8c->total_amount) ? number_format((float)$assigns_order_8c->total_amount, 2) : '' }}</td>
        </tr>


        {{-- Order = 8.d --}}
        @php
            $assign_8_d = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 1 &&
                       $assign->rateHead &&
                       $assign->rateHead->order_no == '8.d';
            });

            // just the course_code values (unique, trimmed)
            $course_codes = $assign_8_d
                ->pluck('course_code')
                ->filter(fn($c) => filled($c))
                ->map(fn($c) => trim($c))
                ->unique()
                ->values()
                ->all();
            // collect "s/t" per course for THIS teacher
            $fraction_parts = [];

            $total_assigns = $assign_8_d->count();

            $rateHead=\App\Models\RateHead::where('order_no','8.d')->first();
            $head = $rateHead->head;
            $rateAmount_8_d = $rateAmount_order_8d ?? null;
            $default_rate_8_d = $rateAmount_8_d->default_rate ?? 0;

            $total_student_all_course = 0;
            $total_amount_all_course = 0;
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assign_8_d as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                   /* $total_student_all_course += $assign->total_students ?? 0;*/
                    $total_student_all_course += $assign->no_of_items ?? 0;
                    $total_amount_all_course += $assign->total_amount ?? 0;

                      // build "students/teachers" text from the row (no extra queries)
                    $s = (int)   ($assign->total_students  ?? 0);
                    $t = max(1, (int) ($assign->total_teachers ?? 0)); // guard divide-by-zero
                    $fraction_parts[] = "{$s}/{$t}";
                @endphp
            @endforeach
            @php
                $chunks = array_chunk($fraction_parts, 3);
                $pretty_total = rtrim(rtrim(number_format($total_student_all_course, 2, '.', ''), '0'), '.');
            @endphp
            <tr>
                <td class="textstart" colspan="2">{{ $head }}</td>
                <td class="textstart">{{ implode(', ', $course_codes) }}</td>
                {{--<td>{{ $total_student_all_course }}/2</td>--}}
                <td class="textcenter">
                    @foreach ($chunks as $i => $chunk)
                        {{ implode(' + ', $chunk) }}@if ($i < count($chunks) - 1) +<br>@endif
                @endforeach
                <td class="textend">{{ number_format($default_rate_8_d, 2) }}</td>
                <td class="textend">{{ number_format($total_amount_all_course, 2) }}</td>
            </tr>
        @else
            {{-- Show default row if no assign exists --}}
            <tr>
                <td class="textstart" colspan="2">{{ $head }}</td>
                <td></td>
                <td></td>
                {{--<td class="textend">{{ number_format($default_rate_8_d, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif






        {{-- Order = 9 --}}
        @php
            //$assigns_order_9 = $employee->rateAssigns->where('rateHead.order_no', '9');
            $assigns_order_9 = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
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
                    <td class="textend">{{ isset($default_rate) ? number_format($default_rate, 0) : '' }}</td>
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
            $assign_10_a = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 1 &&
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
            $assign->exam_type_id == 1 &&
        @endif





        {{-- Order 10.b --}}
        {{--@php
            //$assign_10_b = $employee->rateAssigns->where('rateHead.order_no', '10.b')->first();
            $assign_10_b = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '10.b';
               })->first();
            $rateAmount_10_b = $rateAmount_order_10_b ?? null;
            $sub_head_10_b = $rateHead_order_10_b->sub_head ?? '6.B';
            $default_rate_10_b = $rateAmount_10_b->default_rate ?? 0;

            if ($assign_10_b && $assign_10_b->total_amount) {
                $global_sum += $assign_10_b->total_amount;
            }
        @endphp
        <tr>
            <td class="textstart">(b) {{ $sub_head_10_b }}</td>
            <td></td>
            <td>{{ $assign_10_b->no_of_items ?? '' }}</td>
            <td class="textend">{{ number_format($default_rate_10_b, 2) }}</td>
            <td class="textend">{{ isset($assign_10_b->total_amount) ? number_format($assign_10_b->total_amount, 2) : '' }}</td>
        </tr>--}}


        {{-- Order = 10.b --}}
        @php
            $assign_10_b = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 1 &&
                       $assign->rateHead &&
                       $assign->rateHead->order_no == '10.b';
            });

            $total_assigns = $assign_10_b->count();

            $rateHead=\App\Models\RateHead::where('order_no','10.b')->first();
            $head = $rateHead->head;
            $sub_head_10_b = $rateHead->sub_head;
            $rateAmount_10_b = $rateAmount_order_10_b ?? null;
            $default_rate_10_b = $rateAmount_10_b->default_rate ?? 0;

            $total_student_all_course = 0;
            $total_amount_all_course = 0;
        @endphp

        @if ($total_assigns > 0)
            @foreach ($assign_10_b as $assign)
                @php
                    $global_sum += $assign->total_amount ?? 0;
                    /*$total_student_all_course += $assign->total_students ?? 0;*/
                    $total_student_all_course += $assign->no_of_items ?? 0;
                    $total_amount_all_course += $assign->total_amount ?? 0;
                @endphp
            @endforeach
            <tr>
                <td class="textstart">(b) {{ $sub_head_10_b }}</td>
                <td>{{ $total_assigns }} courses</td>
                {{--<td>{{ $total_student_all_course }}/2</td>--}}
                <td>{{ $total_student_all_course }}</td>
                <td class="textend">{{ number_format($default_rate_10_b, 2) }}</td>
                <td class="textend">{{ number_format($total_amount_all_course, 2) }}</td>
            </tr>
        @else
            {{-- Show default row if no assign exists --}}
            <tr>
                <td class="textstart">(a) {{ $sub_head_10_b }}</td>
                <td></td>
                <td></td>
                {{-- <td class="textend">{{ number_format($default_rate_10_b, 2) }}</td>--}}
                <td class="textend"></td>
                <td class="textend"></td>
            </tr>
        @endif







        {{--Order 11--}}
        {{-- Order 11 --}}
        @php
            $assigns_order_11 = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                 return $assign->session_id == $session_info->id &&
                        $assign->exam_type_id == 1 &&
                        $assign->rateHead &&
                        $assign->rateHead->order_no == '11';
             });

            $head_order_11   = $rateHead_order_11->head ?? '';
            $default_rate_11 = $rateAmount_order_11->default_rate ?? 0;

            $sum_questions_11 = $assigns_order_11->sum('no_of_items');   // per-teacher share of questions
            $sum_amount_11    = $assigns_order_11->sum('total_amount');  // total money

            if ($sum_amount_11 > 0) {
                $global_sum += $sum_amount_11;
            }
        @endphp
        <tr>
            <td>11</td>
            <td class="textstart" colspan="2">{{ $head_order_11 }}</td>
            <td></td>
            <td>
                {{ $sum_questions_11 > 0 ? number_format($sum_questions_11, 2) : '' }}
            </td>
            <td class="textend">
                @if($sum_questions_11 > 0)
                    {{ number_format($default_rate_11, 0) }}
                @endif
            </td>
            <td class="textend">{{ $sum_amount_11 > 0 ? number_format($sum_amount_11, 2) : '' }}</td>
        </tr>



        {{-- Order 12.a/12.b --}}
        @php
            // Collect ALL 12.a assigns for this teacher (no ->first)
            $assigns_12_a = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                return $assign->session_id == $session_info->id &&
                       $assign->exam_type_id == 1 &&
                       $assign->rateHead &&
                       $assign->rateHead->order_no == '12.a';
            });

            // Totals (sum across groups/rows)
            $sum_stencils_12_a = $assigns_12_a->sum('no_of_items');
            $sum_amount_12_a   = $assigns_12_a->sum('total_amount');

            // RateHead / RateAmount
            $rateAmount_12_a   = $rateAmount_order_12_a ?? null;
            $head_12           = $rateHead_order_12_a->head ?? '';
            $sub_head_12_a     = $rateHead_order_12_a->sub_head ?? '12.a';
            $default_rate_12_a = $rateAmount_12_a->default_rate ?? 0;

            // Add to global total
            if ($sum_amount_12_a > 0) {
                $global_sum += $sum_amount_12_a;
            }
        @endphp
        <tr>
            <td rowspan="2">12</td>
            <td class="textstart" rowspan="2">{{ $head_12 }}</td>

            <td class="textstart">(a) {{ $sub_head_12_a }}</td>
            <td></td>
            <td>{{ $sum_stencils_12_a ? number_format((float)$sum_stencils_12_a, 2) : '' }}</td>

            <td class="textend">
                {{ $assigns_12_a->isNotEmpty() ? number_format((float) $default_rate_12_a, 2) : '' }}
            </td>

            <td class="textend">
                {{ $sum_amount_12_a ? number_format((float) $sum_amount_12_a, 2) : '' }}
            </td>
        </tr>


        {{-- Order 12.b --}}
        @php
            //$assign_12_b = $employee->rateAssigns->where('rateHead.order_no', '12.b')->first();
             $assign_12_b = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '12.b';
               });


              // Totals (if you saved equal-split, no_of_items is the per-teacher share; sum is total for this teacher)
            $sum_stencils_12_b = $assign_12_b->sum('no_of_items');
            $sum_amount_12_b   = $assign_12_b->sum('total_amount');


            // RateHead / RateAmount for 12.b (provided by controller like you did for 7.e)
            $rateAmount_12_b   = $rateAmount_order_12_b ?? null;
            $sub_head_12_b     = $rateHead_order_12_b->sub_head ?? '12.b';
            $default_rate_12_b = $rateAmount_12_b->default_rate ?? 0;


             // Add to global total
            if ($sum_amount_12_b) {
                $global_sum += $sum_amount_12_b;
            }
        @endphp
        <tr>
            <td class="textstart">(b) {{ $sub_head_12_b }}</td>
            <td></td>
            <td>{{ $sum_stencils_12_b ? number_format((float)$sum_stencils_12_b, 2) : '' }}</td>
            <td class="textend">
                {{ $assign_12_b->isNotEmpty() ? number_format((float) $default_rate_12_b, 2) : '' }}
            </td>
            <td class="textend">
                {{ $sum_amount_12_b ? number_format((float) $sum_amount_12_b, 2) : '' }}
            </td>
        </tr>


        {{-- Order 13 --}}
        @php
            //$assign_13 = $employee->rateAssigns->where('rateHead.order_no', '13')->first();
            $assign_13 = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '13';
               })->first();
            $head_order_13 = $rateHead_order_13->head ?? 'Error';
            $rateAmount_13 = $rateAmount_order_13 ?? null;
            $default_rate_13 = $rateAmount_13->default_rate ?? 0;

            if ($assign_13 && $assign_13->total_amount) {
                $global_sum += $assign_13->total_amount;
            }
        @endphp
        <tr>
            <td>13</td>
            <td class="textstart" colspan="2">{{ $head_order_13 }}</td>
            <td></td>
            <td>{{ $assign_13->total_students ?? '' }}</td>
            @if($assign_13)
                <td class="textend">{{ isset($default_rate_13) ? number_format($default_rate_13, 2) : '' }}</td>
            @else
                <td class="textend"></td>
            @endif
            <td class="textend">{{ isset($assign_13->total_amount) ? number_format($assign_13->total_amount, 2) : '' }}</td>
        </tr>


        {{-- Order 14 --}}
        @php
            //$assigns_order_14 = $employee->rateAssigns->where('rateHead.order_no', '14')->first();
             $assigns_order_14 = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '14';
               })->first();
            $head_order_14 = $rateHead_order_14->head ?? 'Error';
            $default_rate_14 = $rateAmount_order_14->default_rate ?? 0;
            if ($assigns_order_14 && $assigns_order_14->total_amount) {
                $global_sum += $assigns_order_14->total_amount;
            }
        @endphp
        <tr>
            <td>14</td>
            <td class="textstart" colspan="2">{{ $head_order_14 }}</td>
            <td></td>
            <td></td>
            {{--<td class="textend">{{ number_format($default_rate_14, 2) }}</td>--}}
            <td class="textend"></td>
            <td class="textend">{{ isset($assigns_order_14->total_amount) ? number_format($assigns_order_14->total_amount, 2) : '' }}</td>
        </tr>

        {{-- Order 15 --}}
        @php
            //$assigns_order_15 = $employee->rateAssigns->where('rateHead.order_no', '15')->first();
            $assigns_order_15 = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
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
            //$assign_16 = $employee->rateAssigns->where('rateHead.order_no', '16')->first();
            $assign_16 = $employee->rateAssigns->filter(function($assign) use ($session_info) {
                   return $assign->session_id == $session_info->id &&
                          $assign->exam_type_id == 1 &&
                          $assign->rateHead &&
                          $assign->rateHead->order_no == '16';
               })->first();
            $head_order_16 = $rateHead_order_16->head ?? 'Error';
            $rateAmount_16 = $rateAmount_order_16 ?? null;
            $default_rate_16 = $rateAmount_16->default_rate ?? 0;

            if ($assign_16 && $assign_16->total_amount) {
                $global_sum += $assign_16->total_amount;
            }
        @endphp
        <tr>
            <td>16</td>
            <td class="textstart" colspan="2">{{ $head_order_16 }}</td>
            <td></td>
            <td>{{ $assign_16->total_students ?? '' }}</td>
            <td class="textend">
                @if(isset($default_rate_16) && $assign_16)
                    {{ number_format($default_rate_16, 2) }}
                @endif
            </td>
            <td class="textend">{{ isset($assign_16->total_amount) ? number_format($assign_16->total_amount, 2) : '' }}</td>
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
            <td style="width: 30%;" class="pt-20">
                ----------------------------------------------------------------------
            </td>
            <td style="width: 30%;" class="pt-20" style="text-align: right">-----------approved</td>
        </tr>
        <tr>
            <td class="pt-40">Signature of Examiner</td>
            <td class="pt-40">Prepared by</td>
            <td class="pt-40">Assistant Comptroller</td>
            <td class="pt-40">Comptroller (In Charge)</td>
        </tr>
    </table>

    {{-- Revenue Stamp note --}}
    <table class="revenue_table">
        <tr>
            <td style="width: 20%;">
                <div class="revenue-stamp-box">
                    <span>Revenue<br>Stamp</span>
                </div>
            </td>
            <td style="width: 60%; text-align: center; vertical-align: bottom; padding-bottom: 0;">
                N.B. For bill 400 Tk or above use Revenue Stamp of 10 Tk
            </td>
            <td style="width: 20%; text-align: right; vertical-align: bottom; padding-bottom: 0;">
                # MRMR
            </td>
        </tr>
    </table>

    @if (!$loop->last)
        <div class="page-break"></div>
    @endif

@endforeach
@php } @endphp





</body>
</html>
