<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Bill Regular</title>
    <style>
        @media print {
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            tr, th, td { page-break-inside: avoid; }
            h3 { page-break-after: avoid; break-after: avoid; }
        }

        /* Page + base */
        @page {
            margin: 5mm 12mm 5mm 12mm;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 12px;
        }

        /* Shared table base */
        .header_table, .body_table_1, .body_table_2 , .body_table_4,.body_table_5,.body_table_9,.body_table_8_a,.body_table_8_a,.body_table_8_b,.body_table_10_a,.body_table_10_b,.body_table_8_d{
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* keeps widths stable in PDF */
        }

        /* Header */
        .header_table td {
            text-align: center;
            font-size: 13px;
        }

        /* Body table 1 */
        .body_table_1 {
            font-size: 13px;
        }

        .body_table_1 th,
        .body_table_1 td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        /* Body table 2 */
        .body_table_2 {
            font-size: 13px;
        }

        .body_table_2 th,
        .body_table_2 td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .body_table_4 th,
        .body_table_4 td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .body_table_5 th,
        .body_table_5 td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .body_table_9 th,
        .body_table_9 td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .body_table_8_a th,
        .body_table_8_a td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .body_table_8_b th,
        .body_table_8_b td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .body_table_10_a th,
        .body_table_10_a td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .body_table_10_b th,
        .body_table_10_b td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .body_table_8_d th,
        .body_table_8_d td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        /* Utilities */
        .page-break {
            page-break-after: always;
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
    </style>
</head>
<body>

@php
    // Ordinal helper (1=>1st, 2=>2nd, 3=>3rd, else => th)
    $ordinals = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th', 5 => '5th', 6 => '6th', 7 => '7th', 8 => '8th'];
    $yearText = $ordinals[$session_info->year ?? null] ?? (($session_info->year ?? '') . 'th');
    $semesterText = $ordinals[$session_info->semester ?? null] ?? (($session_info->semester ?? '') . 'th');
@endphp

@hasanyrole('Teacher|Admin|SuperAdmin')

{{-- Repeatable Header --}}
<table class="header_table" style="table-layout: fixed;">
    <colgroup>
        <col style="width: 15%;">
        <col style="width: 35%;">
        <col style="width: 20%;">
        <col style="width: 30%;">
    </colgroup>

    <tr>
        <td colspan="1" style="text-align: right; padding: 20px 0 0 0;">
            <img src="{{ public_path('images/logo_duet.png') }}" style="width: 50px;">
        </td>
        <td colspan="3" style="text-align: left; padding: 20px 0 0 35px;">
            <strong>Dhaka University of Engineering &amp; Technology, Gazipur</strong><br>
            <span style="display:inline-block; margin-left:100px; margin-top:5px;">Gazipur-1707</span>
        </td>
    </tr>

    <tr>
        <td colspan="4" style="padding: 10px 0;">
            <div style="margin-left:5px; font-weight:bold;">(Department of Architecture)</div>
        </td>
    </tr>

    <tr>
        <td style="text-align:right; padding-right:10px;">Bachelor in Architecture</td>
        <td>
            <span>{{ $yearText }} year {{ $semesterText }} semester</span>
        </td>
        <td style="text-align:left; padding-left:40px;">
                <span style="font-weight:bold">
                    {{ isset($exam_type) && (int)$exam_type === 2 ? 'Review' : 'Regular' }}
                </span>
        </td>
        <td style="text-align:right;">Examination: {{ $session_info->session ?? '' }}</td>
    </tr>
</table>

{{-- A) Moderation Committee --}}
@if($assigns_order_1->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        A) List of Examination Committee/Moderation Committee Members (@ min 1500/- per member)
    </h3>

    <table class="body_table_1" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:65%;">Name and Address</th>
            <th style="width:25%;">Position</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assigns_order_1 as $i => $assign)
            @php
                $person  = $assign->teacher ?? $assign->employee;
                $email   = data_get($person, 'user.email');
                $isChair = $email === ($headEmail ?? null);
            @endphp
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align: left;">
                    {{ $person?->user?->name }},
                    {{ $person?->designation?->designation }},
                    {{ $person?->department?->fullname }},
                    DUET, Gazipur
                </td>
                <td style="text-align:center;">{{ $isChair ? 'Chairman' : 'Member' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

{{-- B) Examiners & Paper Setters --}}
@if(!empty($assigns_order_2) && count($assigns_order_2) > 0)
    <h3 style="margin-top:15px;margin-bottom: 4px">
        B) List of Examiners (@200/- per script , min 1000/- per examiner) & Paper Settters (@3600/- per paper setter)
    </h3>

    <table class="body_table_2" border="1" cellpadding="6">
        <thead>
        <tr>
            <th rowspan="2" style="width:5%;">Sl. No.</th>
            <th rowspan="2" style="width:15%;">Course</th>
            <th colspan="2" style="width:70%;">Name &amp; Address</th>
            <th rowspan="2" style="width:10%;">No. of Scripts</th>
        </tr>
        <tr>
            <th style="width:55%;">Paper Setter</th>
            <th style="width:15%;">Examiner</th>
        </tr>
        </thead>
        <tbody>
        @php $sl = 1; @endphp
        @foreach($assigns_order_2 as $courseCode => $rows)
            @php
                $rows        = collect($rows);
                $rowspan     = $rows->count();
                $first       = $rows->first();
                $firstPerson = $first->teacher ?? $first->employee;

                $course_code = $first->course_code ?? $courseCode;
                $course_name = $first->course_name ?? '';
                $scriptsText = (int)($first->total_students ?? 0) . '/' . (int)($first->total_teachers ?? $rowspan);
            @endphp

            <tr>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $sl }}</td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">
                    {{ $course_code }}
                </td>
                <td style="text-align:left;">
                    {{ optional(optional($firstPerson)->user)->name }},
                    {{ optional(optional($firstPerson)->designation)->designation }},
                    {{ optional(optional($firstPerson)->department)->fullname }},
                    DUET, Gazipur
                </td>
                <td style="text-align:center;">Same as P.S.</td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $scriptsText }}</td>
            </tr>

            @foreach($rows->skip(1) as $row)
                @php $person = $row->teacher ?? $row->employee; @endphp
                <tr>
                    <td style="text-align:left;">
                        {{ optional(optional($person)->user)->name }},
                        {{ optional(optional($person)->designation)->designation }},
                        {{ optional(optional($person)->department)->fullname }},
                        DUET, Gazipur
                    </td>
                    <td style="text-align:center;">-Do-</td>
                </tr>
            @endforeach
            @php $sl++; @endphp
        @endforeach
        </tbody>
    </table>
