<?php

namespace App\Http\Controllers;

use App\Models\ExamType;
use App\Models\RateAssign;
use App\Models\RateHead;
use App\Models\Session;
use App\Services\ApiData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class StatementReviewController extends Controller
{
    public function reviewSessionShow(){
        $sessions=ApiData::getReviewSessions();
        if($sessions === null) {
            return redirect()->back()->with([
                'message' => 'Session Import Failed',
                'alert-type' => 'error',
            ]);
        }
        return view('statement.session_view.review_session_list',compact('sessions'));
    }

    public function reviewSessionExtraShow(){
        $sessionData = ApiData::getReviewSessionExtra(); // should contain ->session

        //return $sessionData;

        // Validate the API data
        if (!$sessionData || empty($sessionData->session)) {
            return redirect()->back()->with([
                'message' => 'Session Import Failed (missing session value).',
                'alert-type' => 'error',
            ]);
        }

        // Query: session match, exam_type_id = 2 (review), ugr_id is NULL
        $sessions = Session::query()
            ->where('session', $sessionData->session)
            ->where('exam_type_id', 2)
            ->whereNull('ugr_id')
            ->where('status', 1)
            ->orderBy('id')
            ->get();

        if ($sessions->isEmpty()) {
            return redirect()->back()->with([
                'message' => 'No matching review sessions found.',
                'alert-type' => 'error',
            ])->withInput();
        }
        return view('statement.session_view.review_session_list_extra',compact('sessions'));
    }

    public function reviewStatementGenerate(Request $request){
        $sid = $request->sid;

        $exam_type = ExamType::where('type','Review')->value('id');

        // try to find session where id = sid and ugr_id is null
        $session_info = Session::whereNull('ugr_id')
            ->where('exam_type_id', $exam_type)
            ->where('id', $sid)
            ->where('status', 1)
            ->first();

        // if not found, try where ugr_id = sid
        if (!$session_info) {
            $session_info = Session::where('ugr_id', $sid)
                ->where('exam_type_id', $exam_type)
                ->first();
        }



        $rateHead_order_1 = RateHead::where('order_no', '1')->first();

        $assigns_order_1 = RateAssign::with([
            'teacher.user','teacher.designation','teacher.department','teacher.university', // <-- add university
            'employee.user','employee.designation','employee.department','rateHead'
        ])
            ->where('session_id', $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->when($rateHead_order_1, fn($q) => $q->where('rate_head_id', $rateHead_order_1->id))
            ->get();

        // Get department head email
        $head      = ApiData::getHead();
        $headEmail = data_get($head, 'teacher.user.email') ?? data_get($head, 'head.teacher.user.email');

        // Sort: KUET(6) teachers → chairman in bucket → designation_id asc → (optional) name
        $assigns_order_1 = $assigns_order_1->sortBy([
            // 1) KUET (university id == 6) teachers first; everyone else after
            fn($a, $b) =>
                ((int)! (data_get($a, 'teacher.university.id') === 6))
                <=>
                ((int)! (data_get($b, 'teacher.university.id') === 6)),

            // 2) Chairman first within each group
            function ($a, $b) use ($headEmail) {
                $ea = data_get($a, 'teacher.user.email') ?? data_get($a, 'employee.user.email');
                $eb = data_get($b, 'teacher.user.email') ?? data_get($b, 'employee.user.email');
                return (int)($eb === $headEmail) <=> (int)($ea === $headEmail);
            },

            // 3) Then by designation id (ascending)
            fn($a, $b) =>
                (int)(data_get($a, 'teacher.designation.id') ?? data_get($a, 'employee.designation.id') ?? 9999)
                <=>
                (int)(data_get($b, 'teacher.designation.id') ?? data_get($b, 'employee.designation.id') ?? 9999),

            // 4) (Optional) by name to keep stable order within same designation
            fn($a, $b) => strcasecmp(
                data_get($a, 'teacher.user.name') ?? data_get($a, 'employee.user.name') ?? '',
                data_get($b, 'teacher.user.name') ?? data_get($b, 'employee.user.name') ?? ''
            ),
        ])->values();




        //order 2/3
        $rateHead_order_2 = RateHead::where('order_no', '2')->first();

        $assigns_order_2 = RateAssign::with([
            'teacher.user','teacher.designation','teacher.department',
            'employee.user','employee.designation','employee.department','rateHead'
        ])
            ->where('session_id',  $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->where('rate_head_id', $rateHead_order_2->id)
            ->whereNotNull('course_code')
            ->orderBy('course_code')
            ->orderBy('id')
            ->get()
            ->groupBy('course_code');



        //order 4
        $rateHead_order_4 = RateHead::where('order_no', '4')->first();

        $assigns_order_4 = RateAssign::with([
            'teacher.user','teacher.designation','teacher.department',
            'employee.user','employee.designation','employee.department','rateHead'
        ])
            ->where('session_id',  $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->where('rate_head_id', $rateHead_order_4->id)
            ->whereNotNull('course_code')
            ->orderBy('course_code')
            ->orderBy('id')
            ->get()
            ->groupBy('course_code');


        //order 5
        $rateHead_order_5 = RateHead::where('order_no', '5')->first();

        $assigns_order_5 = RateAssign::with([
            'teacher.user','teacher.designation','teacher.department',
            'employee.user','employee.designation','employee.department','rateHead'
        ])
            ->where('session_id',  $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->where('rate_head_id', $rateHead_order_5->id)
            ->whereNotNull('course_code')
            ->orderBy('course_code')
            ->orderBy('id')
            ->get()
            ->groupBy('course_code');


        //order 9
        $rateHead_order_9 = RateHead::where('order_no', '9')->first();

        $assigns_order_9 = RateAssign::with([
            'teacher.user','teacher.designation','teacher.department',
            'employee.user','employee.designation','employee.department','rateHead'
        ])
            ->where('session_id',  $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->where('rate_head_id', $rateHead_order_9->id)
            ->whereNotNull('course_code')
            ->orderBy('course_code')
            ->orderBy('id')
            ->get()
            ->groupBy('course_code');


        //8.a
        $rateHead_order_8_a = RateHead::where('order_no', '8.a')->first();

        $assigns_order_8_a = RateAssign::with([
            'teacher.user','teacher.designation','teacher.department',
            'employee.user','employee.designation','employee.department','rateHead'
        ])
            ->where('session_id',  $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->where('rate_head_id', $rateHead_order_8_a->id)
            ->whereNotNull('course_code')
            ->orderBy('course_code')
            ->orderBy('id')
            ->get()
            ->groupBy('course_code');



        //8.b
        //order 9
        $rateHead_order_8_b = RateHead::where('order_no', '8.b')->first();

        $assigns_order_8_b = RateAssign::with([
            'teacher.user','teacher.designation','teacher.department',
            'employee.user','employee.designation','employee.department','rateHead'
        ])
            ->where('session_id',  $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->where('rate_head_id', $rateHead_order_8_b->id)
            ->whereNotNull('course_code')
            ->orderBy('course_code')
            ->orderBy('id')
            ->get()
            ->groupBy('course_code');


        //10.a
        $rateHead_order_10_a = RateHead::where('order_no', '10.a')->first();

        $assigns_order_10_a = RateAssign::with([
            'teacher.user','teacher.designation','teacher.department',
            'employee.user','employee.designation','employee.department','rateHead'
        ])
            ->where('session_id',  $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->where('rate_head_id', $rateHead_order_10_a->id)
            ->whereNotNull('course_code')
            ->orderBy('course_code')
            ->orderBy('id')
            ->get()
            ->groupBy('course_code');



        //10.b
        $rateHead_order_10_b = RateHead::where('order_no', '10.b')->first();

        $assigns_order_10_b = RateAssign::with([
            'teacher.user','teacher.designation','teacher.department',
            'employee.user','employee.designation','employee.department','rateHead'
        ])
            ->where('session_id',  $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->where('rate_head_id', $rateHead_order_10_b->id)
            ->whereNotNull('course_code')
            ->orderBy('course_code')
            ->orderBy('id')
            ->get()
            ->groupBy('course_code');


        //8.d
        $rateHead_order_8_d = RateHead::where('order_no', '8.d')->first();

        $assigns_order_8_d = RateAssign::with([
            'teacher.user','teacher.designation','teacher.department',
            'employee.user','employee.designation','employee.department','rateHead'
        ])
            ->where('session_id',  $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->where('rate_head_id', $rateHead_order_8_d->id)
            ->whereNotNull('course_code')
            ->orderBy('course_code')
            ->orderBy('id')
            ->get()
            ->groupBy('teacher_id');


        //order 8.c
        $rateHead_order_8_c = RateHead::where('order_no', '8.c')->first();

        $assigns_order_8_c = RateAssign::with([
            'teacher.user','teacher.designation','teacher.department',
            'employee.user','employee.designation','employee.department','rateHead'
        ])
            ->where('session_id', $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->when($rateHead_order_8_c, fn($q) => $q->where('rate_head_id', $rateHead_order_8_c->id))
            ->get();


        // order 12.a
        $rateHead_order_12_a = RateHead::where('order_no', '12.a')->first();

        $assigns_order_12_a = RateAssign::with(['teacher.user','teacher.designation','teacher.department'])
            ->select('teacher_id')
            ->selectRaw('COALESCE(SUM(no_of_items), 0)   as total_students')  // sum of no_of_items
            ->where('session_id', $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->when($rateHead_order_12_a, fn($q) => $q->where('rate_head_id', $rateHead_order_12_a->id))
            ->groupBy('teacher_id')
            ->get();

        // order 12.a
        $rateHead_order_12_b = RateHead::where('order_no', '12.b')->first();

        $assigns_order_12_b = RateAssign::with(['employee.user','employee.designation','employee.department'])
            ->select('employee_id')
            ->selectRaw('COALESCE(SUM(no_of_items), 0)   as total_students')  // sum of no_of_items
            ->where('session_id', $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->when($rateHead_order_12_b, fn($q) => $q->where('rate_head_id', $rateHead_order_12_b->id))
            ->groupBy('employee_id')
            ->get();

        //return $assigns_order_12_a;


        // order 11
        $rateHead_order_11 = RateHead::where('order_no', '11')->first();

        $assigns_order_11 = RateAssign::with(['teacher.user','teacher.designation','teacher.department'])
            ->select('teacher_id')
            ->selectRaw('COALESCE(SUM(no_of_items), 0)   as total_students')  // sum of no_of_items
            ->where('session_id', $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->when($rateHead_order_11, fn($q) => $q->where('rate_head_id', $rateHead_order_11->id))
            ->groupBy('teacher_id')
            ->get();




        //M) order 13
        $rateHead_order_13 = RateHead::where('order_no', '13')->first();

        $assigns_order_13 = RateAssign::with([
            'teacher.user','teacher.designation','teacher.department',
            'employee.user','employee.designation','employee.department','rateHead'
        ])
            ->where('session_id', $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->when($rateHead_order_13, fn($q) => $q->where('rate_head_id', $rateHead_order_13->id))
            ->get();


        //order 16
        $rateHead_order_16 = RateHead::where('order_no', '16')->first();

        $assigns_order_16 = RateAssign::with([
            'teacher.user','teacher.designation','teacher.department',
            'employee.user','employee.designation','employee.department','rateHead'
        ])
            ->where('session_id', $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->when($rateHead_order_16, fn($q) => $q->where('rate_head_id', $rateHead_order_16->id))
            ->get();


        // order 7.e
        $rateHead_order_7_e = RateHead::where('order_no', '7.e')->first();

        $assigns_order_7_e = RateAssign::with(['teacher.user','teacher.designation','teacher.department'])
            ->select('teacher_id')
            ->selectRaw('COALESCE(SUM(no_of_items), 0)   as total_students')  // sum of no_of_items
            ->where('session_id', $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->when($rateHead_order_7_e, fn($q) => $q->where('rate_head_id', $rateHead_order_7_e->id))
            ->groupBy('teacher_id')
            ->get();

        // order 7.f
        $rateHead_order_7_f = RateHead::where('order_no', '7.f')->first();

        $assigns_order_7_f = RateAssign::with(['teacher.user','teacher.designation','teacher.department'])
            ->select('teacher_id')
            ->selectRaw('COALESCE(SUM(no_of_items), 0)   as total_students')  // sum of no_of_items
            ->where('session_id', $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->when($rateHead_order_7_f, fn($q) => $q->where('rate_head_id', $rateHead_order_7_f->id))
            ->groupBy('teacher_id')
            ->get();


        // order 6.c
        $rateHead_order_6_c = RateHead::where('order_no', '6.c')->first();

        $assigns_order_6_c = RateAssign::with(['teacher.user','teacher.designation','teacher.department'])
            ->select('teacher_id')
            ->selectRaw('COALESCE(SUM(no_of_items), 0)   as total_students')  // sum of no_of_items
            ->where('session_id', $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->when($rateHead_order_6_c, fn($q) => $q->where('rate_head_id', $rateHead_order_6_c->id))
            ->groupBy('teacher_id')
            ->get();

        //return $assigns_order_6_c;


        // order 6.a
        $rateHead_order_6_a = RateHead::where('order_no', '6.a')->first();

        $assigns_order_6_a = RateAssign::with(['teacher.user','teacher.designation','teacher.department'])
            ->select('teacher_id')
            // keep decimals by casting before SUM
            ->selectRaw('COALESCE(SUM(CASE WHEN is_internal = 1 THEN CAST(no_of_items AS DECIMAL(18,6)) ELSE 0 END), 0) AS internal_students')
            ->selectRaw('COALESCE(SUM(CASE WHEN is_external = 1 THEN CAST(no_of_items AS DECIMAL(18,6)) ELSE 0 END), 0) AS external_students')
            ->where('session_id', $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->when($rateHead_order_6_a, fn($q) => $q->where('rate_head_id', $rateHead_order_6_a->id))
            ->groupBy('teacher_id')
            ->get();

        //return $assigns_order_6_a;


        // order 6.d
        $rateHead_order_6_d = RateHead::where('order_no', '6.d')->first();

        $assigns_order_6_d = RateAssign::with(['teacher.user','teacher.designation','teacher.department'])
            ->select('teacher_id')
            ->selectRaw('COALESCE(SUM(no_of_items), 0)   as total_students')  // sum of no_of_items
            ->where('session_id', $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->when($rateHead_order_6_d, fn($q) => $q->where('rate_head_id', $rateHead_order_6_d->id))
            ->groupBy('teacher_id')
            ->get();

        // order 6.b
        $rateHead_order_6_b = RateHead::where('order_no', '6.b')->first();

        $assigns_order_6_b = RateAssign::with(['teacher.user','teacher.designation','teacher.department'])
            ->select('teacher_id')
            ->selectRaw('COALESCE(SUM(no_of_items), 0)   as total_students')  // sum of no_of_items
            ->where('session_id', $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->when($rateHead_order_6_b, fn($q) => $q->where('rate_head_id', $rateHead_order_6_b->id))
            ->groupBy('teacher_id')
            ->get();


        // order 14
        $rateHead_order_14 = RateHead::where('order_no', '14')->first();

        $assigns_order_14 = RateAssign::with(['teacher.user','teacher.designation','teacher.department'])
            ->select('teacher_id')
            ->selectRaw('COALESCE(SUM(no_of_items), 0)   as total_students')  // sum of no_of_items
            ->where('session_id', $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->when($rateHead_order_14, fn($q) => $q->where('rate_head_id', $rateHead_order_14->id))
            ->groupBy('teacher_id')
            ->get();


        // order 15
        $rateHead_order_15 = RateHead::where('order_no', '15')->first();

        $assigns_order_15 = RateAssign::with(['teacher.user','teacher.designation','teacher.department'])
            ->select('teacher_id')
            ->selectRaw('COALESCE(SUM(no_of_items), 0)   as total_students')  // sum of no_of_items
            ->where('session_id', $session_info->id)
            ->where('exam_type_id', $exam_type)
            ->when($rateHead_order_15, fn($q) => $q->where('rate_head_id', $rateHead_order_15->id))
            ->groupBy('teacher_id')
            ->get();

        //return $assigns_order_7_e;


        //return $assigns_order_1;
        return view('statement.statement_download.review_statement', [
            'session_info'      => $session_info,
            'exam_type'         => $exam_type,
            'assigns_order_1'   => $assigns_order_1,
            'headEmail'         => $headEmail,

            'assigns_order_2'   => $assigns_order_2,
            'assigns_order_4'   => $assigns_order_4,
            'assigns_order_5'   => $assigns_order_5,
            'assigns_order_9'   => $assigns_order_9,
            'assigns_order_8_a' => $assigns_order_8_a,
            'assigns_order_8_b' => $assigns_order_8_b,
            'assigns_order_10_a'=> $assigns_order_10_a,
            'assigns_order_10_b'=> $assigns_order_10_b,
            'assigns_order_8_d' => $assigns_order_8_d,
            'assigns_order_8_c' => $assigns_order_8_c,

            'assigns_order_12_a'=> $assigns_order_12_a,
            'assigns_order_12_b'=> $assigns_order_12_b,
            'assigns_order_11'  => $assigns_order_11,

            'assigns_order_13'  => $assigns_order_13,
            'assigns_order_16'  => $assigns_order_16,
            'assigns_order_7_e' => $assigns_order_7_e,
            'assigns_order_7_f' => $assigns_order_7_f,
            'assigns_order_6_c' => $assigns_order_6_c,
            'assigns_order_6_a' => $assigns_order_6_a,
            'assigns_order_6_d' => $assigns_order_6_d,
            'assigns_order_6_b' => $assigns_order_6_b,
            'assigns_order_14'  => $assigns_order_14,
            'assigns_order_15'  => $assigns_order_15,
        ]);
    }
}
