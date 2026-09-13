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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CommitteeInputController_v3 extends Controller
{
    //regular session list
    public function regularSessionShow(){
        $sessions=ApiData::getRegularSessions();
        if($sessions === null) {
            return redirect()->back()->with([
                'message' => 'Session Import Failed',
                'alert-type' => 'error',
            ]);
        }
        return view('committee_input.session_view.regular_session_list',compact('sessions'));
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


    public function regularSessionForm(Request $request)
    {

        //this session id got from session list blade
        $sid=$request->sid;
        //return $sid;
        //$session_info=ApiData::getSessionInfo($sid);

        //create session record
        $exam_type = ExamType::where('type', 'regular')->first();
        $session_info = LocalData::getOrCreateRegularSession($sid,$exam_type->id);

        //return $session_info;
        //filter by department
        $order = ['Arch', 'CE', 'ChE', 'Chem','CSE','EEE','FE','HSS','IPE','Math','ME','MME','Phy','TE']; // Custom order of departments

        //this is for selection box with search
        $teachers = Teacher::with('user', 'designation', 'department')
            ->whereHas('department', function ($query) use ($order) {
                $query->whereIn('shortname', $order);
            })
            ->join('departments', 'teachers.department_id', '=', 'departments.id')
            ->orderByRaw("FIELD(departments.shortname, '" . implode("','", $order) . "')")
            ->select('teachers.*') // Select only teacher fields to avoid conflict
            ->get();

        //return $teachers;

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


        //All Employee
        /*$employees = Employee::with('user', 'designation', 'department')
            ->whereHas('department', function ($query) use ($order) {
                $query->whereIn('shortname', $order);
            })
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->orderByRaw("FIELD(departments.shortname, '" . implode("','", $order) . "')")
            ->select('employees.*') // Select only employee fields to avoid conflict
            ->get();*/
        $employees = Employee::with('user', 'designation', 'department')
            ->where('department_id', 2)
            ->orderBy('id') // or any ordering you prefer
            ->get();

        //all theory course with teacher
        $all_course_with_teacher = ApiData::getSessionWiseTheoryCoursesRegular($sid);
        //return $all_course_with_teacher;

        // Count number of theory courses
        $number_of_theory_courses = isset($all_course_with_teacher->courses)
            ? count($all_course_with_teacher->courses)
            : 0;
        //return $number_of_theory_courses;
        //return $number_of_theory_courses;

        //no need to call again for class test(class test for theory course)
        // $all_course_with_class_test_teacher=ApiData::getSessionWiseTheoryCourses(sid);
        //all sessional course with teacher
        $all_sessional_course_with_teacher = ApiData::getSessionWiseSessionalCourses($sid);

        //all theory sessional courses
        $all_theory_sessional_courses_with_student_count = ApiData::getSessionWiseTheorySessionalCourses($sid);
        //return $all_theory_sessional_courses_with_student_count;
        //all student advisor in specific student
        $all_advisor_with_student_count = ApiData::getSessionWiseStudentAdvisor($sid);
        //return $all_advisor_with_student_count;

        //active coordinator(we will
        //
        //
        // give it internal database)
        $teacher_coordinator = ApiData::getCoOrdinator();
        //return $teacher_coordinator->teacher->user->email;

        //active coordinator(we will give it internal database)
        $teacher_head = ApiData::getHead();
        //return $teacher_head->teacher->user->email;

        //total student
        $totalStudentInSession=ApiData::getTotalStudentInSession($sid);

        // return response()->json(['$all_course_with_teacher'=>$all_course_with_teacher]);
        /*return response()->json(['head'=>$all_course_with_class_test_teacher]);*/
        return view('committee_input.regular_form.regular_session_form')
            ->with('sid',$sid)
            ->with('teacher_head', $teacher_head)
           ->with('teacher_coordinator', $teacher_coordinator)
            ->with('session_info', $session_info)
            ->with('exam_type',$exam_type->id)
            ->with('groupedTeachers', $groupedTeachers)
            ->with('teachers', $teachers)
            ->with('employees', $employees)
            ->with('all_course_with_teacher', $all_course_with_teacher)
            ->with('number_of_theory_courses', $number_of_theory_courses)
            ->with('all_course_with_class_test_teacher', $all_course_with_teacher)
            ->with('all_sessional_course_with_teacher', $all_sessional_course_with_teacher)
            ->with('all_theory_sessional_courses_with_student_count', $all_theory_sessional_courses_with_student_count)
            ->with('all_advisor_with_student_count', $all_advisor_with_student_count)
            ->with('totalStudentInSession', $totalStudentInSession);
    }


    //Examination Moderation Committee order=1
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
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;

        Log::info('teacherId',$teacherIds);
        Log::info('amounts',$amounts);
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

            // Step 4.5: Delete old RateAssign entries for this session, exam, and rate head
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

    //store PaperSetter/Examiner order=2 , 3
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
        $exam_type_record=ExamType::where('type','regular')->first();
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
                   /* $rateAssign->total_students = $request->input("registered_students_count.$courseId");*/
                    $rateAssign->total_students =$no_of_scripts;
                    $rateAssign->total_teachers = $teacherCount;



                    // ✅ Log before saving
                    Log::info('📄 Saving Paper Setter Assignment', [
                        'course_id' => $courseId,
                        'teacher_id' => $teacherId,
                        'course_code' => $rateAssign->course_code,
                        'course_name' => $rateAssign->course_name,
                        'total_students' => $rateAssign->total_students,
                        'total_teacher' => $rateAssign->total_teacher,
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
                //this is for total_students column
                $total_students = $noOfScripts[$courseId] ?? 0;
                //this is for each member item
                $no_of_scripts = $noOfScripts[$courseId] ?? 0;

                $teacherCount = count($teacherIds);

                //hidden input
                $courseno = $request->input("courseno.$courseId");
                $coursetitle = $request->input("coursetitle.$courseId");
               // $registered_students_count = $request->input("registered_students_count.$courseId");
                $teacher_count = $request->input("teacher_count.$courseId");//teacher comes from database

                // Fallback if teacher_count is not provided or invalid
                if (empty($teacher_count)) {
                    $teacher_count = $teacherCount;
                }

                Log::info('📘 Examiner Course-wise Input Data', [
                    'course_id' => $courseId,
                    'teacher_ids' => $teacherIds,
                    'total_input_students' => $no_of_scripts,
                    'no_of_scripts' => $no_of_scripts,
                    'teacher_count' => $teacherCount,
                    'course_code' => $courseno,
                    'course_title' => $coursetitle,
                    'registered_students_count' => $no_of_scripts,
                    'hidden_teacher_count' => $teacher_count,
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
                        'total_students' => $total_students,
                        'total_teacher' => $teacher_count,
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
                        'total_students' => $total_students,
                        'total_teachers'  => $teacher_count,
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

    //order= 4
    //internal class assignment
    public function storeClassTestTeacherStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_test_teachers_ids' => 'required|array',
            'no_of_students_ct' => 'required|array',
            'class_test_rate' => 'required|numeric|min:1',
            'sid' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        $classTestTeacherData = $request->input('class_test_teachers_ids', []);
        $noOfStudents = $request->input('no_of_students_ct', []);
        $sessionId = $request->sid;
        $class_test_rate = $request->class_test_rate;
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;

        // ✅ Log incoming request
        Log::info('🔍 Incoming Class Test Teacher Submission', [
            'class_test_teachers_ids' => $classTestTeacherData,
            'no_of_students_ct' => $noOfStudents,
            'class_test_rate' => $class_test_rate,
            'session_id' => $sessionId,
        ]);

        try {
            DB::beginTransaction();

            $rateHead = $this->getOrCreateRateHead(4, [
                'head' => 'Class Test',
                'dist_type' => 'Share',
                'enable_min' => 0,
                'enable_max' => 0,
                'is_course' => 1,
                'is_student_count' => 1,
                'marge_with' => null,
                'status' => 1,
            ]);

            //$session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);
            $session_info=Session::where('ugr_id',$sessionId)->where('exam_type_id',$exam_type)->first();

            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session_info->id,
                $exam_type,
                [
                    'default_rate' => $class_test_rate,
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



            foreach ($classTestTeacherData as $courseId => $teacherIds) {
                $courseno = $request->input("courseno.$courseId");
                $coursetitle = $request->input("coursetitle.$courseId");
                //this is from input field(not api)
                $input_studentCount = $noOfStudents[$courseId] ?? 0;
                //this teacher number from api
                $teacher_count = $request->input("teacher_count.$courseId");
                //this is from input
                $teacherCount = count($teacherIds);

                $studentCount = $teacherCount > 0 ? $input_studentCount * 2 : 0;

                Log::info('📘 Class Test Course-wise Input Data', [
                    'course_id' => $courseId,
                    'teacher_ids' => $teacherIds,
                    'student_count' => $studentCount,
                    'course_code' => $courseno,
                    'course_title' => $coursetitle,
                    'hidden_teacher_count' => $teacher_count,
                ]);

                foreach ($teacherIds as $teacherId) {
                    $total_amount = $studentCount * $rateAmount->default_rate;

                    Log::info('📄 Saving Class Test RateAssign', [
                        'teacher_id' => $teacherId,
                        'course_id' => $courseId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session_info->id,
                        'exam_type_id' => $exam_type,
                        'total_amount' => $total_amount,
                    ]);

                    RateAssign::create([
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session_info->id,
                        'no_of_items' => $studentCount,
                        'total_amount' => $total_amount,
                        'exam_type_id' => $exam_type,

                        'course_code' => $courseno,
                        'course_name' => $coursetitle,
                        'total_students' => $input_studentCount,
                        'total_teachers' => $teacher_count,
                    ]);
                }
            }

            DB::commit();
            Log::info('✅ Class Test Teacher Data Stored Successfully.', [
                'session_id' => $session_info->id,
                'rate_head_id' => $rateHead->id,
            ]);

            return response()->json([
                'message' => 'Class Test Teacher data saved successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error storing Class Test Teacher data', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'An error occurred while saving data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //order 5
    public function storeSessionalCourseTeacher(Request $request)
    {
        // ✅ Step 1: Validation
        $validator = Validator::make($request->all(), [
            'sessional_course_teacher_ids' => 'required|array',
            'no_of_contact_hour' => 'required|array',
            'total_week' => 'required|numeric|min:1',
            'sid' => 'required',
            'sessional_per_hour_rate' => 'required|numeric|min:1',
            'sessional_examiner_min_rate' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        // ✅ Step 2: Extract input
        $sessionalTeacherData = $request->input('sessional_course_teacher_ids', []);
        $noOfContactHour = $request->input('no_of_contact_hour', []);
        $total_week = $request->total_week;
        $sessionId = $request->sid;
        $sessional_per_hour_rate = $request->sessional_per_hour_rate;
        $sessional_examiner_min_rate = $request->sessional_examiner_min_rate;
        $exam_type = 1;

        // ✅ Log incoming data
        Log::info('🔍 Incoming Sessional Course Submission', [
            'teacher_ids' => $sessionalTeacherData,
            'contact_hours' => $noOfContactHour,
            'total_week' => $total_week,
            'session_id' => $sessionId,
            'per_hour_rate' => $sessional_per_hour_rate,
            'min_rate' => $sessional_examiner_min_rate,
        ]);

        try {
            DB::beginTransaction();

            // ✅ Step 3: Create or fetch RateHead
            $rateHead = $this->getOrCreateRateHead(5, [
                'head' => 'Laboratory/Survey works',
                'dist_type' => 'Share',
                'enable_min' => 1,
                'enable_max' => 0,
                'is_course' => 1,
                'is_student_count' => 0,
                'marge_with' => null,
                'status' => 1,
            ]);

            // ✅ Step 4: Get or create session info
            $session_info = LocalData::getOrCreateRegularSession($sessionId, $exam_type);

            // ✅ Step 5: Get or create RateAmount
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session_info->id,
                $exam_type,
                [
                    'default_rate' => $sessional_per_hour_rate,
                    'min_rate'     => $sessional_examiner_min_rate,
                    'max_rate'     => null,
                    'total_week'   => $total_week
                ]
            );

            //RateAssign
            // Delete old entries (rateAssign)
            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();
            // ✅ Step 6: Save RateAssign per teacher
            foreach ($sessionalTeacherData as $courseId => $teacherIds) {
                // Case 1: Common hour (multi-select)
                if ($request->input("teacher_count.$courseId") == 0) {
                    $contactHour = floatval($request->input("no_of_contact_hour.$courseId"));

                    foreach ($teacherIds as $teacherId) {
                        $totalAmount = $contactHour * $rateAmount->default_rate * $total_week;
                        if ($totalAmount < $rateAmount->min_rate) $totalAmount = $rateAmount->min_rate;

                        Log::info('📘 Sessional Teacher RateAssign From MultiSelect', [
                            'teacher_id' => $teacherId,
                            'course_id' => $courseId,
                            'contact_hour' => $contactHour,
                            /*'total_week' => $total_week,*/
                            'total_amount' => $totalAmount,
                        ]);
                        RateAssign::create([
                            'teacher_id' => $teacherId,
                            'rate_head_id' => $rateHead->id,
                            'session_id' => $session_info->id,
                           /* 'total_week' => $total_week,*/
                            'no_of_items' => $contactHour,
                            'total_amount' => $totalAmount,
                            'exam_type_id' => $exam_type,
                            'course_code' => $request->input("courseno.$courseId"),
                            'course_name' => $request->input("coursetitle.$courseId"),
                            'total_students' => $total_week,
                            'total_teachers' => count($teacherIds),
                        ]);
                    }
                }
                else{
                    $hours = $noOfContactHour[$courseId] ?? [];

                    foreach ($teacherIds as $index => $teacherId) {
                        $contactHour = isset($hours[$index]) ? floatval($hours[$index]) : 0;
                        $totalAmount = $contactHour * $rateAmount->default_rate * $total_week;

                        if ($totalAmount < $rateAmount->min_rate) {
                            $totalAmount = $rateAmount->min_rate;
                        }

                        Log::info('📘 Sessional Teacher RateAssign', [
                            'teacher_id' => $teacherId,
                            'course_id' => $courseId,
                            'contact_hour' => $contactHour,
                            'total_week' => $total_week,
                            'total_amount' => $totalAmount,
                        ]);

                        RateAssign::create([
                            'teacher_id' => $teacherId,
                            'rate_head_id' => $rateHead->id,
                            'session_id' => $session_info->id,
                            'no_of_items' => $contactHour,
                            'total_amount' => $totalAmount,
                            'exam_type_id' => $exam_type,

                            'course_code' => $request->input("courseno.$courseId"),
                            'course_name' => $request->input("coursetitle.$courseId"),
                            'total_students' => $total_week,
                            'total_teachers' => $request->input("teacher_count.$courseId"),
                        ]);
                    }
                }
            }

            DB::commit();
            Log::info('✅ Sessional Course Teacher Data Stored Successfully', [
                'session_id' => $session_info->id,
                'rate_head_id' => $rateHead->id,
            ]);

            return response()->json([
                'message' => 'Sessional Course Teacher data saved successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error storing Sessional Course Teacher data', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'An error occurred while saving data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    //order=9
    public function storeScrutinizers(Request $request)
    {
        $scrutinizer_teacher_ids = $request->input('scrutinizer_teacher_ids', []);
        $scrutinizers_no_of_students = $request->input('scrutinizers_no_of_students', []);
        $sessionId = $request->input('sid');
        $scrutinize_script_rate = $request->input('scrutinize_script_rate');
        $scrutinize_min_rate = $request->input('scrutinize_min_rate');
        $exam_type = 1;

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


            //RateAssign
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

    //order=8.a
    public function storeTheoryGradeSheet(Request $request)
    {
        $teacherData = $request->input('prepares_theory_grade_sheet_teacher_ids', []);
        $studentData = $request->input('prepares_theory_grade_sheet_no_of_students', []);
        $sessionId=$request->sid;
        $theory_grade_sheet_rate=$request->theory_grade_sheet_rate;
        $exam_type_record=ExamType::where('type','regular')->first();
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

    //order=8.b
    public function storeSessionalGradeSheet(Request $request)
    {
        $teacherData = $request->input('prepare_sessional_grade_sheet_teacher_ids', []);
        $studentData = $request->input('prepare_sessional_grade_sheet_no_of_students', []);
        $sessionId   = $request->sid;
        $sessional_grade_sheet_rate   = $request->sessional_grade_sheet_rate;
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;

        Log::info('📥 Received Sessional Grade Sheet Submission', [
            'session_id' => $sessionId,
            'teacher_data' => $teacherData,
            'student_data' => $studentData,
            'rate' => $sessional_grade_sheet_rate,
        ]);

        $errors = [];

        // ✅ Basic validation
        if (empty($teacherData)) {
            $errors['prepare_sessional_grade_sheet_teacher_ids'] = 'You must select at least one teacher.';
        }

        if (empty($studentData)) {
            $errors['prepare_sessional_grade_sheet_no_of_students'] = 'You must provide number of students.';
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

            // ✅ Step 1: Create or fetch RateHead
            $rateHead = $this->getOrCreateRateHead('8.b', [
                'head' => 'Gradesheet Preparation',
                'sub_head' => 'Sessional',
                'dist_type' => 'Share',
                'enable_min' => 0,
                'enable_max' => 0,
                'is_course' => 1,
                'is_student_count' => 1,
                'marge_with' => null,
                'status' => 1,
            ]);

            Log::debug('✅ RateHead confirmed', $rateHead->toArray());

            // ✅ Ensure Session exists
            $session_info = LocalData::getOrCreateRegularSession($sessionId ,$exam_type);

            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session_info->id,
                $exam_type,
                [
                    'default_rate' => $sessional_grade_sheet_rate,
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
            // ✅ Loop through course-wise teacher assignments
            foreach ($teacherData as $courseId => $teacherIds) {
                $studentCount = (int) $studentData[$courseId];
                $teacherCount = count($teacherIds);


                //hidden input
                $courseno = $request->input("courseno.$courseId");
                $coursetitle = $request->input("coursetitle.$courseId");
                $registered_students_count = $request->input("registered_students_count.$courseId");
                $teacher_count = $request->input("teacher_count.$courseId");


                if ($teacherCount > 0 && $studentCount > 0) {
                    $studentsPerTeacher = $studentCount / $teacherCount;

                    foreach ($teacherIds as $teacherId) {
                        $calculatedAmount = $studentsPerTeacher * $rateAmount->default_rate;


                        Log::info('📘 Preparation Of Grade Sheet Sessional Store', [
                            'teacher_id'   => $teacherId,
                            'rate_head_id' => $rateHead->id,
                            'session_id'   => $session_info->id,
                            'no_of_items'  => $studentsPerTeacher,
                            'total_amount' => $calculatedAmount,


                            'course_code'    => $courseno,
                            'course_name'    => $coursetitle,
                            'total_students' => $studentCount,
                            'total_teachers'  => $teacherCount,
                            'exam_type_id' => $exam_type
                        ]);

                        RateAssign::create([
                            'teacher_id'   => $teacherId,
                            'rate_head_id' => $rateHead->id,
                            'session_id'   => $session_info->id,
                            'no_of_items'  => $studentsPerTeacher,
                            'total_amount' => $calculatedAmount,


                            'course_code'    => $courseno,
                            'course_name'    => $coursetitle,
                            'total_students' => $studentCount,
                            'total_teachers'  => $teacherCount,
                            'exam_type_id' => $exam_type
                        ]);
                    }
                }
            }

            DB::commit();

            Log::info('✅ Sessional Grade Sheet Rate Assignments saved.', [
                'rate_head_id' => $rateHead->id,
                'session_id' => $session_info->id,
            ]);

            return response()->json([
                'message' => 'Sessional Grade Sheet committee saved successfully.',
                'grade_sheet_teacher_ids' => $teacherData,
                'grade_sheet_no_of_students' => $studentData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error saving Sessional Grade Sheet data: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while saving Sessional Grade Sheet data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //order=10.a
    public function storeScrutinizersTheoryGradeSheet(Request $request)
    {
        $teacherData = $request->input('scrutinizing_theory_grade_sheet_teacher_ids', []);
        $studentData = $request->input('scrutinizing_theory_grade_sheet_no_of_students', []);
        $sessionId = $request->sid;
        $scrutinize_theory_grade_sheet_rate = $request->scrutinize_theory_grade_sheet_rate;
        $exam_type_record=ExamType::where('type','regular')->first();
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

                        Log::info('📘 Preparation Of Grade Sheet  Store', [
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

    //order=10.b
    public function storeScrutinizersSessionalGradeSheet(Request $request)
    {
        $teacherData = $request->input('scrutinizing_sessional_grade_sheet_teacher_ids', []);
        $studentData = $request->input('scrutinizing_sessional_grade_sheet_no_of_students', []);
        $sessionId = $request->sid;
        $scrutinize_sessional_grade_sheet_rate = $request->scrutinize_sessional_grade_sheet_rate;
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;

        Log::info('📥 Received Scrutinizing Sessional Grade Sheet', [
            'session_id' => $sessionId,
            'teacher_data' => $teacherData,
            'student_data' => $studentData,
            'rate' => $scrutinize_sessional_grade_sheet_rate
        ]);

        $errors = [];

        // Step 1: Validation
        if (empty($teacherData)) {
            $errors['scrutinizing_sessional_grade_sheet_teacher_ids'] = 'You must select at least one teacher.';
        }

        if (empty($studentData)) {
            $errors['scrutinizing_sessional_grade_sheet_no_of_students'] = 'You must provide number of students.';
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
            $rateHead = $this->getOrCreateRateHead('10.b', [
                'head' => 'GradeSheet Scrutinizing (Sessional)',
                'sub_head' => 'Sessional',
                'dist_type' => 'Share',
                'enable_min' => 0,
                'enable_max' => 0,
                'is_course' => 1,
                'is_student_count' => 1,
                'marge_with' => null,
                'status' => 1,
            ]);


            // Step 3: Ensure session
            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

            // Step 4: Create or fetch RateAmount
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session_info->id,
                $exam_type,
                [
                    'default_rate' => $scrutinize_sessional_grade_sheet_rate,
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
            // Step 5: Assign rates per teacher
            foreach ($teacherData as $courseId => $teacherIds) {
                $studentCount = (int) $studentData[$courseId];
                $teacherCount = count($teacherIds);

                if ($teacherCount > 0 && $studentCount > 0) {
                    $studentsPerTeacher = $studentCount / $teacherCount;

                    foreach ($teacherIds as $teacherId) {
                        $calculatedAmount = $studentsPerTeacher * $rateAmount->default_rate;
                        // $totalAmount = max($rateAmount->min_rate ?? 0, $calculatedAmount); // Enforce min

                        Log::info('📘 Preparation Of Scrutinziing Sessoinal Store', [
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
                        $rateAssign->exam_type_id=$exam_type;
                        $rateAssign->save();
                    }
                }
            }

            DB::commit();

            Log::info('✅ Scrutinziing Sessoinal Rate Assignments saved.', [
                'rate_head_id' => $rateHead->id,
                'session_id' => $session_info->id,
            ]);

            return response()->json([
                'message' => 'Scrutinizer (Sessional) Grade Sheet saved successfully.',
                'teacher_ids' => $teacherData,
                'student_counts' => $studentData,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error saving Scrutinizer (Sessional) Grade Sheet: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while saving data.',
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
        $prepare_computerized_result_rate=$request->input('prepare_computerized_result_rate');
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;

        Log::info('📥 Received Prepared Computerized Result Data', [
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

            // Step 3: Get or create session
            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

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

             //RateAssign
            // Delete old entries (rateAssign)
            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

             //RateAssign
            // Delete old entries (rateAssign)
            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();


            // Step 5: Assign to teachers
            foreach ($teacherData as $courseId => $teacherIds) {
                $studentCount = (int) $studentData[$courseId];
                $teacherCount = count($teacherIds);

                if ($teacherCount > 0 && $studentCount > 0) {
                    $studentsPerTeacher = $studentCount / $teacherCount;

                    foreach ($teacherIds as $teacherId) {
                        $calculatedAmount = $studentsPerTeacher * $rateAmount->default_rate;
                        // $totalAmount = max($rateAmount->min_rate ?? 0, $calculatedAmount);

                        Log::info('📘 Preparation Of Scrutinziing Sessoinal Store', [
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
                        $rateAssign->exam_type_id=$exam_type;
                        $rateAssign->save();
                    }
                }
            }

            DB::commit();

            Log::info('✅ Prepared Computerized Result Assignments saved.', [
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
            Log::error('❌ Error saving Prepared Computerized Result: ' . $e->getMessage());

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
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;

        Log::info('📥 Received Verified Computerized Result Data', [
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


            Log::info('✅ RateHead created or updated.', $rateHead->toArray());

            // Step 2: Get or create session
            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

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


             //RateAssign
            // Delete old entries (rateAssign)
            RateAssign::where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();


            // Step 4: Assign to teachers
            $total_teacher=count($teacherIds);
            $studentsPerTeacher = $totalStudents / count($teacherIds);

            foreach ($teacherIds as $teacherId) {
                $calculatedAmount = $studentsPerTeacher * $rateAmount->default_rate;
                // $totalAmount = max($rateAmount->min_rate ?? 0, $calculatedAmount);


                Log::info('📘 Preparation Of Scrutinziing Sessoinal Store', [
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




                //total student & total teacher
                $rateAssign->total_students = $totalStudents;
                $rateAssign->total_teachers = $total_teacher;

                $rateAssign->save();
            }

            DB::commit();

            Log::info('✅ Verified Computerized Result Assignments saved.', [
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
            Log::error('❌ Error saving Verified Computerized Result: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while saving data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //order=12.a
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
        $exam_type_record=ExamType::where('type','regular')->first();
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
                    'teacher_id' => $teacherId,
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

    //order 12.b
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
        $exam_type_record=ExamType::where('type','regular')->first();
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

                Log::info('📘 Question Comparison Store', [
                    'teacher_id' => $teacherId,
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

    //order=11
    public function storeComparisonCommittee(Request $request)
    {
        // Log all request data with a custom message
        Log::info('Comparison,Correction Committee', [
            'request_data' => $request->all()  // Log all input data from the request
        ]);
        $teacherIds = $request->input('comparison_question_committee_teacher_ids'); // array
        $number_of_correction = $request->input('comparison_question_committee_amounts');        // array (indexed)
        $sessionId = $request->sid;
        $per_comparsion_rate=$request->comparison_question_paper_rate;
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;


        Log::info('📥 Received Question Comparison Committee Data', [
            'teacherId' => $teacherIds,
            'number_of_stencil' => $number_of_correction,
            'rate' => $per_comparsion_rate,
            'sessionId' => $sessionId,
        ]);

        // Step 1: Validate teacher inputs
        if (empty($teacherIds) || !is_array($teacherIds) || count($teacherIds) !== count($number_of_correction)) {
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
                $question_comparison_count=$number_of_correction[$index];
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

    //order=13
    public function storeAdvisorStudent(Request $request)
    {
        $teacherIds = $request->input('advisorTeacherIds', []);
        $studentCounts = $request->input('advisorTotal_students', []);
        $sessionId = $request->sid;
        $advisor_per_student_rate = $request->advisor_per_student_rate;
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;

        Log::info('📥 Received Prepared Advisor Result Data', [
            'session_id' => $sessionId,
            'teacher_data' => $teacherIds,
            'student_data' => $studentCounts,
            'rate' => $advisor_per_student_rate
        ]);


        $errors = [];

        // ✅ Validation
        if (empty($teacherIds) || empty($studentCounts)) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => [
                    'advisorTeacherIds' => 'Teacher IDs are required.',
                    'advisorTotal_students' => 'Student counts are required.',
                ]
            ], 422);
        }

        try {
            DB::beginTransaction();

            // ✅ Step 1: Create or fetch RateHead
            $rateHead = $this->getOrCreateRateHead('13', [
                'head' => 'Advisor Fee',
                'dist_type' => 'Individual',
                'is_course' => 0,
                'is_student_count' => 1,
                'marge_with' => null,
                'status' => 1,
            ]);

            Log::info('✅ RateHead confirmed', $rateHead->toArray());

            // ✅ Step 2: Create or fetch Session
            $session = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

            // ✅ Step 3: Create or fetch RateAmount
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session->id,
                $exam_type,
                [
                    'default_rate' => $advisor_per_student_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );

            RateAssign::where('session_id', $session->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            // ✅ Step 4: Assign teachers
            foreach ($teacherIds as $index => $teacherId) {
                $studentCount = (int) ($studentCounts[$index] ?? 0);

                if ($studentCount > 0) {
                    //$amount = max($rateAmount->min_rate, $studentCount * $rateAmount->default_rate);
                    $amount=$studentCount * $rateAmount->default_rate;


                    Log::info('📘 Preparation Of Advisor Student', [
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $studentCount,
                        'total_amount' => $amount,

                        'total_students'=>$studentCount,
                    ]);


                    $rateAssign = RateAssign::create([
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $studentCount,
                        'total_amount' => $amount,
                        'total_students'=>$studentCount,
                        'exam_type_id'=>$exam_type,
                    ]);

                    Log::info("✅ RateAssign Created for Teacher ID $teacherId", $rateAssign->toArray());
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Advisor-Student assignments saved successfully!',
                'teacher_ids' => $teacherIds,
                'student_counts' => $studentCounts
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error saving Advisor-Student data: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while saving Advisor-Student data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //order=16
    public function storeVerifiedFinalGraduationResult(Request $request)
    {
        $teacherIds = $request->input('verified_grade_teacher_ids', []);
        $no_of_students = $request->input('verified_grade_amounts', []);
        $sessionId = $request->sid;
        $final_result_per_student_rate=$request->final_result_per_student_rate;
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;


        Log::info('📥 Received Prepared Computerized Result Data', [
            'session_id' => $sessionId,
            'teacher_data' => $teacherIds,
            'student_data' => $no_of_students,
            'rate' => $final_result_per_student_rate
        ]);


        if (empty($teacherIds) || empty($no_of_students)) {
            return response()->json([
                'message' => 'Teacher IDs and amounts are required.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Step 1: Get or create RateHead
            $rateHead = $this->getOrCreateRateHead(16, [
                'head' => 'Verified of Final Graduation Result',
                'dist_type' => 'Individual',
                'enable_min' => 0,
                'enable_max' => 0,
                'is_course' => 0,
                'is_student_count' => 1,
                'marge_with' => null,
                'status' => 1,
            ]);

            Log::info('✅ RateHead confirmed', $rateHead->toArray());
            // Step 2: Get or create Session
            $session = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

            // Step 3: Get or create RateAmount
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session->id,
                $exam_type,
                [
                    'default_rate' => $final_result_per_student_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );

            RateAssign::where('session_id', $session->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            // Step 4: Assign each teacher
            foreach ($teacherIds as $index => $teacherId) {
                $studentCount = (int) ($no_of_students[$index] ?? 0);

                if ($studentCount > 0) {
                    //$totalAmount = max($rateAmount->min_rate, $studentCount * $rateAmount->default_rate);
                    $totalAmount=$studentCount * $rateAmount->default_rate;


                    Log::info('📘 Preparation Of Advisor Student', [
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $studentCount,
                        'total_amount' => $totalAmount,

                        'total_students'=>$studentCount,
                    ]);

                    $rateAssign = RateAssign::create([
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $studentCount,
                        'total_amount' => $totalAmount,
                        'total_students'=>$studentCount,
                        'exam_type_id'=>$exam_type,
                    ]);

                    Log::info("✅ RateAssign created for Teacher ID $teacherId", $rateAssign->toArray());
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Verified Final Graduation Result data saved successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error saving Verified Final Graduation Result: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //order=7.e
    public function storeConductedCentralOralExam(Request $request)
    {
        $teacherIds = $request->input('conducted_central_oral_examination_teacher_ids', []);
        $no_of_students = $request->input('conducted_central_oral_examination_student_amounts', []);
        $sessionId = $request->sid;
        $central_examination_thesis_rate = $request->oral_central_exam_thesis_project;
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;

        Log::info('📥 Received Prepared Computerized Result Data', [
            'session_id' => $sessionId,
            'teacher_data' => $teacherIds,
            'student_data' => $no_of_students,
            'rate' => $central_examination_thesis_rate
        ]);

        if (empty($teacherIds) || empty($no_of_students)) {
            return response()->json([
                'message' => 'Teacher IDs and amounts are required.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Step 1: Get or create RateHead
            $rateHead = $this->getOrCreateRateHead('7.e', [
                'head' => 'Sessional',
                'sub_head' => 'Central Viva',
                'dist_type' => 'Individual',
                'enable_min' => 0,
                'enable_max' => 0,
                'is_course' => 0,
                'is_student_count' => 1,
                'marge_with' => null,
                'status' => 1,
            ]);

            Log::info('✅ RateHead confirmed', $rateHead->toArray());

            // Step 2: Get or create Session
            $session = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

            // Step 3: Get or create RateAmount
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session->id,
                $exam_type,
                [
                    'default_rate' => $central_examination_thesis_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );

            RateAssign::where('session_id', $session->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            // Step 4: Assign each teacher
            foreach ($teacherIds as $index => $teacherId) {
                $studentCount = (int) ($no_of_students[$index] ?? 0);

                if ($studentCount > 0) {
                    //$totalAmount = max($rateAmount->min_rate, $studentCount * $rateAmount->default_rate);
                    $totalAmount=$studentCount * $rateAmount->default_rate;

                    Log::info('📘 Preparation Of Advisor Student', [
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $studentCount,
                        'total_amount' => $totalAmount,

                        'total_students'=>$studentCount,
                    ]);
                    $rateAssign = RateAssign::create([
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $studentCount,
                        'total_amount' => $totalAmount,
                        'total_students'=>$studentCount,
                        'exam_type_id'=>$exam_type,
                    ]);

                    Log::info("✅ RateAssign created for Teacher ID $teacherId", $rateAssign->toArray());
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Verified Cetral Oral Examination saved successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error saving Verified Final Graduation Result: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //order=7.f
    public function storeInvolvedSurvey(Request $request)
    {
        $teacherIds = $request->input('involved_survey_teacher_ids'); // array
        $no_of_students = $request->input('involved_survey_student_amounts');        // array (indexed)
        $sessionId = $request->sid;
        $servey_rate = $request->servey_rate;
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;

        Log::info('📥 Received Involved Survey Data', [
            'session_id' => $sessionId,
            'teacher_data' => $teacherIds,
            'student_data' => $no_of_students,
            'rate' => $servey_rate
        ]);

        if (empty($teacherIds) || empty($no_of_students)) {
            return response()->json([
                'message' => 'Teacher IDs and amounts are required.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Step 1: Get or create RateHead
            $rateHead = $this->getOrCreateRateHead('7.f', [
                'head' => 'Sessional',
                'sub_head' => 'Survey',
                'dist_type' => 'Individual',
                'is_course' => 0,
                'is_student_count' => 1,
                'marge_with' => null,
                'status' => 1,
            ]);

            Log::info('✅ RateHead confirmed', $rateHead->toArray());

            // Step 2: Get or create Session
            $session = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

            // Step 3: Get or create RateAmount
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session->id,
                $exam_type,
                [
                    'default_rate' => $servey_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );
            Log::info('✅ RateAmount Confirmed', $rateAmount->toArray());

            RateAssign::where('session_id', $session->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            // Step 4: Assign each teacher
            foreach ($teacherIds as $index => $teacherId) {
                $studentCount = (int) ($no_of_students[$index] ?? 0);

                if ($studentCount > 0) {
                    //$totalAmount = max($rateAmount->min_rate, $studentCount * $rateAmount->default_rate);
                    $totalAmount=$studentCount * $rateAmount->default_rate;

                    Log::info('📘 Preparation Of RateAssign', [
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $studentCount,
                        'total_amount' => $totalAmount,

                        'total_students'=>$studentCount,
                    ]);
                    $rateAssign = RateAssign::create([
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $studentCount,
                        'total_amount' => $totalAmount,
                        'total_students'=>$studentCount,
                        'exam_type_id'=>$exam_type,
                    ]);

                    Log::info("✅ RateAssign created for Teacher ID $teacherId", $rateAssign->toArray());
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Involved Survey saved successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error saving Verified Final Graduation Result: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //order=6.c
    public function storeConductedPreliminaryViva(Request $request)
    {
        $teacherIds = $request->input('conducted_preliminary_viva_teacher_ids'); // array
        $no_of_students = $request->input('conducted_preliminary_viva_student_amounts');        // array (indexed)
        $sessionId = $request->sid;
        $viva_thesis_project_rate = $request->viva_thesis_project_rate;
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;


        Log::info('📥 Received Conducted Priliminary Viva Data', [
            'session_id' => $sessionId,
            'teacher_data' => $teacherIds,
            'student_data' => $no_of_students,
            'rate' => $viva_thesis_project_rate
        ]);

        if (empty($teacherIds) || empty($no_of_students)) {
            return response()->json([
                'message' => 'Teacher IDs and amounts are required.'
            ], 422);
        }

        // Step 1: Get or create Session
        $session = LocalData::getOrCreateRegularSession($sessionId,$exam_type);
        try {
            DB::beginTransaction();

            // Step 2: Get or create RateHead
            $rateHead = $this->getOrCreateRateHead('6.c', [
                'head' => 'Project/Thesis',
                'sub_head' => 'Initial Viva ' . ($session->year . '/' . $session->semester),
                'dist_type' => 'Individual',
                'is_course' => 0,
                'is_student_count' => 1,
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
                    'default_rate' => $viva_thesis_project_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );

            Log::info('✅ RateAmount Confirmed', $rateAmount->toArray());

            RateAssign::where('session_id', $session->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            // Step 4: Assign each teacher
            foreach ($teacherIds as $index => $teacherId) {
                $studentCount = (int) ($no_of_students[$index] ?? 0);

                if ($studentCount > 0) {
                    //$totalAmount = max($rateAmount->min_rate, $studentCount * $rateAmount->default_rate);
                    $totalAmount=$studentCount * $rateAmount->default_rate;

                    Log::info('📘 Preparation Of RateAssign', [
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $studentCount,
                        'total_amount' => $totalAmount,

                        'total_students'=>$studentCount,
                    ]);

                    $rateAssign = RateAssign::create([
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $studentCount,
                        'total_amount' => $totalAmount,
                        'total_students'=>$studentCount,
                        'exam_type_id'=>$exam_type,
                    ]);

                    Log::info("✅ RateAssign created for Teacher ID $teacherId", $rateAssign->toArray());
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Preliminary Viva saved successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error saving Verified Final Graduation Result: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //order=6.a
    public function storeExaminedThesisProject(Request $request)
    {
        $teacherIds = $request->input('examined_thesis_project_teacher_ids', []);
        $internal_no_of_students = $request->input('examined_internal_thesis_project_student_amounts', []);
        $external_no_of_students = $request->input('examined_external_thesis_project_student_amounts', []);
        $sessionId = $request->sid;
        $examined_thesis_project_rate = $request->examined_thesis_project_rate;
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;


        Log::info('📥 Received Examined Thesis Project Data', [
            'session_id' => $sessionId,
            'teacher_data' => $teacherIds,
            'internal_no_of_students' => $internal_no_of_students,
            'external_no_of_students' => $external_no_of_students,
            'rate' => $examined_thesis_project_rate
        ]);


        if (empty($teacherIds)) {
            return response()->json(['message' => 'No teacher data submitted.'], 422);
        }

        try {
            // Step 1: Get or create Session
            $session = LocalData::getOrCreateRegularSession($sessionId,$exam_type);
            Log::info('✅ Session Info Created', $session->toArray());


            DB::beginTransaction();

            // 2. Get or Create RateHead
            $rateHead = $this->getOrCreateRateHead('6.a', [
                'head' => 'Project/Thesis',
                'sub_head' => 'Examination',
                'dist_type' => 'Individual',
                'enable_min' => 0,
                'enable_max' => 0,
                'is_course' => 0,
                'is_student_count' => 1,
                'marge_with' => null,
                'status' => 1,
            ]);
            Log::info('✅ RateHead confirmed', $rateHead->toArray());


            // 3. Get or Create RateAmount
            $rateAmount = $this->getOrCreateRateAmount(
                $rateHead->id,
                $session->id,
                $exam_type,
                [
                    'default_rate' => $examined_thesis_project_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );

            Log::info('✅ RateAmount Confirmed', $rateAmount->toArray());


            RateAssign::where('session_id', $session->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();


            // 4. Create RateAssign for each teacher
            foreach ($teacherIds as $index => $teacherId) {
                $internal = (int) ($internal_no_of_students[$index] ?? 0);
                $external = (int) ($external_no_of_students[$index] ?? 0);
                $totalStudents = $internal + $external;

                if ($totalStudents > 0) {
                    // $totalAmount = max($rateAmount->min_rate, $totalStudents * $rateAmount->default_rate);
                    $totalAmount=$totalStudents*$rateAmount->default_rate;

                    Log::info('📘 Preparation Of RateAssign', [
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $totalStudents,
                        'total_amount' => $totalAmount,
                        'total_students'=>$totalStudents,
                        'exam_type_id'=>$exam_type,
                    ]);

                    //for store internal and external student
                    //we use total_student=internal
                    //we use total_teacher=external
                    //no_of_items store total students(internal+external)
                    $rateAssign = RateAssign::create([
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $totalStudents,
                        'total_amount' => $totalAmount,
                        'total_students'=>$internal,
                        'total_teachers'=>$external,
                        'exam_type_id'=>$exam_type,
                    ]);

                    Log::info("✅ RateAssign created for Teacher ID {$teacherId}", $rateAssign->toArray());
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Examined Thesis/Project data saved successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error storing Examined Thesis/Project: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while saving data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //order=6.d
    public function storeConductedOralExamination(Request $request)
    {
        $teacherIds = $request->input('conducted_oral_examination_teacher_ids'); // array
        $no_of_students = $request->input('conducted_oral_examination_student_amounts');        // array (indexed)
        $sessionId = $request->sid;
        $oral_exam_thesis_project_rate = $request->oral_exam_thesis_project;
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;

        Log::info('📥 Received Conducted Oral Data', [
            'session_id' => $sessionId,
            'teacher_data' => $teacherIds,
            'no_of_students' => $no_of_students,
            'rate' => $oral_exam_thesis_project_rate
        ]);



        if (empty($teacherIds) || empty($no_of_students)) {
            return response()->json([
                'message' => 'Teacher IDs and amounts are required.'
            ], 422);
        }

        try {
            // Step 1: Get or create Session
            $session = LocalData::getOrCreateRegularSession($sessionId,$exam_type);
            Log::info('session info',$session->toArray());

            DB::beginTransaction();

            // Step 2: Get or create RateHead
            $rateHead = $this->getOrCreateRateHead('6.d', [
                'head' => 'Project/Thesis',
                'sub_head' => 'Final Viva ' . $session->year . '/' . $session->semester,
                'dist_type' => 'Individual',
                'enable_min' => 0,
                'enable_max' => 0,
                'is_course' => 0,
                'is_student_count' => 1,
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
                    'default_rate' => $oral_exam_thesis_project_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );
            Log::info('✅ RateAmount Confirmed', $rateAmount->toArray());


            RateAssign::where('session_id', $session->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();
            // Step 4: Assign each teacher
            foreach ($teacherIds as $index => $teacherId) {
                $studentCount = (int) ($no_of_students[$index] ?? 0);

                if ($studentCount > 0) {
                    //$totalAmount = max($rateAmount->min_rate, $studentCount * $rateAmount->default_rate);
                    $totalAmount=$studentCount * $rateAmount->default_rate;

                    Log::info('📘 Preparation Of RateAssign', [
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $studentCount,
                        'total_amount' => $totalAmount,
                        'total_students'=>$studentCount,
                        'exam_type_id'=>$exam_type,
                    ]);

                    $rateAssign = RateAssign::create([
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $studentCount,
                        'total_amount' => $totalAmount,

                        'total_students'=>$studentCount,
                        'exam_type_id'=>$exam_type,
                    ]);

                    Log::info("✅ RateAssign created for Teacher ID $teacherId", $rateAssign->toArray());
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Conducted Oral Exam saved successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error saving Verified Final Graduation Result: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //order=6.b
    public function storeSupervisedThesisProject(Request $request)
    {
        $teacherIds = $request->input('supervised_thesis_project_teacher_ids'); // array
        $no_of_students = $request->input('supervised_thesis_project_student_amounts');        // array (indexed)
        $sessionId = $request->sid;
        $supervised_thesis_project_rate = $request->supervised_thesis_project_rate;
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;

        Log::info('📥 Received Conducted Oral Data', [
            'session_id' => $sessionId,
            'teacher_data' => $teacherIds,
            'no_of_students' => $no_of_students,
            'rate' => $supervised_thesis_project_rate
        ]);

        if (empty($teacherIds) || empty($no_of_students)) {
            return response()->json([
                'message' => 'Teacher IDs and amounts are required.'
            ], 422);
        }

        try {
            // Step 1: Get or create Session
            $session = LocalData::getOrCreateRegularSession($sessionId,$exam_type);
            Log::info('session info',$session->toArray());

            DB::beginTransaction();

            // Step 2: Get or create RateHead
            $rateHead = $this->getOrCreateRateHead('6.b', [
                'head' => 'Project/Thesis',
                'sub_head' => 'Supervising',
                'dist_type' => 'Individual',
                'enable_min' => 0,
                'enable_max' => 0,
                'is_course' => 0,
                'is_student_count' => 1,
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
                    'default_rate' => $supervised_thesis_project_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );


            RateAssign::where('session_id', $session->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();


            Log::info('✅ RateAmount Confirmed', $rateAmount->toArray());

            // Step 4: Assign each teacher
            foreach ($teacherIds as $index => $teacherId) {
                $studentCount = (int) ($no_of_students[$index] ?? 0);

                if ($studentCount > 0) {
                    //$totalAmount = max($rateAmount->min_rate, $studentCount * $rateAmount->default_rate);
                    $totalAmount=$studentCount * $rateAmount->default_rate;

                    Log::info('📘 Preparation Of RateAssign', [
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $studentCount,
                        'total_amount' => $totalAmount,
                        'total_students'=>$studentCount,
                        'exam_type_id'=>$exam_type,
                    ]);

                    $rateAssign = RateAssign::create([
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $studentCount,
                        'total_amount' => $totalAmount,

                        'total_students'=>$studentCount,
                        'exam_type_id'=>$exam_type,
                    ]);

                    Log::info("✅ RateAssign created for Teacher ID $teacherId", $rateAssign->toArray());
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Supervised Thesis/Project saved successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error saving Supervised Thesis/Project: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //order=14
    public function storeHonorariumCoordinator(Request $request)
    {
        // If validation passes, extract values
        $teacherId = $request->input('coordinator_id');
        $coordinator_rate = $request->input('coordinator_amount');
        $sessionId=$request->input('sid');
        $exam_type_record=ExamType::where('type','regular')->first();
        $exam_type = $exam_type_record->id;

        Log::info('📥 Received Coordinator Data', [
            'session_id' => $sessionId,
            'teacher_data' => $teacherId,
            'rate' => $coordinator_rate
        ]);

        try {
            // Step 1: Get or create session
            $session = LocalData::getOrCreateRegularSession($sessionId,$exam_type);
            Log::info('📘 Session Info:', $session->toArray());

            DB::beginTransaction();

            // Step 2: Get or create RateHead
            $rateHead = $this->getOrCreateRateHead('14', [
                'head' => 'Course Co-ordinator Fee',
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
                    'default_rate' => $coordinator_rate,
                    'min_rate'     => null,
                    'max_rate'     => null,
                ]
            );

            Log::info('✅ RateAmount Confirmed', $rateAmount->toArray());

            // Step 4: Create RateAssign

            Log::info('📘 Preparation Of RateAssign', [
                'teacher_id' => $teacherId,
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'total_amount' => $coordinator_rate,
                'exam_type_id'=>$exam_type,
            ]);

            RateAssign::where('session_id', $session->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();

            $rateAssign = RateAssign::create([
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'teacher_id' => $teacherId,
                'total_amount' => $coordinator_rate,
                'exam_type_id'=>$exam_type,
            ]);
            Log::info('📝 RateAssign Created:', $rateAssign->toArray());

            DB::commit();

            return response()->json(['message' => 'Course Co-ordinator Honorarium saved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error Storing Chairman Honorarium:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }

    //order=15
    public function storeHonorariumChairman(Request $request)
    {
        // If validation passes, extract values
        $teacherId = $request->input('chairman_id');
        $chairman_rate = $request->input('chairman_amount');
        $sessionId=$request->input('sid');
        $exam_type_record=ExamType::where('type','regular')->first();
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

            // Step 4: Create RateAssign

            Log::info('📘 Preparation Of RateAssign', [
                'teacher_id' => $teacherId,
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'total_amount' => $chairman_rate,
                'exam_type_id'=>$exam_type,
            ]);

            RateAssign::where('session_id', $session->id)
                ->where('exam_type_id', $exam_type)
                ->where('rate_head_id', $rateHead->id)
                ->delete();


            $rateAssign = RateAssign::create([
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'teacher_id' => $teacherId,
                'total_amount' => $chairman_rate,
                'exam_type_id'=>$exam_type,
            ]);
            Log::info('📝 RateAssign Created:', $rateAssign->toArray());

            DB::commit();

            return response()->json(['message' => 'Course Co-ordinator Honorarium saved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error Storing Chairman Honorarium:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }



}