@endif


{{-- C) Class Test (order 4) --}}
@if($assigns_order_4->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        C) Internal Assessment/Class Test (@ 45/- per class test per student)
    </h3>

    <table class="body_table_4" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:20%;">Course</th>
            <th style="width:60%;">Name &amp; Address</th>
            <th style="width:10%;">Nos. of Student</th>
        </tr>
        </thead>
        <tbody>
        @php $sl = 1; @endphp

        @foreach($assigns_order_4 as $courseCode => $rows)
            @php
                $rows         = collect($rows);
                $rowspan      = $rows->count();
                $first        = $rows->first();
                $firstPerson  = $first->teacher ?? $first->employee;

                $course_code  = $first->course_code ?? $courseCode;
                $course_name  = $first->course_name ?? '';
                // Nos. of Student = total_students * 2 (2 is fixed)
                $studentCount = (int)($first->total_students ?? 0);
            @endphp

            {{-- first line for this course (carries the rowspans) --}}
            <tr>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $sl }}</td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">
                    {{ $course_code }}
                </td>
                <td style="text-align:left;">
                    {{ optional(optional($firstPerson)->user)->name }},
                    {{ optional(optional($firstPerson)->designation)->designation }},
                    {{ optional(optional($firstPerson)->department)->fullname }},
                    DUET, Gazipur
                </td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $studentCount }}×2</td>
            </tr>

            {{-- remaining teachers for this same course --}}
            @foreach($rows->skip(1) as $row)
                @php $person = $row->teacher ?? $row->employee; @endphp
                <tr>
                    <td style="text-align:left;">
                        {{ optional(optional($person)->user)->name }},
                        {{ optional(optional($person)->designation)->designation }},
                        {{ optional(optional($person)->department)->fullname }},
                        DUET, Gazipur
                    </td>
                </tr>
            @endforeach

            @php $sl++; @endphp
        @endforeach
        </tbody>
    </table>
@endif


