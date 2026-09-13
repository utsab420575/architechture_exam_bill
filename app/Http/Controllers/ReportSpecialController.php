<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ExamType;
use App\Models\RateAmount;
use App\Models\RateHead;
use App\Models\Session;
use App\Models\Teacher;
use App\Services\ApiData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportSpecialController extends Controller
{
    public function specialSessionShow(){
        $sessionData = ApiData::getSpecialSession(); // should contain ->session

        //return $sessionData;

        // Validate the API data
        if (!$sessionData || empty($sessionData->session)) {
            return redirect()->back()->with([
                'message' => 'Session Import Failed (missing session value).',
                'alert-type' => 'error',
            ]);
        }

        // Query: session match, exam_type_id = 2 (special), ugr_id is NULL
        $sessions = Session::query()
            ->where('session', $sessionData->session)
            ->where('exam_type_id', 3)
            ->where('status', 1)
            ->whereNull('ugr_id')
            ->orderBy('id')
            ->get();

        if ($sessions->isEmpty()) {
            return redirect()->back()->with([
                'message' => 'No matching Special sessions found.',
                'alert-type' => 'error',
            ])->withInput();
        }

        //return $sessions;
        return view('report.session_view.special_session_list', compact('sessions'));
    }

    public function specialReportGenerate(Request $request){
        //return 'hi';
        $sid = $request->sid;

        $exam_type = ExamType::where('type','Special')->value('id');


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
                ->where('status', 1)
                ->first();
        }
        //return $session_info;
        Log::info('📥 Received request to generate Regular Report PDF', ['ugr_session_id' => $sid]);



        $teachers = Teacher::with([
            'user',
            'designation',
            'rateAssigns',
        ])->whereHas('rateAssigns', function ($query) use ($session_info,$exam_type) {
            $query->where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type);
        })
            ->orderByRaw('department_id = 2 DESC') // ✅ Architecture first
            ->orderBy('department_id')             // Then others by department
            ->get();

        Log::info('👨‍🏫 Total teachers found for this session: ' . $teachers->count());


        $employees = Employee::with([
            'user',
            'designation',
            'rateAssigns',
        ])->whereHas('rateAssigns', function ($query) use ($session_info,$exam_type) {
            $query->where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type);
        })
            ->orderByRaw('department_id = 2 DESC') // ✅ Architecture first
            ->orderBy('department_id')             // Then others by department
            ->get();

        Log::info('👨‍🏫 Total teachers found for this session: ' . $teachers->count());



        $rateHead_order_1 = RateHead::where('order_no', 1)->first();
        // Assuming $session_info is already available
        $rateAmount_order_1 = RateAmount::where('session_id', $session_info->id)
            ->where('exam_type_id',$exam_type)
            ->whereHas('rateHead', function ($query) {
                $query->where('order_no', 1);
            })
            ->with('rateHead')
            ->first(); // or get() if you expect multiple

        Log::info('📦 RateHead Order 1', optional($rateHead_order_1)->toArray() ?? []);
        Log::info('📦 RateAmount Order 1', optional($rateAmount_order_1)->toArray() ?? []);

        $rateHead_order_2 = RateHead::where('order_no', 2)->first();
        // Assuming $session_info is already available
        $rateAmount_order_2 = RateAmount::where('session_id', $session_info->id)
            ->where('exam_type_id',$exam_type)
            ->whereHas('rateHead', function ($query) {
                $query->where('order_no', 2);
            })
            ->with('rateHead')
            ->first(); // or get() if you expect multiple
        Log::info('📦 RateHead Order 1', optional($rateHead_order_2)->toArray() ?? []);
        Log::info('📦 RateAmount Order 1', optional($rateAmount_order_2)->toArray() ?? []);


        $rateHead_order_3 = RateHead::where('order_no', 3)->first();
        // Assuming $session_info is already available
        $rateAmount_order_3 = RateAmount::where('session_id', $session_info->id)
            ->where('exam_type_id',$exam_type)
            ->whereHas('rateHead', function ($query) {
                $query->where('order_no', 3);
            })
            ->with('rateHead')
            ->first(); // or get() if you expect multiple
        Log::info('📦 RateHead Order 3', optional($rateHead_order_3)->toArray() ?? []);
        Log::info('📦 RateAmount Order 3', optional($rateAmount_order_3)->toArray() ?? []);




        //Order 4
        $rateHead_order_4 = RateHead::where('order_no', 4)->first();
        Log::info('📦 rateHead_order_4 Order 1', optional($rateHead_order_4)->toArray() ?? []);

        //Order 5
        $rateHead_order_5 = RateHead::where('order_no', 5)->first();
        Log::info('📦 rateHead_order_5', optional($rateHead_order_5)->toArray() ?? []);

        //Order 6.a
        $rateHead_order_6a = RateHead::where('order_no', '6.a')->first();
        Log::info('📦 rateHead_order_6a', optional($rateHead_order_6a)->toArray() ?? []);

        //Order 6.b
        $rateHead_order_6b = RateHead::where('order_no', '6.b')->first();
        Log::info('📦 RateHead Order 6b', optional($rateHead_order_6b)->toArray() ?? []);


        //Order 6.c
        $rateHead_order_6c = RateHead::where('order_no', '6.c')->first();
        Log::info('📦 RateHead Order 6c', optional($rateHead_order_6c)->toArray() ?? []);



        //Order 6.d
        $rateHead_order_6d = RateHead::where('order_no', '6.d')->first();
        Log::info('📦 $rateHead_order_6d', optional($rateHead_order_6d)->toArray() ?? []);




        //Order 7.e
        $rateHead_order_7e = RateHead::where('order_no', '7.e')->first();
        Log::info('📦 rateHead_order_7e', optional($rateHead_order_7e)->toArray() ?? []);



        //Order 7.f
        $rateHead_order_7f = RateHead::where('order_no', '7.f')->first();
        Log::info('📦 rateHead_order_7f', optional($rateHead_order_7f)->toArray() ?? []);



        //Order 8.a
        $rateHead_order_8a = RateHead::where('order_no', '8.a')->first();
        // Assuming $session_info is already available
        $rateAmount_order_8a = RateAmount::where('session_id', $session_info->id)
            ->where('exam_type_id',$exam_type)
            ->whereHas('rateHead', function ($query) {
                $query->where('order_no', '8.a');
            })
            ->with('rateHead')
            ->first(); // or get() if you expect multiple
        Log::info('📦 rateHead_order_8a', optional($rateHead_order_8a)->toArray() ?? []);
        Log::info('📦 rateAmount_order_8a', optional($rateHead_order_8a)->toArray() ?? []);


        //Order 8.b
        $rateHead_order_8b = RateHead::where('order_no', '8.b')->first();
        Log::info('📦 rateHead_order_8b', optional($rateHead_order_8b)->toArray() ?? []);


        //Order 8.c
        $rateHead_order_8c = RateHead::where('order_no', '8.c')->first();
        Log::info('📦 rateHead_order_8c', optional($rateHead_order_8c)->toArray() ?? []);


        //Order 8.d
        $rateHead_order_8d = RateHead::where('order_no', '8.d')->first();
        Log::info('📦rateHead_order_8d', optional($rateHead_order_8d)->toArray() ?? []);


        //Order 9
        $rateHead_order_9 = RateHead::where('order_no', '9')->first();
        // Assuming $session_info is already available
        $rateAmount_order_9 = RateAmount::where('session_id', $session_info->id)
            ->where('exam_type_id',$exam_type)
            ->whereHas('rateHead', function ($query) {
                $query->where('order_no', '9');
            })
            ->with('rateHead')
            ->first(); // or get() if you expect multiple
        Log::info('📦 rateHead_order_9', optional($rateHead_order_9)->toArray() ?? []);
        Log::info('📦 rateAmount_order_9', optional($rateAmount_order_9)->toArray() ?? []);





        //Order 10.a
        $rateHead_order_10_a = RateHead::where('order_no', '10.a')->first();
        // Assuming $session_info is already available
        $rateAmount_order_10_a = RateAmount::where('session_id', $session_info->id)
            ->where('exam_type_id',$exam_type)
            ->whereHas('rateHead', function ($query) {
                $query->where('order_no', '10.a');
            })
            ->with('rateHead')
            ->first(); // or get() if you expect multiple
        Log::info('📦 RateHead Order 1', optional($rateHead_order_10_a)->toArray() ?? []);
        Log::info('📦 RateAmount Order 1', optional($rateAmount_order_10_a)->toArray() ?? []);


        //Order 10.b
        $rateHead_order_10_b = RateHead::where('order_no', '10.b')->first();
        Log::info('📦 rateHead_order_10_b', optional($rateHead_order_10_b)->toArray() ?? []);



        //Order 11
        $rateHead_order_11 = RateHead::where('order_no', '11')->first();
        // Assuming $session_info is already available
        $rateAmount_order_11 = RateAmount::where('session_id', $session_info->id)
            ->where('exam_type_id',$exam_type)
            ->whereHas('rateHead', function ($query) {
                $query->where('order_no', '11');
            })
            ->with('rateHead')
            ->first(); // or get() if you expect multiple
        Log::info('📦 RateHead Order 1', optional($rateHead_order_11)->toArray() ?? []);
        Log::info('📦 RateAmount Order 1', optional($rateHead_order_11)->toArray() ?? []);


        //Order 12.a
        $rateHead_order_12_a = RateHead::where('order_no', '12.a')->first();
        // Assuming $session_info is already available
        $rateAmount_order_12_a = RateAmount::where('session_id', $session_info->id)
            ->where('exam_type_id',$exam_type)
            ->whereHas('rateHead', function ($query) {
                $query->where('order_no', '12.a');
            })
            ->with('rateHead')
            ->first(); // or get() if you expect multiple
        Log::info('📦 RateHead Order 1', optional($rateHead_order_12_a)->toArray() ?? []);
        Log::info('📦 RateAmount Order 1', optional($rateHead_order_12_a)->toArray() ?? []);

        //Order 12.b
        $rateHead_order_12_b = RateHead::where('order_no', '12.b')->first();
        // Assuming $session_info is already available
        $rateAmount_order_12_b = RateAmount::where('session_id', $session_info->id)
            ->where('exam_type_id',$exam_type)
            ->whereHas('rateHead', function ($query) {
                $query->where('order_no', '12.b');
            })
            ->with('rateHead')
            ->first(); // or get() if you expect multiple
        Log::info('📦 RateHead Order 1', optional($rateHead_order_12_b)->toArray() ?? []);
        Log::info('📦 RateAmount Order 1', optional($rateHead_order_12_b)->toArray() ?? []);



        //Order 13
        $rateHead_order_13 = RateHead::where('order_no', '13')->first();
        Log::info('📦 rateHead_order_13', optional($rateHead_order_13)->toArray() ?? []);



        //Order 14
        $rateHead_order_14 = RateHead::where('order_no', '14')->first();
        Log::info('📦 rateHead_order_14', optional($rateHead_order_14)->toArray() ?? []);
        //dd($rateAmount_order_14);


        //Order 15
        $rateHead_order_15 = RateHead::where('order_no', '15')->first();
        // Assuming $session_info is already available
        $rateAmount_order_15 = RateAmount::where('session_id', $session_info->id)
            ->where('exam_type_id',$exam_type)
            ->whereHas('rateHead', function ($query) {
                $query->where('order_no', '15');
            })
            ->with('rateHead')
            ->first(); // or get() if you expect multiple
        Log::info('📦 RateHead Order 1', optional($rateHead_order_15)->toArray() ?? []);
        Log::info('📦 RateAmount Order 1', optional($rateHead_order_15)->toArray() ?? []);


        //Order 16
        $rateHead_order_16 = RateHead::where('order_no', '16')->first();
        Log::info('📦rateHead_order_16', optional($rateHead_order_16)->toArray() ?? []);


        $pdf = Pdf::loadView('report.pdf_download.special_report', [
            'teachers' => $teachers,
            'employees' => $employees,
            'session_info' => $session_info,

            'rateHead_order_1' => $rateHead_order_1,
            'rateAmount_order_1'=>$rateAmount_order_1,
            'rateHead_order_2' => $rateHead_order_2,
            'rateAmount_order_2'=>$rateAmount_order_2,
            'rateHead_order_3' => $rateHead_order_3,
            'rateAmount_order_3'=>$rateAmount_order_3,
            'rateHead_order_4' => $rateHead_order_4,
            'rateHead_order_5' => $rateHead_order_5,

            'rateHead_order_6a' => $rateHead_order_6a,
            'rateHead_order_6b' => $rateHead_order_6b,

            'rateHead_order_6c' => $rateHead_order_6c,

            'rateHead_order_6d' => $rateHead_order_6d,


            'rateHead_order_7e' => $rateHead_order_7e,

            'rateHead_order_7f' => $rateHead_order_7f,


            'rateHead_order_8a' => $rateHead_order_8a,
            'rateAmount_order_8a'=>$rateAmount_order_8a,
            'rateHead_order_8b' => $rateHead_order_8b,

            'rateHead_order_8c' => $rateHead_order_8c,

            'rateHead_order_8d' => $rateHead_order_8d,


            'rateHead_order_9' => $rateHead_order_9,
            'rateAmount_order_9'=>$rateAmount_order_9,


            'rateHead_order_10_a' => $rateHead_order_10_a,
            'rateAmount_order_10_a'=>$rateAmount_order_10_a,
            'rateHead_order_10_b' => $rateHead_order_10_b,


            'rateHead_order_11' => $rateHead_order_11,
            'rateAmount_order_11'=>$rateAmount_order_11,


            'rateHead_order_12_a' => $rateHead_order_12_a,
            'rateAmount_order_12_a'=>$rateAmount_order_12_a,
            'rateHead_order_12_b' => $rateHead_order_12_b,
            'rateAmount_order_12_b'=>$rateAmount_order_12_b,

            'rateHead_order_13' => $rateHead_order_13,


            'rateHead_order_14' => $rateHead_order_14,


            'rateHead_order_15' => $rateHead_order_15,
            'rateAmount_order_15'=>$rateAmount_order_15,


            'rateHead_order_16' => $rateHead_order_16,

        ])->setPaper('legal', 'portrait'); // or 'landscape';


        $filename = 'special_' . $session_info->session . '_' . $session_info->year . '_' . $session_info->semester . '_exam_bill.pdf';
        return $pdf->stream($filename);
        // return $pdf->download('demo_exam_bill.pdf');
    }
}
