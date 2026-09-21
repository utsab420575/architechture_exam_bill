<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ExamType;
use App\Models\RateAmount;
use App\Models\RateAssign;
use App\Models\RateHead;
use App\Models\Session;
use App\Models\Teacher;
use App\Services\ApiData;
use App\Services\LocalData;
use App\Services\ReviewSessionFormDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CommitteeInputReviewController extends Controller
{
    //showing session list
    public function reviewSessionShow(){
        //regular , review session is same
        $sessions=ApiData::getReviewSessions();
        //return $sessions;
        //dd(ApiData::getReviewSessions());
        //return $sessions;
        if($sessions === null) {
            return redirect()->back()->with([
                'message' => 'Session Import Failed',
                'alert-type' => 'error',
            ]);
        }

        //return $sessions;
        return view('committee_input.session_view.review_session_list',compact('sessions'));
    }


    public function reviewSessionShowExtra()
    {
        //only single session retrive for use session(2023-2024 / 2024-2025 ........)
        $sessionData = ApiData::getReviewSessionExtra(); // should contain ->session
        //return $sessionData;

        // Validate the API data
        if (!$sessionData || empty($sessionData->session)) {
            return redirect()->back()->with([
                'message' => 'Session Import Failed (missing session value).',
                'alert-type' => 'error',
            ]);
        }

        //here only showing manually inserted sessions
        // Query: session match, exam_type_id = 2 (review), ugr_id is NULL
        // We search in local database using only session(2023-2024/2024-2025)

        //if review session get from API is 1/2,2/2,3/2,4/2 than extra review session(we manually inputed) will be 1/1,2/1,3/1,4/1,5/1
        //if review session get from API is 1/1,2/1,3/1,4/1 than extra review session(we manually inputed) will be 1/2,2/2,3/2,4/2,5/2
        $sessions = Session::query()
            ->where('session', $sessionData->session)
            ->where('exam_type_id', 2)
            ->whereNull('ugr_id')
            ->where('status',1)
            ->orderBy('id')
            ->get();

        if ($sessions->isEmpty()) {
            return redirect()->back()->with([
                'message' => 'No matching review sessions found.',
                'alert-type' => 'error',
            ])->withInput();
        }


        //return $sessions;

        return view('committee_input.session_view.review_session_list_extra', compact('sessions'));

    }


    private function getOrCreateRateAmount($rateHeadId, $sessionId, $examTypeId, array $allData)
    {
        $rateAmount = RateAmount::updateOrCreate(
            [
                'rate_head_id' => $rateHeadId,
                'session_id' => $sessionId,
                'exam_type_id' => $examTypeId,
            ],
            array_merge($allData, [
                'rate_head_id' => $rateHeadId,
                'session_id' => $sessionId,
                'exam_type_id' => $examTypeId,
                'saved' => 1, // Assuming 'saved' is a boolean or flag for record status
            ])
        );

        Log::info('Confirmed Rate Amount', $rateAmount ? $rateAmount->toArray() : ['rateAmount' => null]);
        return $rateAmount;
    }

    private function getOrCreateRateHead($orderNo, array $allData)
    {
        $rateHead = RateHead::where('order_no', $orderNo)->first();

        if ($rateHead) {
            Log::info("📄 RateHead found for order_no {$orderNo}", $rateHead->toArray());
        }
        if (!$rateHead) {
            $rateHead = new RateHead();
            $rateHead->fill($allData);
            $rateHead->order_no = $orderNo;

            if ($rateHead->save()) {
                Log::info("✅ RateHead created for order_no {$orderNo}", $rateHead->toArray());
            } else {
                Log::error("❌ Failed to create RateHead for order_no {$orderNo}");
            }
        }

        Log::info("📄 RateHead Confirmed {$orderNo}", $rateHead->toArray());

        return $rateHead;
    }
    public function reviewSessionForm(Request $request)
    {

        $sid=$request->sid;
        $exam_type = ExamType::where('type', 'review')->first();
        $session_info = LocalData::getOrCreateReviewSession($sid,$exam_type->id);
        //return $session_info;

        $order = ['Arch', 'CE', 'ChE', 'Chem','CSE','EEE','FE','HSS','IPE','Math','ME','MME','Phy','TE']; // Custom order of departments

        $teachers = Teacher::with('user', 'designation', 'department')
            ->whereHas('department', function ($query) use ($order) {
                $query->whereIn('shortname', $order);
            })
            ->join('departments', 'teachers.department_id', '=', 'departments.id')
            ->orderByRaw("FIELD(departments.shortname, '" . implode("','", $order) . "')")
            ->select('teachers.*') // Select only teacher fields to avoid conflict
            ->get();
        //return $teachers;

        // Group by department short name
        $groupedTeachers = $teachers->groupBy(function ($teacher) {
            return $teacher->department->fullname ?? 'Unknown';
        });

        // Move 'Arch' to the beginning
        $groupedTeachers = $groupedTeachers->sortBy(function ($group, $key) {
            return $key === 'Architecture' ? 0 : 1;
        });
        //return $groupedTeachers;

        $employees = Employee::with('user', 'designation', 'department')
            ->where('department_id', 2)
            ->orderBy('id') // or any ordering you prefer
            ->get();

        // all theory course with teacher (multi-session payload)
        $all_course_with_teacher = ApiData::getSessionWiseTheoryCoursesReview($sid);

        // 1) Target year/semester from $session_info
        $targetYear     = (int) ($session_info->year ?? 0);
        $targetSemester = (int) ($session_info->semester ?? 0);

        // 2) Helper: extract (year, semester) from courseno (first & third digits)
        $pickYearSemesterFromCourseNo = function (?string $courseno): array {
            $digits = preg_replace('/\D/', '', (string) $courseno); // keep only digits
            if (strlen($digits) < 3) return [null, null];           // skip malformed codes
            return [(int)$digits[0], (int)$digits[2]];              // 1st=year, 3rd=semester
        };

        // 3) Flatten ALL sessions -> rows
        $sessions = $all_course_with_teacher->sessions ?? [];
        $flattenedRows = [];
        foreach ($sessions as $sess) {
            foreach (($sess->courses ?? []) as $row) {
                // each $row has ->courseObject and ->registered_students_count
                $flattenedRows[] = $row;
            }
        }

        // 4) Filter by year/semester pattern, and DEDUPE by course id WHILE KEEPING THE WRAPPER ROW
        $byCourseId = [];
        foreach ($flattenedRows as $row) {
            $course = $row->courseObject ?? null;
            if (!$course) continue;

            [$yr, $sem] = $pickYearSemesterFromCourseNo($course->courseno ?? '');
            if ($yr === $targetYear && $sem === $targetSemester) {
                // Keep the full row so Blade still has courseObject + registered_students_count
                $byCourseId[$course->id] = $row;
            }
        }

        $filteredRows = array_values($byCourseId);


        // === 4.5) Merge duplicates that only differ by hyphen/space in courseno ===
        // Key by a normalized courseno (remove non-alphanumerics, uppercase)
        // Keep the latest courseObject by id; sum the registered_students_count
        $bucket = [];

        $normalize = function (?string $code) {
            // CE 2111, CE-2111, ce-2111 -> CE2111
            return preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $code));
        };

        foreach ($filteredRows as $row) {
            if (!isset($row->courseObject)) continue;

            $course    = $row->courseObject;
            $origCode  = $course->courseno ?? '';
            $normCode  = $normalize($origCode);
            $count     = (int) ($row->registered_students_count ?? 0);
            $courseId  = (int) ($course->id ?? 0);

            if (!isset($bucket[$normCode])) {
                // seed
                $bucket[$normCode] = [
                    'row'        => $row,              // keep full wrapper (courseObject + registered_students_count)
                    'latest_id'  => $courseId,         // track latest by id
                    'coursenos'  => [$origCode],       // collect original courseno variants
                    'sum_count'  => $count,            // sum of registered_students_count
                ];
                continue;
            }

            // aggregate
            $bucket[$normCode]['sum_count'] += $count;

            // collect this courseno variant
            if (!in_array($origCode, $bucket[$normCode]['coursenos'], true)) {
                $bucket[$normCode]['coursenos'][] = $origCode;
            }

            // if this is the newer course by id, replace the base row (but keep the running sum & list)
            if ($courseId > $bucket[$normCode]['latest_id']) {
                $bucket[$normCode]['row']       = $row;
                $bucket[$normCode]['latest_id'] = $courseId;
            }
        }

        // Build merged rows: latest courseObject stays, code becomes "latest/others", count becomes sum
        $mergedRows = [];
        foreach ($bucket as $normCode => $data) {
            /** @var stdClass $row */
            $row = $data['row'];

            // Set summed count
            $row->registered_students_count = $data['sum_count'];

            // courseno display: put latest first, then the other variants
            $latestCode = $row->courseObject->courseno ?? '';
            $others     = array_values(array_diff($data['coursenos'], [$latestCode]));
            if (!empty($others)) {
                // e.g. "CE 2111/CE-2111/CE2111"
                $row->courseObject->courseno = $latestCode . '/' . implode('/', $others);
            }

            $mergedRows[] = $row;
        }



        // 5) Overwrite the payload to match what your Blade expects
        $all_course_with_teacher->courses = $mergedRows;
        //return $all_course_with_teacher->courses;