{{-- D) Class Test (order 5) --}}
@if($assigns_order_5->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        D) Sessional (@ 115/- per contact hour per week; min 1500/- per examiner)
    </h3>

    <table class="body_table_5" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:20%;">Course</th>
            <th style="width:60%;">Name &amp; Address</th>
            <th style="width:10%;">Contact Hr./Week</th>
        </tr>
        </thead>
        <tbody>
        @php $sl = 1; @endphp

        @foreach($assigns_order_5 as $courseCode => $rows)
            @php
                $rows         = collect($rows);
                $rowspan      = $rows->count();
                $first        = $rows->first();
                $firstPerson  = $first->teacher ?? $first->employee;

                $course_code  = $first->course_code ?? $courseCode;
                $course_name  = $first->course_name ?? '';
                // Nos. of Student = total_students * 2 (2 is fixed)
                $studentCount = (int)($first->no_of_items ?? 0);
            @endphp

            {{-- first line for this course (carries the rowspans) --}}
            <tr>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $sl }}</td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">
                    {{ $course_code }}
                </td>
                <td style="text-align:left;">
                    {{ optional(optional($firstPerson)->user)->name }},
                    {{ optional(optional($firstPerson)->designation)->designation }},
                    {{ optional(optional($firstPerson)->department)->fullname }},
                    DUET, Gazipur
                </td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $studentCount }}</td>
            </tr>

            {{-- remaining teachers for this same course --}}
            @foreach($rows->skip(1) as $row)
                @php $person = $row->teacher ?? $row->employee; @endphp
                <tr>
                    <td style="text-align:left;">
                        {{ optional(optional($person)->user)->name }},
                        {{ optional(optional($person)->designation)->designation }},
                        {{ optional(optional($person)->department)->fullname }},
                        DUET, Gazipur
                    </td>
                </tr>
            @endforeach

            @php $sl++; @endphp
        @endforeach
        </tbody>
    </table>
@endif


{{-- E) Class Test (order 9) --}}
@if($assigns_order_9->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        E) List of Scrutinizers (@ 24/- per script,min 1000/- per scrutinizers)
    </h3>

    <table class="body_table_9" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:10%;">Course</th>
            <th style="width:60%;">Name &amp; Address</th>
            <th style="width:10%;">No. of half Sripts</th>
            <th style="width:10%;">Total</th>
        </tr>
        </thead>
        <tbody>
        @php $sl = 1; @endphp

        @foreach($assigns_order_9 as $courseCode => $rows)
            @php
                $rows         = collect($rows);
                $rowspan      = $rows->count();
                $first        = $rows->first();
                $firstPerson  = $first->teacher ?? $first->employee;

                $course_code  = $first->course_code ?? $courseCode;
                $course_name  = $first->course_name ?? '';
                // Nos. of Student = total_students * 2 (2 is fixed)
                $studentCount = (int)($first->total_students ?? 0);

                 $scriptsText = (int)($first->total_students ?? 0) . '/' . (int)($first->total_teachers ?? $rowspan);
            @endphp

            {{-- first line for this course (carries the rowspans) --}}
            <tr>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $sl }}</td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">
                    {{ $course_code }}
                </td>
                <td style="text-align:left;">
                    {{ optional(optional($firstPerson)->user)->name }},
                    {{ optional(optional($firstPerson)->designation)->designation }},
                    {{ optional(optional($firstPerson)->department)->fullname }},
                    DUET, Gazipur
                </td>
                <td  style="text-align:center;">{{ $scriptsText }}</td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $studentCount }}</td>
            </tr>

            {{-- remaining teachers for this same course --}}
            @foreach($rows->skip(1) as $row)
                @php $person = $row->teacher ?? $row->employee; @endphp
                <tr>
                    <td style="text-align:left;">
                        {{ optional(optional($person)->user)->name }},
                        {{ optional(optional($person)->designation)->designation }},
                        {{ optional(optional($person)->department)->fullname }},
                        DUET, Gazipur
                    </td>
                    <td  style="text-align:center;">{{ $scriptsText }}</td>

                </tr>
            @endforeach

            @php $sl++; @endphp
        @endforeach
        </tbody>
    </table>
@endif

{{-- F) Class Test (order 8.a) --}}
@if($assigns_order_8_a->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        F)  List of Teachers for the Preparation of Grade Sheet(Theoritical (@45/- per student per subject))
    </h3>

    <table class="body_table_8_a" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:10%;">Course</th>
            <th style="width:60%;">Name &amp; Address</th>
            <th style="width:10%;">No. of Students</th>
            <th style="width:10%;">Total</th>
        </tr>
        </thead>
        <tbody>
        @php $sl = 1; @endphp

        @foreach($assigns_order_8_a as $courseCode => $rows)
            @php
                $rows         = collect($rows);
                $rowspan      = $rows->count();
                $first        = $rows->first();
                $firstPerson  = $first->teacher ?? $first->employee;

                $course_code  = $first->course_code ?? $courseCode;
                $course_name  = $first->course_name ?? '';
                // Nos. of Student = total_students * 2 (2 is fixed)
                $studentCount = (int)($first->total_students ?? 0);

                 $scriptsText = (int)($first->total_students ?? 0) . '/' . (int)($first->total_teachers ?? $rowspan);
            @endphp

            {{-- first line for this course (carries the rowspans) --}}
            <tr>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $sl }}</td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">
                    {{ $course_code }}
                </td>
                <td style="text-align:left;">
                    {{ optional(optional($firstPerson)->user)->name }},
                    {{ optional(optional($firstPerson)->designation)->designation }},
                    {{ optional(optional($firstPerson)->department)->fullname }},
                    DUET, Gazipur
                </td>
                <td  style="text-align:center;">{{ $scriptsText }}</td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $studentCount }}</td>
            </tr>

            {{-- remaining teachers for this same course --}}
            @foreach($rows->skip(1) as $row)
                @php $person = $row->teacher ?? $row->employee; @endphp
                <tr>
                    <td style="text-align:left;">
                        {{ optional(optional($person)->user)->name }},
                        {{ optional(optional($person)->designation)->designation }},
                        {{ optional(optional($person)->department)->fullname }},
                        DUET, Gazipur
                    </td>
                    <td  style="text-align:center;">{{ $scriptsText }}</td>

                </tr>
            @endforeach

            @php $sl++; @endphp
        @endforeach
        </tbody>
    </table>
