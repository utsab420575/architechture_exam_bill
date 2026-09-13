<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ExamType;
use App\Models\RateAmount;
use App\Models\RateAssign;
use App\Models\RateHead;
use App\Models\Teacher;
use App\Services\ApiData;
use App\Services\LocalData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CommitteeInputReviewController_v3 extends Controller
{
    //showing session list
    public function reviewSessionShow(){
        $sessions=ApiData::getReviewSessions();
        if($sessions === null) {
            return redirect()->back()->with([
                'message' => 'Session Import Failed',
                'alert-type' => 'error',
            ]);
        }
        return view('committee_input.session_view.review_session_list',compact('sessions'));
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
        $session_info = LocalData::getOrCreateRegularSession($sid,$exam_type->id);

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


        //all theory course with teacher
        //all theory course with teacher
        $all_course_with_teacher = ApiData::getSessionWiseTheoryCoursesReview($sid);
        //return $all_course_with_teacher;

        // Count number of theory courses
        $number_of_theory_courses = isset($all_course_with_teacher->courses)
            ? count($all_course_with_teacher->courses)
            : 0;

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
            if ($studentCount === null || $studentCount === '' || $studentCount < 1) {
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

            $session_info = LocalData::getOrCreateRegularSession($sessionId, $exam_type);
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


            foreach ($scrutinizer_teacher_ids as $courseId => $teacherIds) {
                $studentCount = (int) $scrutinizers_no_of_students[$courseId];
                $teacherCount = count($teacherIds);

                $courseno = $request->input("courseno.$courseId");
                $coursetitle = $request->input("coursetitle.$courseId");
                //$registered_students_count = $request->input("registered_students_count.$courseId");
                //$teacher_count = $request->input("teacher_count.$courseId");

                Log::info("📌 Processing Course ID: $courseId", [
                    'teacher_count' => $teacherCount,
                    'students' => $studentCount
                ]);

                if ($teacherCount > 0 && $studentCount > 0) {
                    $studentsPerTeacher = $studentCount / $teacherCount;

                    foreach ($teacherIds as $teacherId) {
                        $calculatedAmount = $studentsPerTeacher * $rateAmount->default_rate;
                        $total_amount = max($rateAmount->min_rate, $calculatedAmount);

                        RateAssign::create([
                            'teacher_id' => $teacherId,
                            'rate_head_id' => $rateHead->id,
                            'session_id' => $session_info->id,
                            'no_of_items' => $studentsPerTeacher,
                            'total_amount' => $total_amount,
                            'course_code' => $courseno,
                            'course_name' => $coursetitle,
                            'total_students' => $studentCount,
                            'total_teachers' => $teacherCount,
                            'exam_type_id' => $exam_type
                        ]);

                        Log::debug("✅ RateAssign created for teacher $teacherId", [
                            'amount' => $total_amount,
                            'items' => $studentsPerTeacher
                        ]);
                    }
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
            if ($studentCount === null || $studentCount === '' || $studentCount < 1) {
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

                if ($teacherCount > 0 && $studentCount > 0) {
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
            if ($studentCount === null || $studentCount === '' || $studentCount < 1) {
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

                if ($teacherCount > 0 && $studentCount > 0) {
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

    public function storeStencilCuttingCommittee(Request $request)
    {
        // Log all request data with a custom message
        Log::info('Stencill Committee', [
            'request_data' => $request->all()  // Log all input data from the request
        ]);
        $teacherIds = $request->input('stencil_cutting_committee_teacher_ids'); // array
        $number_of_stencil = $request->input('stencil_cutting_committee_amounts');        // array (indexed)
        $sessionId = $request->sid;
        $per_stencil_cutting_rate=$request->stencil_cutting_question_paper_rate;
        $exam_type_record=ExamType::where('type','review')->first();
        $exam_type = $exam_type_record->id;



        Log::info('📥 Received Stencill Committee Data', [
            'teacherId' => $teacherIds,
            'number_of_stencil' => $number_of_stencil,
            'rate' => $per_stencil_cutting_rate,
            'sessionId' => $sessionId,
        ]);

        // Step 1: Validate teacher inputs
        if (empty($teacherIds) || !is_array($teacherIds) || count($teacherIds) !== count($number_of_stencil)) {
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




        Log::info('pass out2');
        DB::beginTransaction();



        try {
            // Step 3: Ensure RateHead exists
            $rateHead = $this->getOrCreateRateHead('12.a', [
                'head' => 'Question',
                'sub_head' => 'Stencil Cutting',
                'dist_type' => 'Individual',
                'is_course' => 1,
                'is_student_count' => 1,
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
                    'default_rate' => $per_stencil_cutting_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );


            //RateAssign
            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();


            // Step 5: Loop and store teacher-wise rate_assign
            foreach ($teacherIds as $index => $teacherId) {
                $stencil_count=$number_of_stencil[$index];
                $calculatedAmount =  $stencil_count * $rateAmount->default_rate;

                if ($calculatedAmount <= 0) {
                    //  DB::rollBack();
                    return response()->json([
                        'message' => "Invalid amount for teacher ID: $teacherId."
                    ], 422);
                }

                Log::info('📘 Stencill Cutting Store', [
                    /*'teacher_id' => $teacherId,*/
                    /* $teacherId worked as  employeeId here*/
                    'employee_id' => $teacherId,
                    'rate_head_id' => $rateHead->id,
                    'session_id' => $session_info->id,
                    'exam_type_id'=>$exam_type,
                    'no_of_items' => $stencil_count,
                    'total_amount' => $calculatedAmount,
                ]);

                RateAssign::create([
                    /*'teacher_id' => $teacherId,*/
                    /* $teacherId worked as  employeeId here*/
                    'employee_id' => $teacherId,
                    'rate_head_id' => $rateHead->id,
                    'session_id' => $session_info->id,
                    'exam_type_id'=>$exam_type,
                    'no_of_items' => $stencil_count,
                    'total_amount' => $calculatedAmount,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Stencill cutting data stored successfully.'
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
    public function storePrintingQuestion(Request $request)
    {
        // Log all request data with a custom message
        Log::info('Printing Question', [
            'request_data' => $request->all()  // Log all input data from the request
        ]);
        $teacherIds = $request->input('print_question_committee_teacher_ids'); // array
        $number_of_stencil = $request->input('printing_question_committee_amounts');        // array (indexed)
        $sessionId = $request->sid;
        $per_printing_question_paper_rate=$request->printing_question_paper_rate;
        $exam_type_record=ExamType::where('type','review')->first();
        $exam_type = $exam_type_record->id;



        Log::info('📥 Received Stencill Committee Data', [
            'teacherId' => $teacherIds,
            'number_of_stencil' => $number_of_stencil,
            'rate' => $per_printing_question_paper_rate,
            'sessionId' => $sessionId,
        ]);

        // Step 1: Validate teacher inputs
        if (empty($teacherIds) || !is_array($teacherIds) || count($teacherIds) !== count($number_of_stencil)) {
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




        Log::info('pass out2');
        DB::beginTransaction();
        try {
            // Step 3: Ensure RateHead exists
            $rateHead = $this->getOrCreateRateHead('12.b', [
                'head' => 'Question',
                'sub_head' => 'Printing',
                'is_course' => 0,
                'dist_type' => 'Individual',
                'is_student_count' => 1,
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
                    'default_rate' => $per_printing_question_paper_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );

            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            // Step 5: Loop and store teacher-wise rate_assign
            foreach ($teacherIds as $index => $teacherId) {
                $stencil_count=$number_of_stencil[$index];
                $calculatedAmount =  $stencil_count * $rateAmount->default_rate;

                if ($calculatedAmount <= 0) {
                    //  DB::rollBack();
                    return response()->json([
                        'message' => "Invalid amount for teacher ID: $teacherId."
                    ], 422);
                }

                Log::info('📘 Question Printing Store', [
                    /*'teacher_id' => $teacherId,*/
                    /* $teacherId worked as  employeeId here*/
                    'employee_id' => $teacherId,
                    'rate_head_id' => $rateHead->id,
                    'session_id' => $session_info->id,
                    'exam_type_id'=>$exam_type,
                    'no_of_items' => $stencil_count,
                    'total_amount' => $calculatedAmount,
                ]);

                RateAssign::create([
                    /*'teacher_id' => $teacherId,*/
                    /* $teacherId worked as  employeeId here*/
                    'employee_id' => $teacherId,
                    'rate_head_id' => $rateHead->id,
                    'session_id' => $session_info->id,
                    'exam_type_id'=>$exam_type,
                    'no_of_items' => $stencil_count,
                    'total_amount' => $calculatedAmount,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Stencill cutting data stored successfully.'
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

    public function storeComparisonCommittee(Request $request)
    {
        // Log all request data with a custom message
        Log::info('Comparison,Correction Committee', [
            'request_data' => $request->all()  // Log all input data from the request
        ]);
        $teacherIds = $request->input('comparison_question_committee_teacher_ids'); // array
        $number_of_comparison = $request->input('comparison_question_committee_amounts');        // array (indexed)
        $sessionId = $request->sid;
        $per_comparsion_rate=$request->comparison_question_paper_rate;
        $exam_type_record=ExamType::where('type','review')->first();
        $exam_type = $exam_type_record->id;



        Log::info('📥 Received Question Comparison Committee Data', [
            'teacherId' => $teacherIds,
            'number_of_stencil' => $number_of_comparison,
            'rate' => $per_comparsion_rate,
            'sessionId' => $sessionId,
        ]);

        // Step 1: Validate teacher inputs
        if (empty($teacherIds) || !is_array($teacherIds) || count($teacherIds) !== count($number_of_comparison)) {
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




        Log::info('pass out2');
        DB::beginTransaction();



        try {
            // Step 3: Ensure RateHead exists
            $rateHead = $this->getOrCreateRateHead('11', [
                'head' => 'Question Typing,Sketching & Misc.',
                'is_course' => 1,
                'dist_type' => 'Individual',
                'is_student_count' => 1,
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
                    'default_rate' => $per_comparsion_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );

            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();


            // Step 5: Loop and store teacher-wise rate_assign
            foreach ($teacherIds as $index => $teacherId) {
                $question_comparison_count=$number_of_comparison[$index];
                $calculatedAmount =  $question_comparison_count * $rateAmount->default_rate;

                if ($calculatedAmount <= 0) {
                    //  DB::rollBack();
                    return response()->json([
                        'message' => "Invalid amount for teacher ID: $teacherId."
                    ], 422);
                }

                Log::info('📘 Question Comparison Store', [
                    'teacher_id' => $teacherId,
                    'rate_head_id' => $rateHead->id,
                    'session_id' => $session_info->id,
                    'exam_type_id'=>$exam_type,
                    'no_of_items' => $question_comparison_count,
                    'total_amount' => $calculatedAmount,
                ]);

                RateAssign::create([
                    'teacher_id' => $teacherId,
                    'rate_head_id' => $rateHead->id,
                    'session_id' => $session_info->id,
                    'exam_type_id'=>$exam_type,
                    'no_of_items' => $question_comparison_count,
                    'total_amount' => $calculatedAmount,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Question Committee data stored successfully.'
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