// (Optional) If you don't need the original sessions block anymore, you can slim it:
// unset($all_course_with_teacher->sessions);

// 6) Count for your Blade
        $number_of_theory_courses = count($mergedRows);

        //return $number_of_theory_courses;

        /* // Count number of theory courses
         $number_of_theory_courses = isset($all_course_with_teacher->courses)
             ? count($all_course_with_teacher->courses)
             : 0;*/

        // return $number_of_theory_courses;

        //no need to call again for class test(class test for theory course)
        // $all_course_with_class_test_teacher=ApiData::getSessionWiseTheoryCourses(sid);
        //all sessional course with teacher
        $all_sessional_course_with_teacher = ApiData::getSessionWiseSessionalCourses($sid);
        //all theory sessional courses
        $all_theory_sessional_courses_with_student_count = ApiData::getSessionWiseTheorySessionalCourses($sid);
        //all student advisor in specific student
        $all_advisor_with_student_count = ApiData::getSessionWiseStudentAdvisor($sid);
        //active head
        $teacher_head = ApiData::getHead();

        //total student
        $totalStudentInSession = ApiData::getTotalStudentInSession($sid);

        // return response()->json(['$all_course_with_teacher'=>$all_course_with_teacher]);
        // Preload rate amounts and existing assignments via service
        $preloadedData = ReviewSessionFormDataService::getPreloadedFormData($session_info, $exam_type->id);

        return view('committee_input.review_form.review_session_form', array_merge([
            'sid' => $sid,
            'session_info' => $session_info,
            'exam_type' => $exam_type->id,
            'teachers' => $teachers,
            'employees' => $employees,
            'teacher_head' => $teacher_head,
            'totalStudentInSession' => $totalStudentInSession,
            'groupedTeachers' => $groupedTeachers,
            'all_course_with_teacher' => $all_course_with_teacher,
            'number_of_theory_courses' => $number_of_theory_courses,
            'all_course_with_class_test_teacher' => $all_course_with_teacher,
            'all_sessional_course_with_teacher' => $all_sessional_course_with_teacher,
            'all_theory_sessional_courses_with_student_count' => $all_theory_sessional_courses_with_student_count,
            'all_advisor_with_student_count' => $all_advisor_with_student_count,
        ], $preloadedData));
    }

    //no need this ; we can delete this method;
    public function reviewSessionExtraForm(Request $request)
    {

        $sid=$request->sid;
        //return $sid;
        $exam_type = ExamType::where('type', 'review')->first();
        $session_info = Session::find($sid);
        //return $session_info;

        $order = ['Arch', 'CE', 'ChE', 'Chem','CSE','EEE','FE','HSS','IPE','Math','ME','MME','Phy','TE']; // Custom order of departments

        $teachers = Teacher::with('user', 'designation', 'department')
            ->whereHas('department', function ($query) use ($order) {
                $query->whereIn('shortname', $order);
            })
            ->join('departments', 'teachers.department_id', '=', 'departments.id')
            ->orderByRaw("FIELD(departments.shortname, '" . implode("','", $order) . "')")
            ->select('teachers.*') // Select only teacher fields to avoid conflict
            ->get();
        //return $teachers;

        // Group by department short name
        $groupedTeachers = $teachers->groupBy(function ($teacher) {
            return $teacher->department->fullname ?? 'Unknown';
        });

        // Move 'Arch' to the beginning
        $groupedTeachers = $groupedTeachers->sortBy(function ($group, $key) {
            return $key === 'Architecture' ? 0 : 1;
        });
        //return $groupedTeachers;

        $employees = Employee::with('user', 'designation', 'department')
            ->where('department_id', 2)
            ->orderBy('id') // or any ordering you prefer
            ->get();

        // all theory course with teacher (multi-session payload)
        $all_course_with_teacher = ApiData::getSessionWiseTheoryCoursesReview($sid);
        //return $all_course_with_teacher;

        // 1) Target year/semester from $session_info
        $targetYear     = (int) ($session_info->year ?? 0);
        $targetSemester = (int) ($session_info->semester ?? 0);
        //return $targetSemester;


        // 2) Helper: extract (year, semester) from courseno (first & third digits)
        $pickYearSemesterFromCourseNo = function (?string $courseno): array {
            $digits = preg_replace('/\D/', '', (string) $courseno); // keep only digits
            if (strlen($digits) < 3) return [null, null];           // skip malformed codes
            return [(int)$digits[0], (int)$digits[2]];              // 1st=year, 3rd=semester
        };

        // 3) Flatten ALL sessions -> rows
        $sessions = $all_course_with_teacher->sessions ?? [];
        $flattenedRows = [];
        foreach ($sessions as $sess) {
            foreach (($sess->courses ?? []) as $row) {
                // each $row has ->courseObject and ->registered_students_count
                $flattenedRows[] = $row;
            }
        }

        // 4) Filter by year/semester pattern, and DEDUPE by course id WHILE KEEPING THE WRAPPER ROW
        $byCourseId = [];
        foreach ($flattenedRows as $row) {
            $course = $row->courseObject ?? null;
            if (!$course) continue;

            [$yr, $sem] = $pickYearSemesterFromCourseNo($course->courseno ?? '');
            if ($yr === $targetYear && $sem === $targetSemester) {
                // Keep the full row so Blade still has courseObject + registered_students_count
                $byCourseId[$course->id] = $row;
            }
        }

        $filteredRows = array_values($byCourseId);

        // 5) Overwrite the payload to match what your Blade expects
        $all_course_with_teacher->courses = $filteredRows;
        //return $all_course_with_teacher->courses;

// (Optional) If you don't need the original sessions block anymore, you can slim it:
// unset($all_course_with_teacher->sessions);

// 6) Count for your Blade
        $number_of_theory_courses = count($filteredRows);


        /* // Count number of theory courses
         $number_of_theory_courses = isset($all_course_with_teacher->courses)
             ? count($all_course_with_teacher->courses)
             : 0;*/

        // return $number_of_theory_courses;

        //no need to call again for class test(class test for theory course)
        // $all_course_with_class_test_teacher=ApiData::getSessionWiseTheoryCourses(sid);
        //all sessional course with teacher
        $all_sessional_course_with_teacher = ApiData::getSessionWiseSessionalCourses($sid);
        //all theory sessional courses
        $all_theory_sessional_courses_with_student_count = ApiData::getSessionWiseTheorySessionalCourses($sid);
        //all student advisor in specific student
        $all_advisor_with_student_count = ApiData::getSessionWiseStudentAdvisor($sid);
        //active head
        $teacher_head = ApiData::getHead();

        // return response()->json(['$all_course_with_teacher'=>$all_course_with_teacher]);
        /*return response()->json(['head'=>$all_course_with_class_test_teacher]);*/
        return view('committee_input.review_form.review_session_form')
            ->with('sid',$sid)
            /*->with('teacher_head', $teacher_head)*/
            /*  ->with('teacher_coordinator', $teacher_coordinator)*/
            ->with('session_info', $session_info)
            ->with('exam_type',$exam_type->id)
            ->with('teachers', $teachers)
            ->with('employees', $employees)
            ->with('teacher_head', $teacher_head)
            ->with('groupedTeachers', $groupedTeachers)
            ->with('all_course_with_teacher', $all_course_with_teacher)
            ->with('number_of_theory_courses', $number_of_theory_courses)
            ->with('all_course_with_class_test_teacher', $all_course_with_teacher)
            ->with('all_sessional_course_with_teacher', $all_sessional_course_with_teacher)
            ->with('all_theory_sessional_courses_with_student_count', $all_theory_sessional_courses_with_student_count)
            ->with('all_advisor_with_student_count', $all_advisor_with_student_count);
    }


    //Examinat
    public function storeExaminationModerationCommittee(Request $request)
    {
        // Log all request data with a custom message
        Log::info('Examination moderation committee called', [
            'request_data' => $request->all()  // Log all input data from the request
        ]);
        $teacherIds = $request->input('moderation_committee_teacher_ids'); // array
        $amounts = $request->input('moderation_committee_amounts');        // array (indexed)
        $sessionId = $request->sid;
        $min_rate=$request->moderation_committee_min_rate;
        $max_rate=$request->moderation_committee_max_rate;
        $exam_type_record=ExamType::where('type','review')->first();
        $exam_type = $exam_type_record->id;


        Log::info('teacherId',$teacherIds);
        Log::info('teacherId',$amounts);
        Log::info('sessionId: ' . $sessionId);

        // Step 1: Validate teacher inputs
        if (empty($teacherIds) || !is_array($teacherIds) || count($teacherIds) !== count($amounts)) {
            return response()->json([
                'message' => 'Invalid data submitted. Please select teachers and their respective student count.'
            ], 422);
        }


        Log::info('pass out1');
        // Step 2: Check for duplicates
        if (count($teacherIds) !== count(array_unique($teacherIds))) {
            return response()->json([
                'message' => 'Duplicate teacher selection detected. Please choose unique teachers.'
            ], 422);
        }


        // ✅ Step 3: Check if each amount is within min and max rate
        foreach ($amounts as $index => $amount) {
            if (!is_numeric($amount)) {
                return response()->json([
                    'message' => "Invalid amount format for teacher at index {$index}."
                ], 422);
            }

            if ($amount < $min_rate || $amount > $max_rate) {
                return response()->json([
                    'message' => "Amount for teacher at position " . ($index + 1) . " must be between {$min_rate} and {$max_rate}."
                ], 422);
            }
        }


        Log::info('pass out2');
        DB::beginTransaction();

        try {
            // Step 3: Ensure RateHead exists
            $rateHead = RateHead::where('order_no', 1)->first();
            Log::info('rateHead', $rateHead ? $rateHead->toArray() : ['rateHead' => null]);
            $rateHead = $this->getOrCreateRateHead(1, [
                'head' => 'Moderation',
                'dist_type' => 'Individual',
                'enable_min' => 1,
                'enable_max' => 1,
                'is_course' => 0,
                'is_student_count' => 0,
                'marge_with' => null,
                'status' => 1,
            ]);

            //ensure session exist
            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);



            // Step 4: Ensure  RateAmount exists(Rate Amount Exist for Rate Head=1)
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session_info->id,
                $exam_type,
                [
                    'default_rate' => 0,
                    'min_rate' => $min_rate,
                    'max_rate' => $max_rate,
                ]
            );

            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            // Step 5: Loop and store teacher-wise rate_assign
            foreach ($teacherIds as $index => $teacherId) {
                $amount = isset($amounts[$index]) ? intval($amounts[$index]) : 0;

                if ($amount <= 0) {
                    //  DB::rollBack();
                    return response()->json([
                        'message' => "Invalid amount for teacher ID: $teacherId."
                    ], 422);
                }

                RateAssign::create([
                    'teacher_id' => $teacherId,
                    'rate_head_id' => $rateHead->id,
                    'session_id' => $session_info->id,
                    'exam_type_id'=>$exam_type,
                    'no_of_items' => 0,
                    'total_amount' => $amount,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Moderation committee data stored successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    //Store Paper Setter , Examiner
    public function storeExaminerPaperSetter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'examiner_rate_per_script' => 'required|numeric|min:1',
            'examiner_min_rate' => 'required|numeric|min:1',
            'paper_setter_rate' => 'required|numeric|min:1',
            'paper_setter_ids' => 'required|array',
            'examiner_ids' => 'required|array',
            'no_of_script' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }


        $paperSetterData = $request->input('paper_setter_ids', []);
        $examinerData = $request->input('examiner_ids', []);
        $noOfScripts = $request->input('no_of_script', []);
        $script_rate=$request->examiner_rate_per_script;
        $examiner_min_rate=$request->examiner_min_rate;
        $paper_setter_rate=$request->paper_setter_rate;
        $sessionId = $request->sid;
        $exam_type_record=ExamType::where('type','review')->first();
        $exam_type = $exam_type_record->id;

        // ✅ Log all data
        Log::info('🔍 Incoming Examiner & Paper Setter Submission', [
            'paper_setter_ids' => $paperSetterData,
            'examiner_ids' => $examinerData,
            'no_of_script' => $noOfScripts,
            'examiner_rate_per_script' => $script_rate,
            'examiner_min_rate' => $examiner_min_rate,
            'paper_setter_rate' => $paper_setter_rate,
            'session_id' => $sessionId,
            'exam_type' => $exam_type,
        ]);

        try {
            DB::beginTransaction();

            // RateHead 2 - Paper Setters
            $rateHead_2 = $this->getOrCreateRateHead(2, [
                'head' => 'Paper Setters',
                'dist_type' => 'Individual',
                'enable_min' => 0,
                'enable_max' => 0,
                'is_course' => 1,
                'is_student_count' => 0,
                'marge_with' => null,
                'status' => 1,
            ]);

            // RateHead 3 - Examiner
            $rateHead_3 = $this->getOrCreateRateHead(3, [
                'head' => 'Examiner',
                'dist_type' => 'Share',
                'enable_min' => 1,
                'enable_max' => 0,
                'is_course' => 1,
                'is_student_count' => 1,
                'marge_with' => null,
                'status' => 1,
            ]);

            // Ensure Session exists
            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

            // RateAmount for RateHead 2 - Paper Setter
            $rateAmount_2 = $this->getOrCreateRateAmount(
                $rateHead_2->id,
                $session_info->id,
                $exam_type,
                [
                    'default_rate' => $paper_setter_rate,
                    'min_rate' => null,
                    'max_rate' => null,
                ]
            );

            // RateAmount for RateHead 3 - Examiner
            $rateAmount_3 = $this->getOrCreateRateAmount(
                $rateHead_3->id,
                $session_info->id,
                $exam_type,
                [
                    'default_rate' => $script_rate,
                    'min_rate' => $examiner_min_rate,
                    'max_rate' => null,
                ]
            );


            // Delete old paper setter entries (rate_head_2)
            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead_2->id)
                ->delete();

            // Delete old examiner entries (rate_head_3)
            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead_3->id)
                ->delete();
            Log::info('Delete Done RateAssign');

            /*"paper_setters":
               {
                    "1": ["110", "120"],
                    "4": ["120", "140"],
                }*/
            //here $courseId is 1,4
            //$teacherIds [110, 120] for 1
            //$teacherIds [120, 140] for 4
            // Store Paper Setters
            foreach ($paperSetterData as $courseId => $teacherIds) {
                //loop for $teacherIds [120, 140] $teacherId=120,$teacherId=140
                $no_of_scripts = $noOfScripts[$courseId] ?? 0;
                $teacherCount = count($teacherIds);
                foreach ($teacherIds as $teacherId) {
                    $rateAssign = new RateAssign();
                    $rateAssign->teacher_id = $teacherId;
                    $rateAssign->rate_head_id = $rateHead_2->id;
                    $rateAssign->session_id = $session_info->id;
                    $rateAssign->no_of_items = 0;
                    $rateAssign->total_amount = $paper_setter_rate;
                    $rateAssign->exam_type_id = $exam_type;




                    // Add hidden course-related data
                    $rateAssign->course_code = $request->input("courseno.$courseId");
                    $rateAssign->course_name = $request->input("coursetitle.$courseId");
                    //$rateAssign->total_students = $request->input("registered_students_count.$courseId");
                    //$rateAssign->total_teachers = $request->input("teacher_count.$courseId");
                    $rateAssign->total_students =$no_of_scripts;
                    $rateAssign->total_teachers = $teacherCount;



                    // ✅ Log before saving
                    Log::info('📄 Saving Paper Setter Assignment', [
                        'course_id' => $courseId,
                        'teacher_id' => $teacherId,
                        'course_code' => $rateAssign->course_code,
                        'course_name' => $rateAssign->course_name,
                        'total_students' => $rateAssign->total_students,
                        'total_teachers' => $rateAssign->total_teachers,
                        'rate_head_id' => $rateAssign->rate_head_id,
                        'session_id' => $rateAssign->session_id,
                        'exam_type_id' => $rateAssign->exam_type_id,
                        'total_amount' => $rateAssign->total_amount,
                    ]);
                    if ($rateAssign->save()) {
                        Log::info('✅ RateAssign saved successfully', $rateAssign->toArray());
                    } else {
                        Log::error('❌ Failed to save RateAssign - unknown error', $rateAssign->toArray());
                    }
                }
            }

            // Store Examiners
            foreach ($examinerData as $courseId => $teacherIds) {
                $total_input_students = $noOfScripts[$courseId] ?? 0;
                $no_of_scripts = $noOfScripts[$courseId] ?? 0;

                $teacherCount = count($teacherIds);//this is used here because in review don't have specific teacher for a course

                //hidden input
                $courseno = $request->input("courseno.$courseId");
                $coursetitle = $request->input("coursetitle.$courseId");
                //$registered_students_count = $request->input("registered_students_count.$courseId");
                // $teacher_count = $request->input("teacher_count.$courseId");


                Log::info('📘 Examiner Course-wise Input Data', [
                    'course_id' => $courseId,
                    'teacher_ids' => $teacherIds,
                    'total_input_students' => $total_input_students,
                    'no_of_scripts' => $no_of_scripts,
                    'teacher_count' => $teacherCount,
                    'course_code' => $courseno,
                    'course_title' => $coursetitle,
                    'registered_students_count' => $no_of_scripts,
                    'hidden_teacher_count' => $teacherCount,
                ]);

                if ($teacherCount > 0) {
                    $no_of_scripts = $no_of_scripts / $teacherCount;
                } else {
                    $no_of_scripts = 0;
                }
                foreach ($teacherIds as $teacherId) {
                    $total_amount = $no_of_scripts * $rateAmount_3->default_rate;
                    if ($total_amount < $rateAmount_3->min_rate) {
                        $total_amount = $rateAmount_3->min_rate;
                    }


                    // ✅ Log before saving
                    Log::info('📄 Saving Examiner Data', [
                        'course_id' => $courseId,
                        'teacher_id' => $teacherId,
                        'course_code' => $courseno,
                        'course_name' => $coursetitle,
                        'total_students' => $total_input_students,
                        'total_teacher' => $teacherCount,
                        'rate_head_id' => $rateHead_3->id,
                        'session_id' => $session_info->id,
                        'exam_type_id' => $exam_type,
                        'total_amount' => $total_amount,
                    ]);


                    //another way for insert
                    RateAssign::create([
                        'teacher_id'   => $teacherId,
                        'rate_head_id' => $rateHead_3->id,
                        'session_id'   => $session_info->id,
                        'no_of_items'  => $no_of_scripts,
                        'total_amount' => $total_amount,
                        'exam_type_id'=>$exam_type,

                        // Add hidden course-related data
                        'course_code'  => $courseno,
                        'course_name'   => $coursetitle,
                        'total_students' => $total_input_students,
                        'total_teachers'  => $teacherCount,
                    ]);
                }
            }

            DB::commit();
            Log::info('✅ All examiner and paper setter data saved successfully.', [
                'session_id' => $session_info->id,
                'rate_heads' => [
                    'paper_setter' => $rateHead_2->id,
                    'examiner' => $rateHead_3->id,
                ]
            ]);


            return response()->json([
                'message' => 'Examiner and Paper Setter data saved successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while saving data.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function storeScrutinizers(Request $request)
    {
        $scrutinizer_teacher_ids = $request->input('scrutinizer_teacher_ids', []);
        $scrutinizers_no_of_students = $request->input('scrutinizers_no_of_students', []);
        $sessionId = $request->input('sid');
        $scrutinize_script_rate = $request->input('scrutinize_script_rate');
        $scrutinize_min_rate = $request->input('scrutinize_min_rate');
        $exam_type_record=ExamType::where('type','review')->first();
        $exam_type = $exam_type_record->id;

        Log::info('📥 Scrutinizer Form Submission Received', [
            'teacher_ids' => $scrutinizer_teacher_ids,
            'no_of_students' => $scrutinizers_no_of_students,
            'session_id' => $sessionId,
            'script_rate' => $scrutinize_script_rate,
            'min_rate' => $scrutinize_min_rate
        ]);

        // ✅ Validate the input
        $validator = Validator::make($request->all(), [
            'scrutinizer_teacher_ids' => 'required|array',
            'scrutinizers_no_of_students' => 'required|array',
            'scrutinize_script_rate' => 'required|numeric|min:1',
            'scrutinize_min_rate' => 'required|numeric|min:0',
            'sid' => 'required'
        ]);

        // Per-course validation
        foreach ($scrutinizer_teacher_ids as $courseId => $teacherIds) {
            if (empty($teacherIds)) {
                $validator->after(function ($validator) use ($courseId) {
                    $validator->errors()->add("scrutinizer_teacher_ids.$courseId", "Select at least one teacher for course ID $courseId.");
                });
            }

            $studentCount = $scrutinizers_no_of_students[$courseId] ?? null;
            if ($studentCount === null || $studentCount === '') {
                $validator->after(function ($validator) use ($courseId) {
                    $validator->errors()->add("scrutinizers_no_of_students.$courseId", "Enter a valid number of students for course ID $courseId.");
                });
            }
        }

        if ($validator->fails()) {
            Log::warning('❌ Scrutinizer form validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            Log::info('🔍 Fetching or creating RateHead for Scrutinizer');
            $rateHead = $this->getOrCreateRateHead(9, [
                'head' => 'Scrutinizing(Answre Script)',
                'dist_type' => 'Share',
                'enable_min' => 1,
                'enable_max' => 0,
                'is_course' => 1,
                'is_student_count' => 1,
                'marge_with' => null,
                'status' => 1,
            ]);


            Log::debug('✅ RateHead confirmed', $rateHead->toArray());

            $session_info = LocalData::getOrCreateReviewSession($sessionId, $exam_type);
            Log::info('✅ Session ensured', ['session_id' => $session_info->id]);

            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session_info->id,
                $exam_type,
                [
                    'default_rate' => $scrutinize_script_rate,
                    'min_rate'     => $scrutinize_min_rate,
                    'max_rate'     => null,
                ]
            );

            Log::debug('✅ RateAmount confirmed', $rateAmount->toArray());


            // Delete old entries (rateAssign)
            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            // Pass 1: Group courses and script assignments by teacher
            $teacherCourseMap = [];

            foreach ($scrutinizer_teacher_ids as $courseId => $teacherIds) {
                $studentCount = (int) $scrutinizers_no_of_students[$courseId];
                $teacherCount = count($teacherIds);

                $courseno = $request->input("courseno.$courseId");
                $coursetitle = $request->input("coursetitle.$courseId");

                Log::info("📌 Processing Course ID: $courseId", [
                    'teacher_count' => $teacherCount,
                    'students' => $studentCount
                ]);

                if ($teacherCount > 0 && $studentCount > 0) {
                    $studentsPerTeacher = $studentCount / $teacherCount;

                    foreach ($teacherIds as $teacherId) {
                        $teacherCourseMap[$teacherId][] = [
                            'course_code'    => $courseno,
                            'course_name'    => $coursetitle,
                            'total_students' => $studentCount,
                            'total_teachers' => $teacherCount,
                            'no_of_items'    => $studentsPerTeacher,
                            'earned_amount'  => $studentsPerTeacher * $rateAmount->default_rate,
                        ];
                    }
                }
            }

            // Pass 2: Calculate total earned per teacher across all assigned courses, enforce minimum, and store
            foreach ($teacherCourseMap as $teacherId => $courses) {
                $totalScripts     = array_sum(array_column($courses, 'no_of_items'));
                $totalEarned      = $totalScripts * (float)$rateAmount->default_rate;
                $finalTotalAmount = max((float)($rateAmount->min_rate ?? 0), (float)$totalEarned);

                $allocatedSum = 0;
                $courseCount  = count($courses);

                foreach ($courses as $index => $c) {
                    if ($finalTotalAmount == $totalEarned || $totalScripts <= 0) {
                        // Earned reached or exceeded minimum: keep natural earned amount
                        $courseAmount = round($c['earned_amount'], 2);
                    } else {
                        // Below minimum: distribute the minimum proportionally across courses
                        if ($index === $courseCount - 1) {
                            // Assign exact remainder to last course to avoid rounding discrepancies
                            $courseAmount = round($finalTotalAmount - $allocatedSum, 2);
                        } else {
                            $courseAmount = round(($c['no_of_items'] / $totalScripts) * $finalTotalAmount, 2);
                            $allocatedSum += $courseAmount;
                        }
                    }

                    RateAssign::create([
                        'teacher_id'     => $teacherId,
                        'rate_head_id'   => $rateHead->id,
                        'session_id'     => $session_info->id,
                        'no_of_items'    => $c['no_of_items'],
                        'total_amount'   => $courseAmount,
                        'course_code'    => $c['course_code'],
                        'course_name'    => $c['course_name'],
                        'total_students' => $c['total_students'],
                        'total_teachers' => $c['total_teachers'],
                        'exam_type_id'   => $exam_type
                    ]);

                    Log::debug("✅ RateAssign created for teacher $teacherId", [
                        'course' => $c['course_code'],
                        'amount' => $courseAmount,
                        'items'  => $c['no_of_items']
                    ]);
                }
            }

            DB::commit();

            Log::info('✅ All scrutinizer data saved successfully.', [
                'session_id' => $session_info->id,
                'rate_head_id' => $rateHead->id
            ]);

            return response()->json([
                'message' => 'Scrutinizers committee saved successfully.',
                'scrutinizer_teacher_ids' => $scrutinizer_teacher_ids,
                'scrutinizers_no_of_students' => $scrutinizers_no_of_students,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ Exception caught during scrutinizer save', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'An error occurred while saving data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function storeReviewTheoryGradeSheet(Request $request)
    {
        $teacherData = $request->input('prepares_theory_grade_sheet_teacher_ids', []);
        $studentData = $request->input('prepares_theory_grade_sheet_no_of_students', []);
        $sessionId=$request->sid;
        $theory_grade_sheet_rate=$request->theory_grade_sheet_rate;
        $exam_type_record=ExamType::where('type','review')->first();
        $exam_type = $exam_type_record->id;


        Log::info('📥 Received Theory Grade Sheet Submission', [
            'session_id' => $sessionId,
            'teacher_data' => $teacherData,
            'student_data' => $studentData,
            'rate' => $theory_grade_sheet_rate
        ]);
        $errors = [];

        // ✅ Step 1: Basic validation
        if (empty($teacherData)) {
            $errors['prepares_theory_grade_sheet_teacher_ids'] = 'You must select at least one teacher.';
        }

        if (empty($studentData)) {
            $errors['prepares_theory_grade_sheet_no_of_students'] = 'You must provide number of students.';
        }


        foreach ($teacherData as $courseId => $teacherIds) {
            if (empty($teacherIds)) {
                $errors["teacher_ids.$courseId"] = "Select at least one teacher for course ID $courseId.";
            }

            $studentCount = $studentData[$courseId] ?? null;
            if ($studentCount === null || $studentCount === '') {
                $errors["no_of_students.$courseId"] = "Enter a valid number of students for course ID $courseId.";
            }
        }


        if (!empty($errors)) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $errors
            ], 422);
        }

        try {
            DB::beginTransaction();

            // ✅ Step 2: RateHead creation
            $rateHead = RateHead::where('order_no', '8.a')->first();

            Log::info('🔍 Fetching or creating RateHead for Grade Sheet');
            $rateHead = $this->getOrCreateRateHead('8.a', [
                'head' => 'Gradesheet Preparation',
                'sub_head' => 'Theoretical',
                'dist_type' => 'Share',
                'enable_min' => 0,
                'enable_max' => 0,
                'is_course' => 1,
                'is_student_count' => 1,
                'marge_with' => null,
                'status' => 1,
            ]);

            Log::debug('✅ RateHead confirmed', $rateHead->toArray());

            // ✅ Step 3: Ensure Session exists
            $session_info = LocalData::getOrCreateRegularSession($sessionId, $exam_type); // adjust as needed
            Log::info('✅ Session ensured', ['session_id' => $session_info->id]);

            // ✅ Step 4: RateAmount
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session_info->id,
                $exam_type,
                [
                    'default_rate' => $theory_grade_sheet_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );


            //RateAssign
            // Delete old entries (rateAssign)
            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            foreach ($teacherData as $courseId => $teacherIds) {
                $studentCount = (int) $studentData[$courseId];
                $teacherCount = count($teacherIds);

                //hidden input
                $courseno = $request->input("courseno.$courseId");
                $coursetitle = $request->input("coursetitle.$courseId");
                $registered_students_count = $request->input("registered_students_count.$courseId");
                $teacher_counts = $request->input("teacher_count.$courseId");

                Log::info("📌 Processing Course ID: $courseId", [
                    'teacher_count' => $teacherCount,
                    'students' => $studentCount
                ]);

                if ($teacherCount > 0) {
                    $studentsPerTeacher = $studentCount / $teacherCount;

                    foreach ($teacherIds as $teacherId) {
                        $calculatedAmount = $studentsPerTeacher * $rateAmount->default_rate;
                        //$total_amount = max($rateAmount->min_rate, $calculatedAmount); // Enforce min

                        RateAssign::create([
                            'teacher_id'   => $teacherId,
                            'rate_head_id' => $rateHead->id,
                            'session_id'   => $session_info->id,
                            'no_of_items'  => $studentsPerTeacher,
                            'total_amount' => $calculatedAmount,
                            //hidden input
                            'course_code'  => $courseno,
                            'course_name'   => $coursetitle,
                            'total_students' => $studentCount,
                            'total_teachers' => $teacherCount,
                            'exam_type_id' => $exam_type
                        ]);
                    }
                }
            }


            DB::commit();

            Log::info('✅ Theory Grade Sheet Rate Assignments saved.', [
                'rate_head_id' => $rateHead->id,
                'session_id' => $session_info->id,
            ]);

            return response()->json([
                'message' => 'Theory Grade Sheet committee saved successfully.',
                'grade_sheet_teacher_ids' => $teacherData,
                'grade_sheet_no_of_students' => $studentData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error saving Theory Grade Sheet data: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while saving Theory Grade Sheet data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function storeReviewScrutinizersTheoryGradeSheet(Request $request)
    {
        $teacherData = $request->input('scrutinizing_theory_grade_sheet_teacher_ids', []);
        $studentData = $request->input('scrutinizing_theory_grade_sheet_no_of_students', []);
        $sessionId = $request->sid;
        $scrutinize_theory_grade_sheet_rate = $request->scrutinize_theory_grade_sheet_rate;
        $exam_type_record=ExamType::where('type','review')->first();
        $exam_type = $exam_type_record->id;


        Log::info('📥 Received Scrutinizing Theory Grade Sheet', [
            'session_id' => $sessionId,
            'teacher_data' => $teacherData,
            'student_data' => $studentData,
            'rate' => $scrutinize_theory_grade_sheet_rate
        ]);

        $errors = [];

        // Step 1: Validation
        if (empty($teacherData)) {
            $errors['scrutinizing_theory_grade_sheet_teacher_ids'] = 'You must select at least one teacher.';
        }

        if (empty($studentData)) {
            $errors['scrutinizing_theory_grade_sheet_no_of_students'] = 'You must provide the number of students.';
        }

        foreach ($teacherData as $courseId => $teacherIds) {
            if (empty($teacherIds)) {
                $errors["teacher_ids.$courseId"] = "Select at least one teacher for course ID $courseId.";
            }

            $studentCount = $studentData[$courseId] ?? null;
            if ($studentCount === null || $studentCount === '') {
                $errors["no_of_students.$courseId"] = "Enter a valid number of students for course ID $courseId.";
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $errors
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Step 2: Create or fetch RateHead
            // Step 2: Create or fetch RateHead
            $rateHead = $this->getOrCreateRateHead('10.a', [
                'head' => 'Gradesheet Scrutinizing',
                'sub_head' => 'Theoretical',
                'dist_type' => 'Share',
                'enable_min' => 0,
                'enable_max' => 0,
                'is_course' => 1,
                'is_student_count' => 1,
                'marge_with' => null,
                'status' => 1,
            ]);

            Log::debug('✅ RateHead confirmed', $rateHead->toArray());
            // Step 3: Ensure session
            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);
            Log::info('✅ Session ensured', ['session_id' => $session_info->id]);


            // Step 4: Create or fetch RateAmount
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session_info->id,
                $exam_type,
                [
                    'default_rate' => $scrutinize_theory_grade_sheet_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );



            //RateAssign
            // Delete old entries (rateAssign)
            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();


            // Step 5: RateAssign per teacher
            foreach ($teacherData as $courseId => $teacherIds) {
                $studentCount = (int) $studentData[$courseId];
                $teacherCount = count($teacherIds);

                if ($teacherCount > 0) {
                    $studentsPerTeacher = $studentCount / $teacherCount;

                    foreach ($teacherIds as $teacherId) {
                        $calculatedAmount = $studentsPerTeacher * $rateAmount->default_rate;
                        //$totalAmount = max($rateAmount->min_rate ?? 0, $calculatedAmount); // Enforce min

                        Log::info('📘 Preparation Of Grade Sheet Sessional Store', [
                            'teacher_id'   => $teacherId,
                            'rate_head_id' => $rateHead->id,
                            'session_id'   => $session_info->id,
                            'no_of_items'  => $studentsPerTeacher,
                            'total_amount' => $calculatedAmount,


                            'course_code'    => $request->input("courseno.$courseId"),
                            'course_name'    => $request->input("coursetitle.$courseId"),
                            'total_students' => $studentCount,
                            'total_teachers'  => $teacherCount,
                            'exam_type_id' => $exam_type
                        ]);

                        $rateAssign = new RateAssign();
                        $rateAssign->teacher_id = $teacherId;
                        $rateAssign->rate_head_id = $rateHead->id;
                        $rateAssign->session_id = $session_info->id;
                        $rateAssign->no_of_items = $studentsPerTeacher;
                        $rateAssign->total_amount = $calculatedAmount;

                        // Add hidden course-related data
                        $rateAssign->course_code = $request->input("courseno.$courseId");
                        $rateAssign->course_name = $request->input("coursetitle.$courseId");
                        $rateAssign->total_students = $studentCount;
                        $rateAssign->total_teachers =  $teacherCount;
                        $rateAssign->exam_type_id=$exam_type;
                        $rateAssign->save();
                    }
                }
            }

            DB::commit();

            Log::info('✅ Scrutinizer (Theory) Rate Assignments saved.', [
                'rate_head_id' => $rateHead->id,
                'session_id' => $session_info->id,
            ]);

            return response()->json([
                'message' => 'Scrutinizer (Theory) Grade Sheet committee saved successfully.',
                'teacher_ids' => $teacherData,
                'student_counts' => $studentData,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error saving Scrutinizer (Theory) Grade Sheet: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error occurred while saving data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //order=8.d
    public function storePreparedComputerizedResult(Request $request)
    {
        $teacherData = $request->input('prepared_computerized_result_teacher_ids', []);
        $studentData = $request->input('prepared_computerized_result_no_of_students', []);
        $sessionId = $request->sid;
        $prepare_computerized_result_rate = $request->input('prepare_computerized_result_rate');
        $exam_type_record = ExamType::where('type', 'review')->first();
        $exam_type = $exam_type_record->id;

        Log::info('📥 Received Prepared Computerized Result Data (Review)', [
            'session_id' => $sessionId,
            'teacher_data' => $teacherData,
            'student_data' => $studentData,
            'rate' => $prepare_computerized_result_rate
        ]);

        $errors = [];

        // Step 1: Validate teacher and student input
        if (empty($teacherData)) {
            $errors['prepared_computerized_result_teacher_ids'] = 'You must select at least one teacher.';
        }

        if (empty($studentData)) {
            $errors['prepared_computerized_result_no_of_students'] = 'You must provide the number of students.';
        }

        foreach ($teacherData as $courseId => $teacherIds) {
            if (empty($teacherIds)) {
                $errors["teacher_ids.$courseId"] = "Select at least one teacher for course ID $courseId.";
            }

            $studentCount = $studentData[$courseId] ?? null;
            if ($studentCount === null || $studentCount === '' || !is_numeric($studentCount) || $studentCount < 0) {
                $errors["no_of_students.$courseId"] = "Enter a valid number of students for course ID $courseId.";
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $errors
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Step 2: Create or fetch RateHead
            $rateHead = $this->getOrCreateRateHead('8.d', [
                'head' => 'Prepared Computerized Result',
                'dist_type' => 'Share',
                'enable_min' => 0,
                'enable_max' => 0,
                'is_course' => 1,
                'is_student_count' => 1,
                'marge_with' => null,
                'status' => 1,
            ]);

            Log::info('✅ RateHead confirmed', $rateHead->toArray());

            // Step 3: Get or create review session
            $session_info = LocalData::getOrCreateReviewSession($sessionId, $exam_type);

            // Step 4: Create or fetch RateAmount
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session_info->id,
                $exam_type,
                [
                    'default_rate' => $prepare_computerized_result_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );

            // Delete old entries (rateAssign)
            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            // Step 5: Assign to teachers
            foreach ($teacherData as $courseId => $teacherIds) {
                $studentCount = (int) $studentData[$courseId];
                $teacherCount = count($teacherIds);

                if ($teacherCount > 0 && $studentCount >= 0) {
                    $studentsPerTeacher = $studentCount / $teacherCount;

                    foreach ($teacherIds as $teacherId) {
                        $calculatedAmount = $studentsPerTeacher * $rateAmount->default_rate;

                        Log::info('📘 Prepared Computerized Result Store (Review)', [
                            'teacher_id'   => $teacherId,
                            'rate_head_id' => $rateHead->id,
                            'session_id'   => $session_info->id,
                            'no_of_items'  => $studentsPerTeacher,
                            'total_amount' => $calculatedAmount,
                            'course_code'    => $request->input("courseno.$courseId"),
                            'course_name'    => $request->input("coursetitle.$courseId"),
                            'total_students' => $studentCount,
                            'total_teachers'  => $teacherCount,
                            'exam_type_id' => $exam_type
                        ]);

                        $rateAssign = new RateAssign();
                        $rateAssign->teacher_id = $teacherId;
                        $rateAssign->rate_head_id = $rateHead->id;
                        $rateAssign->session_id = $session_info->id;
                        $rateAssign->no_of_items = $studentsPerTeacher;
                        $rateAssign->total_amount = $calculatedAmount;

                        // Add hidden course-related data
                        $rateAssign->course_code = $request->input("courseno.$courseId");
                        $rateAssign->course_name = $request->input("coursetitle.$courseId");
                        $rateAssign->total_students = $studentCount;
                        $rateAssign->total_teachers = $teacherCount;
                        $rateAssign->exam_type_id = $exam_type;
                        $rateAssign->save();
                    }
                }
            }

            DB::commit();

            Log::info('✅ Prepared Computerized Result Assignments saved (Review).', [
                'rate_head_id' => $rateHead->id,
                'session_id' => $session_info->id,
            ]);

            return response()->json([
                'message' => 'Prepared Computerized Result saved successfully.',
                'teacher_ids' => $teacherData,
                'student_counts' => $studentData,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error saving Prepared Computerized Result (Review): ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while saving data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //order=8.c
    public function storeVerifiedComputerizedGradeSheet(Request $request)
    {
        $teacherIds = $request->input('verified_computerized_result_teachers', []);
        $totalStudents = (int) $request->input('verified_computerized_result_total_students');
        $sessionId = $request->sid;
        $verified_computerized_grade_sheet_rate = $request->verified_computerized_grade_sheet_rate;
        $exam_type_record = ExamType::where('type', 'review')->first();
        $exam_type = $exam_type_record->id;

        Log::info('📥 Received Verified Computerized Result Data (Review)', [
            'session_id' => $sessionId,
            'teacher_ids' => $teacherIds,
            'total_students' => $totalStudents,
            'rate' => $verified_computerized_grade_sheet_rate
        ]);

        $errors = [];

        // Validation
        if (empty($teacherIds)) {
            $errors['verified_computerized_result_teachers'] = 'Select at least one teacher.';
        }

        if (!$totalStudents || $totalStudents < 1) {
            $errors['verified_computerized_result_total_students'] = 'Enter a valid number of students.';
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $errors
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Step 1: RateHead
            $rateHead = $this->getOrCreateRateHead('8.c', [
                'head' => 'Grade Sheeets/GPA Verification',
                'dist_type' => 'Share',
                'enable_min' => 0,
                'enable_max' => 0,
                'is_course' => 0,
                'is_student_count' => 1,
                'marge_with' => null,
                'status' => 1,
            ]);

            Log::info('✅ RateHead created or updated (Review).', $rateHead->toArray());

            // Step 2: Get or create review session
            $session_info = LocalData::getOrCreateReviewSession($sessionId, $exam_type);

            // Step 3: RateAmount
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session_info->id,
                $exam_type,
                [
                    'default_rate' => $verified_computerized_grade_sheet_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );

            // RateAssign: Delete old entries
            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            // Step 4: Assign to teachers
            $total_teacher = count($teacherIds);
            $studentsPerTeacher = $total_teacher > 0 ? ($totalStudents / $total_teacher) : 0;

            foreach ($teacherIds as $teacherId) {
                $calculatedAmount = $studentsPerTeacher * $rateAmount->default_rate;

                Log::info('📘 Verified Computerized Result Store (Review)', [
                    'teacher_id'   => $teacherId,
                    'rate_head_id' => $rateHead->id,
                    'session_id'   => $session_info->id,
                    'no_of_items'  => $studentsPerTeacher,
                    'total_amount' => $calculatedAmount,
                    'total_students' => $totalStudents,
                    'total_teachers'  => $total_teacher,
                    'exam_type_id' => $exam_type
                ]);
                $rateAssign = new RateAssign();
                $rateAssign->teacher_id = $teacherId;
                $rateAssign->rate_head_id = $rateHead->id;
                $rateAssign->session_id = $session_info->id;
                $rateAssign->no_of_items = $studentsPerTeacher;
                $rateAssign->total_amount = $calculatedAmount;
                $rateAssign->exam_type_id = $exam_type;
                $rateAssign->total_students = $totalStudents;
                $rateAssign->total_teachers = $total_teacher;
                $rateAssign->save();
            }

            DB::commit();

            Log::info('✅ Verified Computerized Result Assignments saved (Review).', [
                'rate_head_id' => $rateHead->id,
                'session_id' => $session_info->id,
            ]);

            return response()->json([
                'message' => 'Verified Computerized Result saved successfully.',
                'teacher_ids' => $teacherIds,
                'total_students' => $totalStudents
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error saving Verified Computerized Result (Review): ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while saving data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //order=8.e
    public function storeTabulation(Request $request)
    {
        $teacherIds = $request->input('tabulation_teachers', []);
        $totalStudents = (float) $request->input('tabulation_total_students', 0);
        $sessionId = $request->sid;
        $tabulation_rate = $request->tabulation_rate ?? 90;
        $exam_type_record = ExamType::where('type', 'review')->first();
        $exam_type = $exam_type_record->id;

        Log::info('📥 Received Tabulation Data (Review)', [
            'session_id' => $sessionId,
            'teacher_ids' => $teacherIds,
            'total_students' => $totalStudents,
            'rate' => $tabulation_rate
        ]);

        $errors = [];

        // Validation
        if (empty($teacherIds)) {
            $errors['tabulation_teachers'] = 'Select at least one teacher.';
        }

        if ($totalStudents === null || $totalStudents === '' || !is_numeric($totalStudents) || $totalStudents < 0) {
            $errors['tabulation_total_students'] = 'Enter a valid number of students.';
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $errors
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Step 1: RateHead (8.e)
            $rateHead = RateHead::where('order_no', '8.e')->first();
            if (!$rateHead) {
                $rateHead = $this->getOrCreateRateHead('8.e', [
                    'head' => 'Tabulation',
                    'sub_head' => null,
                    'dist_type' => 'share',
                    'enable_min' => 0,
                    'enable_max' => 0,
                    'is_course' => 0,
                    'is_student_count' => 0,
                    'marge_with' => null,
                    'status' => 1,
                ]);
            }

            Log::info('✅ RateHead for 8.e confirmed (Review).', $rateHead->toArray());

            // Step 2: Get or create review session
            $session_info = LocalData::getOrCreateReviewSession($sessionId, $exam_type);

            // Step 3: RateAmount
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session_info->id,
                $exam_type,
                [
                    'default_rate' => $tabulation_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );

            // RateAssign: Delete old entries
            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            // Step 4: Assign to teachers
            $total_teacher = count($teacherIds);
            $studentsPerTeacher = $total_teacher > 0 ? ($totalStudents / $total_teacher) : 0;

            foreach ($teacherIds as $teacherId) {
                $calculatedAmount = $studentsPerTeacher * $rateAmount->default_rate;

                Log::info('📘 Tabulation RateAssign Store (Review)', [
                    'teacher_id'   => $teacherId,
                    'rate_head_id' => $rateHead->id,
                    'session_id'   => $session_info->id,
                    'no_of_items'  => $studentsPerTeacher,
                    'total_amount' => $calculatedAmount,
                    'total_students' => $totalStudents,
                    'total_teachers' => $total_teacher,
                    'exam_type_id' => $exam_type
                ]);

                $rateAssign = new RateAssign();
                $rateAssign->teacher_id = $teacherId;
                $rateAssign->rate_head_id = $rateHead->id;
                $rateAssign->session_id = $session_info->id;
                $rateAssign->no_of_items = $studentsPerTeacher;
                $rateAssign->total_amount = $calculatedAmount;
                $rateAssign->exam_type_id = $exam_type;
                $rateAssign->total_students = $totalStudents;
                $rateAssign->total_teachers = $total_teacher;
                $rateAssign->save();
            }

            DB::commit();

            Log::info('✅ Tabulation Assignments saved (Review).', [
                'rate_head_id' => $rateHead->id,
                'session_id' => $session_info->id,
            ]);

            return response()->json([
                'message' => 'Tabulation committee saved successfully.',
                'teacher_ids' => $teacherIds,
                'total_students' => $totalStudents
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error saving Tabulation (Review): ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while saving data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //order=12.a
    public function storeStencilCuttingCommittee(Request $request)
    {
        Log::info('📥 Stencil Cutting Request', ['data' => $request->all()]);

        $teacherGroups   = (array) $request->input('stencil_cutting_committee_teacher_ids', []);
        $stencilCounts   = (array) $request->input('stencil_cutting_committee_amounts', []);
        $sessionId       = (int) $request->sid;
        $ratePerStencil  = (float) $request->stencil_cutting_question_paper_rate;

        $examType = ExamType::where('type', 'review')->value('id');

        // ---- Validation ----
        /*  $request->validate([
              'sid' => ['required', 'integer'],
              'stencil_cutting_question_paper_rate' => ['required', 'numeric', 'gt:0'],
              'stencil_cutting_committee_teacher_ids'   => ['required', 'array', 'min:1'],
              'stencil_cutting_committee_teacher_ids.*' => ['array', 'min:1'],
              'stencil_cutting_committee_amounts'       => ['required', 'array', 'min:1'],
              'stencil_cutting_committee_amounts.*'     => ['required', 'numeric', 'gt:0'],
          ]);*/

        DB::beginTransaction();
        try {
            $rateHead = $this->getOrCreateRateHead('12.a', [
                'head'             => 'Question',
                'sub_head'         => 'Stencil Cutting',
                'is_course'        => 1,
                'dist_type'        => 'Share',
                'is_student_count' => 1,
                'marge_with'       => null,
                'status'           => 1,
            ]);

            $session = LocalData::getOrCreateRegularSession($sessionId, $examType);

            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session->id,
                $examType,
                [
                    'default_rate' => $ratePerStencil,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );

            RateAssign::where('session_id', $session->id)
                ->where('exam_type_id', $examType)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            foreach ($teacherGroups as $groupNo => $teacherIds) {
                $teacherIds   = array_values(array_unique(array_filter((array)$teacherIds)));
                $stencils     = (float) ($stencilCounts[$groupNo] ?? 0);
                $teacherCount = count($teacherIds);

                if ($stencils <= 0 || $teacherCount <= 0) {
                    continue;
                }

                $perTeacherItems  = $stencils / $teacherCount;
                $perTeacherAmount = $perTeacherItems * (float) $rateAmount->default_rate;

                foreach ($teacherIds as $teacherId) {
                    Log::info('📘 Stencil Cutting Store', [
                        'teacher_id'      => (int) $teacherId,
                        'rate_head_id'    => $rateHead->id,
                        'session_id'      => $session->id,
                        'exam_type_id'    => $examType,
                        'group_no'        => (int) $groupNo,
                        'total_students'  => $stencils,
                        'total_teachers'  => $teacherCount,
                        'no_of_items'     => $perTeacherItems,
                        'total_amount'    => $perTeacherAmount,
                    ]);

                    RateAssign::create([
                        'teacher_id'      => (int) $teacherId,
                        'rate_head_id'    => $rateHead->id,
                        'session_id'      => $session->id,
                        'exam_type_id'    => $examType,
                        'group_no'        => (int) $groupNo,
                        'total_students'  => $stencils,
                        'total_teachers'  => $teacherCount,
                        'no_of_items'     => $perTeacherItems,
                        'total_amount'    => $perTeacherAmount,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Stencil cutting committee saved successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('❌ Stencil cutting save error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Something went wrong.', 'error' => $e->getMessage()], 500);
        }
    }



    //order 12.b

    public function storePrintingQuestion(Request $request)
    {
        Log::info('📥 Printing Question Request', ['data' => $request->all()]);

        // Grouped inputs from the multi-select Blade
        $teacherGroups  = (array) $request->input('print_question_committee_teacher_ids', []); // [row => [ids...]]
        $stencilCounts  = (array) $request->input('printing_question_committee_amounts', []);  // [row => count]
        $sessionId      =  $request->sid;
        $ratePerStencil = (float) $request->printing_question_paper_rate;

        $examType = ExamType::where('type', 'review')->value('id');

        // ---- Validation ----
        $request->validate([
            'sid' => ['required', 'integer'],
            'printing_question_paper_rate' => ['required', 'numeric', 'gt:0'],
            'print_question_committee_teacher_ids' => ['required', 'array', 'min:1'],
            'print_question_committee_teacher_ids.*' => ['array', 'min:1'],      // each row must have >=1 selection
            'printing_question_committee_amounts' => ['required', 'array', 'min:1'],
            'printing_question_committee_amounts.*' => ['required', 'numeric', 'gt:0'],
        ]);

        DB::beginTransaction();
        try {
            // ---- RateHead ----
            $rateHead = $this->getOrCreateRateHead('12.b', [
                'head'             => 'Question',
                'sub_head'         => 'Printing',
                'is_course'        => 0,
                'dist_type'        => 'Share',
                'is_student_count' => 1,   // using as "count of stencils"
                'marge_with'       => null,
                'status'           => 1,
            ]);

            // ---- Session ----
            $session = LocalData::getOrCreateRegularSession($sessionId, $examType);

            // ---- RateAmount ----
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session->id,
                $examType,
                [
                    'default_rate' => $ratePerStencil,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );

            // ---- Clear old block ----
            RateAssign::where('session_id', $session->id)
                ->where('exam_type_id', $examType)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            // ---- Save by group_no (row key) ----
            foreach ($teacherGroups as $groupNo => $teacherIds) {
                // sanitize
                /* $teacherIds      = array_values(array_filter((array) $teacherIds, fn($v) => $v !== null && $v !== ''));*/
                $teacherIds   = array_values(array_filter((array)$teacherIds)); // sanitize
                $stencilCount = (float) ($stencilCounts[$groupNo] ?? 0);
                $teacherCount = count($teacherIds);
                if ($stencilCount <= 0 || $teacherCount <= 0) {
                    continue;
                }

                // Equal split: each teacher gets (stencils / total_teachers)
                $stencilsPerTeacher = $stencilCount / $teacherCount;

                foreach ($teacherIds as $teacherId) {
                    $amount = round($stencilsPerTeacher * (float) $rateAmount->default_rate, 2);

                    RateAssign::create([
                        // using employee-based payout here (as in your previous implementation)
                        'employee_id'    => (int)$teacherId,
                        'rate_head_id'   => $rateHead->id,
                        'session_id'     => $session->id,
                        'exam_type_id'   => $examType,
                        'group_no'       => (int) $groupNo,

                        // audit/math fields
                        'total_students'  => $stencilCount,
                        'total_teachers' => $teacherCount,
                        'no_of_items'    => $stencilsPerTeacher,   // per-teacher stencil count after split
                        'total_amount'   => $amount,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Printing question committee saved successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('❌ Printing question save error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Something went wrong.', 'error' => $e->getMessage()], 500);
        }
    }

    //order 11
    public function storeComparisonCommittee(Request $request)
    {
        Log::info('Comparison, Correction Committee (raw request)', ['request_data' => $request->all()]);

        $teacherGroups = (array) $request->input('comparison_question_committee_teacher_ids', []); // [groupNo => [teacherIds...]]
        $questionCounts = (array) $request->input('comparison_question_committee_amounts', []);    // [groupNo => questions]
        $sessionId = $request->sid;
        $rate      = (float) $request->comparison_question_paper_rate;

        $exam_type = ExamType::where('type','Review')->value('id');

        if (empty($teacherGroups) || empty($questionCounts)) {
            return response()->json(['message' => 'Please add at least one row with teacher(s) and question count.'], 422);
        }

        DB::beginTransaction();
        try {
            // 1) RateHead for Order 11
            $rateHead = $this->getOrCreateRateHead('11', [
                'head'             => 'Question Typing,Sketching & Misc.',
                'is_course'        => 1,
                'dist_type'        => 'Individual',
                'is_student_count' => 1,
                'marge_with'       => null,
                'status'           => 1,
            ]);

            // 2) Session
            $session = LocalData::getOrCreateRegularSession($sessionId, $exam_type);

            // 3) RateAmount
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id, $session->id, $exam_type,
                ['default_rate' => $rate, 'min_rate' => null, 'max_rate' => null]
            );

            // Replace existing rows for this block
            RateAssign::where('session_id', $session->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            // 4) Save per teacher, splitting each group equally
            foreach ($teacherGroups as $groupNo => $teacherIds) {
                $teacherIds = array_values(array_filter((array) $teacherIds));
                $groupQuestions = (float) ($questionCounts[$groupNo] ?? 0);
                $teacherCount = max(1, count($teacherIds));

                if ($groupQuestions <= 0 || $teacherCount <= 0) {
                    Log::warning('Skipping invalid group in Order 11', [
                        'group_no' => $groupNo, 'teachers' => $teacherIds, 'questions' => $groupQuestions
                    ]);
                    continue;
                }

                $perTeacherItems = $groupQuestions / $teacherCount;
                foreach ($teacherIds as $teacherId) {
                    $amount = $perTeacherItems * (float) $rateAmount->default_rate;

                    Log::info('Order 11 store', [
                        'group_no'        => (int)$groupNo,
                        'teacher_id'      => (int)$teacherId,
                        'per_items'       => $perTeacherItems,
                        'group_questions' => $groupQuestions,
                        'teacher_count'   => $teacherCount,
                        'rate'            => (float)$rateAmount->default_rate,
                        'amount'          => $amount,
                    ]);

                    RateAssign::create([
                        'teacher_id'     => (int) $teacherId,
                        'rate_head_id'   => $rateHead->id,
                        'session_id'     => $session->id,
                        'exam_type_id'   => $exam_type,
                        'group_no'       => (int) $groupNo,

                        // audit/math fields (reuse same semantics as 7.e)
                        'total_students' => $groupQuestions,   // store group total questions here
                        'total_teachers' => $teacherCount,
                        'no_of_items'    => $perTeacherItems,  // per-teacher share of questions
                        'total_amount'   => $amount,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Question Committee data stored successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('❌ Order 11 save error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Something went wrong.', 'error' => $e->getMessage()], 500);
        }
    }

    public function storeHonorariumChairman(Request $request)
    {
        // If validation passes, extract values
        $teacherId = $request->input('chairman_id');
        $chairman_rate = $request->input('chairman_amount');
        $sessionId=$request->input('sid');
        $exam_type_record=ExamType::where('type','review')->first();
        $exam_type = $exam_type_record->id;


        Log::info('📥 Received Chairman Data', [
            'session_id' => $sessionId,
            'teacher_data' => $teacherId,
            'rate' => $chairman_rate
        ]);

        try {
            // Step 1: Get or create session
            $session = LocalData::getOrCreateRegularSession($sessionId,$exam_type);
            Log::info('📘 Session Info:', $session->toArray());

            DB::beginTransaction();

            // Step 2: Get or create RateHead
            $rateHead =$this->getOrCreateRateHead('15', [
                'head' => 'Chairman Fee',
                'dist_type' => 'Individual',
                'is_course' => 0,
                'is_student_count' => 0,
                'marge_with' => null,
                'status' => 1,
            ]);

            Log::info('✅ RateHead confirmed', $rateHead->toArray());

            // Step 3: Get or create RateAmount
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session->id,
                $exam_type,
                [
                    'default_rate' => $chairman_rate, // Set the chairman rate
                    'min_rate'     => null,           // Optional, can be adjusted if needed
                    'max_rate'     => null,           // Optional, can be adjusted if needed
                ]
            );
            Log::info('✅ RateAmount Confirmed', $rateAmount->toArray());


            RateAssign::where('session_id', $session->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();
            // Step 4: Create RateAssign

            Log::info('📘 Preparation Of RateAssign', [
                'teacher_id' => $teacherId,
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'total_amount' => $chairman_rate,
                'exam_type_id'=>$exam_type,
            ]);
            $rateAssign = RateAssign::create([
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'teacher_id' => $teacherId,
                'total_amount' => $chairman_rate,
                'exam_type_id'=>$exam_type,
            ]);
            Log::info('📝 RateAssign Created:', $rateAssign->toArray());

            DB::commit();

            return response()->json(['message' => 'Chairman Honorarium saved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error Storing Chairman Honorarium:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }

}