@endif

{{-- G) Class Test (order 8.b) --}}
@if($assigns_order_8_b->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        G) List of Teachers for the Preparation of Grade Sheet(Sessional) (@35/- per student per subject):
    </h3>

    <table class="body_table_8_b" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:10%;">Course</th>
            <th style="width:60%;">Name &amp; Address</th>
            <th style="width:10%;">No. of Students</th>
            <th style="width:10%;">Total</th>
        </tr>
        </thead>
        <tbody>
        @php $sl = 1; @endphp

        @foreach($assigns_order_8_b as $courseCode => $rows)
            @php
                $rows         = collect($rows);
                $rowspan      = $rows->count();
                $first        = $rows->first();
                $firstPerson  = $first->teacher ?? $first->employee;

                $course_code  = $first->course_code ?? $courseCode;
                $course_name  = $first->course_name ?? '';
                // Nos. of Student = total_students * 2 (2 is fixed)
                $studentCount = (int)($first->total_students ?? 0);

                 $scriptsText = (int)($first->total_students ?? 0) . '/' . (int)($first->total_teachers ?? $rowspan);
            @endphp

            {{-- first line for this course (carries the rowspans) --}}
            <tr>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $sl }}</td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">
                    {{ $course_code }}
                </td>
                <td style="text-align:left;">
                    {{ optional(optional($firstPerson)->user)->name }},
                    {{ optional(optional($firstPerson)->designation)->designation }},
                    {{ optional(optional($firstPerson)->department)->fullname }},
                    DUET, Gazipur
                </td>
                <td  style="text-align:center;">{{ $scriptsText }}</td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $studentCount }}</td>
            </tr>

            {{-- remaining teachers for this same course --}}
            @foreach($rows->skip(1) as $row)
                @php $person = $row->teacher ?? $row->employee; @endphp
                <tr>
                    <td style="text-align:left;">
                        {{ optional(optional($person)->user)->name }},
                        {{ optional(optional($person)->designation)->designation }},
                        {{ optional(optional($person)->department)->fullname }},
                        DUET, Gazipur
                    </td>
                    <td  style="text-align:center;">{{ $scriptsText }}</td>

                </tr>
            @endforeach

            @php $sl++; @endphp
        @endforeach
        </tbody>
    </table>
@endif

{{-- H)  (order 10.a) --}}
@if($assigns_order_10_a->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        H) List of Teachers for the Scrutinizing of Grade Sheet (Theoretical) (@10/- per student per subject)
    </h3>

    <table class="body_table_10_a" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:10%;">Course</th>
            <th style="width:60%;">Name &amp; Address</th>
            <th style="width:10%;">No. of half Students</th>
            <th style="width:10%;">Total</th>
        </tr>
        </thead>
        <tbody>
        @php $sl = 1; @endphp

        @foreach($assigns_order_10_a as $courseCode => $rows)
            @php
                $rows         = collect($rows);
                $rowspan      = $rows->count();
                $first        = $rows->first();
                $firstPerson  = $first->teacher ?? $first->employee;

                $course_code  = $first->course_code ?? $courseCode;
                $course_name  = $first->course_name ?? '';
                // Nos. of Student = total_students * 2 (2 is fixed)
                $studentCount = (int)($first->total_students ?? 0);

                 $scriptsText = (int)($first->total_students ?? 0) . '/' . (int)($first->total_teachers ?? $rowspan);
            @endphp

            {{-- first line for this course (carries the rowspans) --}}
            <tr>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $sl }}</td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">
                    {{ $course_code }}
                </td>
                <td style="text-align:left;">
                    {{ optional(optional($firstPerson)->user)->name }},
                    {{ optional(optional($firstPerson)->designation)->designation }},
                    {{ optional(optional($firstPerson)->department)->fullname }},
                    DUET, Gazipur
                </td>
                <td  style="text-align:center;">{{ $scriptsText }}</td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $studentCount }}</td>
            </tr>

            {{-- remaining teachers for this same course --}}
            @foreach($rows->skip(1) as $row)
                @php $person = $row->teacher ?? $row->employee; @endphp
                <tr>
                    <td style="text-align:left;">
                        {{ optional(optional($person)->user)->name }},
                        {{ optional(optional($person)->designation)->designation }},
                        {{ optional(optional($person)->department)->fullname }},
                        DUET, Gazipur
                    </td>
                    <td  style="text-align:center;">{{ $scriptsText }}</td>

                </tr>
            @endforeach

            @php $sl++; @endphp
        @endforeach
        </tbody>
    </table>
@endif

{{-- I) (order 10.b) --}}
@if($assigns_order_10_b->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        I) List of Teachers for the Scrutinizing of Grade Sheet(Sessional) (@ 10/- per student per subject):
    </h3>

    <table class="body_table_10_b" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:10%;">Course</th>
            <th style="width:60%;">Name &amp; Address</th>
            <th style="width:10%;">No. of Students</th>
            <th style="width:10%;">Total</th>
        </tr>
        </thead>
        <tbody>
        @php $sl = 1; @endphp

        @foreach($assigns_order_10_b as $courseCode => $rows)
            @php
                $rows         = collect($rows);
                $rowspan      = $rows->count();
                $first        = $rows->first();
                $firstPerson  = $first->teacher ?? $first->employee;

                $course_code  = $first->course_code ?? $courseCode;
                $course_name  = $first->course_name ?? '';
                // Nos. of Student = total_students * 2 (2 is fixed)
                $studentCount = (int)($first->total_students ?? 0);

                 $scriptsText = (int)($first->total_students ?? 0) . '/' . (int)($first->total_teachers ?? $rowspan);
            @endphp

            {{-- first line for this course (carries the rowspans) --}}
            <tr>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $sl }}</td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">
                    {{ $course_code }}
                </td>
                <td style="text-align:left;">
                    {{ optional(optional($firstPerson)->user)->name }},
                    {{ optional(optional($firstPerson)->designation)->designation }},
                    {{ optional(optional($firstPerson)->department)->fullname }},
                    DUET, Gazipur
                </td>
                <td  style="text-align:center;">{{ $scriptsText }}</td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $studentCount }}</td>
            </tr>

            {{-- remaining teachers for this same course --}}
            @foreach($rows->skip(1) as $row)
                @php $person = $row->teacher ?? $row->employee; @endphp
                <tr>
                    <td style="text-align:left;">
                        {{ optional(optional($person)->user)->name }},
                        {{ optional(optional($person)->designation)->designation }},
                        {{ optional(optional($person)->department)->fullname }},
                        DUET, Gazipur
                    </td>
                    <td  style="text-align:center;">{{ $scriptsText }}</td>

                </tr>
            @endforeach

            @php $sl++; @endphp
        @endforeach
        </tbody>
    </table>
@endif


{{-- J) (order 8.d) --}}
@if($assigns_order_8_d->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        J) List of Teachers Prepared Computerized Result (@ 10/- per student per subject)
    </h3>

    <table class="body_table_8_d" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:40%;">Name &amp; Address</th>
            <th style="width:20%;">Course</th>
            <th style="width:15%;">No. of Students</th>
            <th style="width:15%;">Total</th>
        </tr>
        </thead>
        <tbody>
        @php $sl = 1; @endphp

        @foreach($assigns_order_8_d as $teacherId => $rows)
            @php
                $rows         = collect($rows);
                $rowspan      = $rows->count();
                $first        = $rows->first();
                $firstPerson  = $first->teacher ?? $first->employee;

                // total students (sum of no_of_items for this teacher)
                $totalStudents = (int) $rows->sum(function($r){ return (float) $r->no_of_items; });
            @endphp

            {{-- First row (teacher info + first course) --}}
            <tr>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $sl }}</td>
                <td rowspan="{{ $rowspan }}" style="text-align:left;">
                    {{ optional(optional($firstPerson)->user)->name }},
                    {{ optional(optional($firstPerson)->designation)->designation }},
                    {{ optional(optional($firstPerson)->department)->fullname }},
                    DUET, Gazipur
                </td>
                <td style="text-align:center;">
                    {{ $first->course_code }}<br>
                </td>
                <td style="text-align:center;">
                    {{ (int)($first->total_students ?? 0) }}/{{ (int)($first->total_teachers ?? $rowspan) }}
                </td>
                <td rowspan="{{ $rowspan }}" style="text-align:center;">{{ $totalStudents }}</td>
            </tr>

            {{-- Remaining courses for same teacher --}}
            @foreach($rows->skip(1) as $row)
                <tr>
                    <td style="text-align:center;">
                        {{ $row->course_code }}<br>
                    </td>
                    <td  style="text-align:center;">
                        {{ (int)($row->total_students ?? 0) }}/{{ (int)($row->total_teachers ?? $rowspan) }}
                    </td>
                </tr>
            @endforeach

            @php $sl++; @endphp
        @endforeach
        </tbody>
    </table>

@endif


{{-- K) (order 8.c) --}}
@if($assigns_order_8_c->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        K) List of Teachers Verified Computerized Grade Sheets & GPA List (@24/- per student)
    </h3>

    <table class="body_table_1" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:65%;">Name and Address</th>
            <th style="width:25%;">No. of Students</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assigns_order_8_c as $i => $assign)
            @php
                $person     = $assign->teacher ?? $assign->employee;
                $students   = (int)($assign->total_students ?? 0);
                $teachers   = (int)($assign->total_teachers ?? 1); // fallback
            @endphp
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:left;">
                    {{ $person?->user?->name }},
                    {{ $person?->designation?->designation }},
                    {{ $person?->department?->fullname }},
                    DUET, Gazipur
                </td>
                <td style="text-align:center;">
                    {{ $students }}/{{ $teachers }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif


{{-- L) (order 12.a) --}}
@if($assigns_order_12_a->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        L) Work Done Under the Supervision of the Chairman (Exam Committee)
    </h3>


    <h3 style="margin-top:15px;margin-bottom: 4px">
        i) List of Stencill Cutting of Question paper (@ 115/- per stencil)
    </h3>

    <table class="body_table_1" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:65%;">Name and Address</th>
            <th style="width:25%;">No. of Stencils</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assigns_order_12_a as $i => $assign)
            @php
                $t = $assign->employee;
            @endphp
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:left;">
                    {{ $t->user->name ?? 'N/A' }},
                    {{ $t->designation->designation ?? '' }},
                    {{ $t->department->fullname ?? '' }}, DUET, Gazipur
                </td>
                @php
                    $students = (float) ($assign->total_students ?? 0);  // no (int) here
                @endphp
                <td style="text-align:center;">
                    {{ fmod($students, 1) == 0 ? (int)$students : number_format($students, 2, '.', '') }}
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
@endif


{{-- L) (order 12.b) --}}
@if($assigns_order_12_b->isNotEmpty())

    <h3 style="margin-top:15px;margin-bottom: 4px">
        ii) List of Printing of Question paper (@ 35/- per stencil)
    </h3>

    <table class="body_table_1" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:65%;">Name and Address</th>
            <th style="width:25%;">No. of Stencils</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assigns_order_12_b as $i => $assign)
            @php
                $t = $assign->teacher;
            @endphp
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:left;">
                    {{ $t->user->name ?? 'N/A' }},
                    {{ $t->designation->designation ?? '' }},
                    {{ $t->department->fullname ?? '' }}, DUET, Gazipur
                </td>
                @php
                    $students = (float) ($assign->total_students ?? 0);  // no (int) here
                @endphp
                <td style="text-align:center;">
                    {{ fmod($students, 1) == 0 ? (int)$students : number_format($students, 2, '.', '') }}
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
@endif

{{-- L) (order 11) --}}
@if($assigns_order_11->isNotEmpty())

    <h3 style="margin-top:15px;margin-bottom: 4px">
        iii) List of Comparison, Correction, Sketching & Distribution of Question Paper (@ 1350/- per stencil)
    </h3>

    <table class="body_table_1" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:65%;">Name and Address</th>
            <th style="width:25%;">No. of Questions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assigns_order_11 as $i => $assign)
            @php
                $t = $assign->teacher;
            @endphp
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:left;">
                    {{ $t->user->name ?? 'N/A' }},
                    {{ $t->designation->designation ?? '' }},
                    {{ $t->department->fullname ?? '' }}, DUET, Gazipur
                </td>
                @php
                    $students = (float) ($assign->total_students ?? 0);  // no (int) here
                @endphp
                <td style="text-align:center;">
                    {{ fmod($students, 1) == 0 ? (int)$students : number_format($students, 2, '.', '') }}
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
@endif



{{-- M) (order 13) --}}
@if($assigns_order_13->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        M) Advisory (@ 225/- per student per semester):
    </h3>

    <table class="body_table_1" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:65%;">Name and Address</th>
            <th style="width:25%;">No. of Students</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assigns_order_13 as $i => $assign)
            @php
                $t = $assign->teacher;
            @endphp
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:left;">
                    {{ $t->user->name ?? 'N/A' }},
                    {{ $t->designation->designation ?? '' }},
                    {{ $t->department->fullname ?? '' }}, DUET, Gazipur
                </td>
                @php
                    $total_student = (int) $assign->total_students;
                @endphp
                <td style="text-align:center;">
                   {{$total_student}}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif


{{-- N) (order 16) --}}
@if($assigns_order_16->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        N)  List of Teachers verified the final graduation results (@ 700/- per student)):
    </h3>

    <table class="body_table_1" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:65%;">Name and Address</th>
            <th style="width:25%;">No. of Students</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assigns_order_16 as $i => $assign)
            @php
                $person     = $assign->teacher ?? $assign->employee;
                $students   = (int)($assign->total_students ?? 0);
            @endphp
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:left;">
                    {{ $person?->user?->name }},
                    {{ $person?->designation?->designation }},
                    {{ $person?->department?->fullname }},
                    DUET, Gazipur
                </td>
                <td style="text-align:center;">
                    {{ $students }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif


{{-- O) (order 7.e) --}}
@if($assigns_order_7_e->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        O)  List of Teachers conducted central oral examination/Jury of thesis/projects (@150/- thesis/projects)
    </h3>

    <table class="body_table_1" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:65%;">Name and Address</th>
            <th style="width:25%;">No. of Students</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assigns_order_7_e as $i => $assign)
            @php
                $t = $assign->teacher;
            @endphp
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:left;">
                    {{ $t->user->name ?? 'N/A' }},
                    {{ $t->designation->designation ?? '' }},
                    {{ $t->department->fullname ?? '' }}, DUET, Gazipur
                </td>
                @php
                    $students = (float) ($assign->total_students ?? 0);  // no (int) here
                @endphp
                <td style="text-align:center;">
                    {{ fmod($students, 1) == 0 ? (int)$students : number_format($students, 2, '.', '') }}
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
@endif


{{-- P) (order 7.f) --}}
@if($assigns_order_7_f->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        P)   List of teachers involved survey (@ 900/- per student)
    </h3>

    <table class="body_table_1" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:65%;">Name and Address</th>
            <th style="width:25%;">No. of Students</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assigns_order_7_f as $i => $assign)
            @php
                $t = $assign->teacher;
            @endphp
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:left;">
                    {{ $t->user->name ?? 'N/A' }},
                    {{ $t->designation->designation ?? '' }},
                    {{ $t->department->fullname ?? '' }}, DUET, Gazipur
                </td>
                @php
                    $students = (float) ($assign->total_students ?? 0);  // no (int) here
                @endphp
                <td style="text-align:center;">
                    {{ fmod($students, 1) == 0 ? (int)$students : number_format($students, 2, '.', '') }}
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
@endif


{{-- Q) (order 6.c) --}}
@if($assigns_order_6_c->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        Q)   List of Teachers conducted preliminary viva of thesis/projects (@ 100/- per student)
    </h3>

    <table class="body_table_1" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:65%;">Name and Address</th>
            <th style="width:25%;">No. of Students</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assigns_order_6_c as $i => $assign)
            @php
                $t = $assign->teacher;
            @endphp
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:left;">
                    {{ $t->user->name ?? 'N/A' }},
                    {{ $t->designation->designation ?? '' }},
                    {{ $t->department->fullname ?? '' }}, DUET, Gazipur
                </td>
                @php
                    $students = (float) ($assign->total_students ?? 0);  // no (int) here
                @endphp
                <td style="text-align:center;">
                    {{ fmod($students, 1) == 0 ? (int)$students : number_format($students, 2, '.', '') }}
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
@endif


{{-- R) (order 6.a) --}}
@if($assigns_order_6_a->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        R)   List of Teachers examined thesis/projects (@ 2700/- thesis/projects)
    </h3>

    <table class="body_table_1" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
            <tr>
                <th rowspan="2" style="width:10%;">Sl. No.</th>
                <th rowspan="2" style="width:65%;">Name and Address</th>
                <th colspan="2" style="width:25%;">No. of Students</th>
            </tr>
            <tr>
                <th>Internal</th>
                <th>External</th>
            </tr>
        </thead>
        <tbody>
        @php
            // int if whole; 2 decimals otherwise; empty if zero
            $fmt = function ($num) {
                $v = (float) $num;
                if (abs($v) < 1e-9) return ''; // hide 0
                return fmod($v, 1) == 0 ? (string)(int)$v : number_format($v, 2, '.', '');
            };
        @endphp

        @foreach($assigns_order_6_a as $row)
            @php
                $t = $row->teacher;
                $name = $t->user->name ?? 'N/A';
                $des  = $t->designation->designation ?? '';
                $dept = $t->department->fullname ?? '';
                $internal = (float) $row->internal_students;
                $external = (float) $row->external_students;
            @endphp
            <tr>
                <td style="text-align:center;">{{ $loop->iteration }}</td>
                <td style="text-align:left;">
                    {{ $name }}@if($des), {{ $des }}@endif
                    @if($dept), {{ $dept }}@endif, DUET, Gazipur
                </td>
                <td style="text-align:center;">{{ $fmt($internal) }}</td>
                <td style="text-align:center;">{{ $fmt($external) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif


{{-- S) (order 6.d) --}}
@if($assigns_order_6_d->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        S)    List of Teachers conducted oral examination/Jury of thesis/projects (@225/- thesis/projects)
    </h3>

    <table class="body_table_1" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:65%;">Name and Address</th>
            <th style="width:25%;">No. of Students</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assigns_order_6_d as $i => $assign)
            @php
                $t = $assign->teacher;
            @endphp
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:left;">
                    {{ $t->user->name ?? 'N/A' }},
                    {{ $t->designation->designation ?? '' }},
                    {{ $t->department->fullname ?? '' }}, DUET, Gazipur
                </td>
                @php
                    $students = (float) ($assign->total_students ?? 0);  // no (int) here
                @endphp
                <td style="text-align:center;">
                    {{ fmod($students, 1) == 0 ? (int)$students : number_format($students, 2, '.', '') }}
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
@endif


{{-- T) (order 6.b) --}}
@if($assigns_order_6_b->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        T)   List of Teachers supervised the thesis/projects (@ 5500/- thesis/projects)
    </h3>

    <table class="body_table_1" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:65%;">Name and Address</th>
            <th style="width:25%;">No. of Students</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assigns_order_6_b as $i => $assign)
            @php
                $t = $assign->teacher;
            @endphp
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:left;">
                    {{ $t->user->name ?? 'N/A' }},
                    {{ $t->designation->designation ?? '' }},
                    {{ $t->department->fullname ?? '' }}, DUET, Gazipur
                </td>
                @php
                    $students = (float) ($assign->total_students ?? 0);  // no (int) here
                @endphp
                <td style="text-align:center;">
                    {{ fmod($students, 1) == 0 ? (int)$students : number_format($students, 2, '.', '') }}
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
@endif


{{-- U) (order 14) --}}
@if($assigns_order_14->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        U)   Honorarium for course co-ordinator (UG) (@3600/-)
    </h3>

    <table class="body_table_1" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:65%;">Name and Address</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assigns_order_14 as $i => $assign)
            @php
                $t = $assign->teacher;
            @endphp
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:left;">
                    {{ $t->user->name ?? 'N/A' }},
                    {{ $t->designation->designation ?? '' }},
                    {{ $t->department->fullname ?? '' }}, DUET, Gazipur
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
@endif


{{-- V) (order 15) --}}
@if($assigns_order_15->isNotEmpty())
    <h3 style="margin-top:15px;margin-bottom: 4px">
        V)    Honorarium for Chairman (@4500/-)
    </h3>

    <table class="body_table_1" style="margin-top: 0px;" border="1" cellpadding="6">
        <thead>
        <tr>
            <th style="width:10%;">Sl. No.</th>
            <th style="width:65%;">Name and Address</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assigns_order_15 as $i => $assign)
            @php
                $t = $assign->teacher;
            @endphp
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:left;">
                    {{ $t->user->name ?? 'N/A' }},
                    {{ $t->designation->designation ?? '' }},
                    {{ $t->department->fullname ?? '' }}, DUET, Gazipur
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
@endif


@php
    $ordinal = function ($n) {
        $n = (int) $n;
        if ($n % 100 >= 11 && $n % 100 <= 13) return $n.'th';
        $suf = ['th','st','nd','rd','th','th','th','th','th','th'];
        return $n . $suf[$n % 10];
    };

    $examTypeId = isset($exam_type) ? (int)$exam_type : (int)($session_info->exam_type_id ?? 0);
    $examLabel  = $examTypeId === 1 ? 'Regular' : ($examTypeId === 2 ? 'Review' : null);

    $yearTxt     = $ordinal($session_info->year ?? 0) . ' year';
    $semesterTxt = $ordinal($session_info->semester ?? 0) . ' Semester';
    $sessionTxt  = $session_info->session ?? '';
@endphp

    <!-- Wrapper aligns the block to the right -->
<div style="width:100%; text-align:right; margin-top:80px;">
    <!-- Inline-table shrinks to content; margin-left:auto pushes to right -->
    <table style="display:inline-table; border-collapse:collapse; margin-left:auto; text-align:center;">
        <tbody>
        <tr>
            <td>Chairman</td>
        </tr>
        <tr>
            <td>Examination Committee</td>
        </tr>
        <tr>
            <td>
                B. Arch. {{ $yearTxt }} {{ $semesterTxt }}
                @if($examLabel) ({{ $examLabel }}) @endif
                Examination - {{ $sessionTxt }}
            </td>
        </tr>
        </tbody>
    </table>
</div>


@endhasanyrole

</body>
</html>
