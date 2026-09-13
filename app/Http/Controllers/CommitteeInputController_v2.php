<?php

namespace App\Http\Controllers;

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

class CommitteeInputController_v2 extends Controller
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



    public function regularSessionForm(Request $request)
    {

        //this session id got from session list blade
        $sid=$request->sid;
        //return $sid;
        $session_info=ApiData::getSessionInfo($sid);
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
        // Group by department short name
        $groupedTeachers = $teachers->groupBy(function ($teacher) {
            return $teacher->department->fullname ?? 'Unknown';
        });

        // Move 'Arch' to the beginning
        $groupedTeachers = $groupedTeachers->sortBy(function ($group, $key) {
            return $key === 'Architecture' ? 0 : 1;
        });
        //return $groupedTeachers;


        //all theory course with teacher
        $all_course_with_teacher = ApiData::getSessionWiseTheoryCoursesRegular($sid);
        //return $all_course_with_teacher;

        //no need to call again for class test(class test for theory course)
        // $all_course_with_class_test_teacher=ApiData::getSessionWiseTheoryCourses(sid);
        //all sessional course with teacher
        $all_sessional_course_with_teacher = ApiData::getSessionWiseSessionalCourses($sid);

        //all theory sessional courses
        $all_theory_sessional_courses_with_student_count = ApiData::getSessionWiseTheorySessionalCourses($sid);
        //return $all_theory_sessional_courses_with_student_count;
        //all student advisor in specific student
        $all_advisor_with_student_count = ApiData::getSessionWiseStudentAdvisor($sid);

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
            ->with('groupedTeachers', $groupedTeachers)
            ->with('teachers', $teachers)
            ->with('all_course_with_teacher', $all_course_with_teacher)
            ->with('all_course_with_class_test_teacher', $all_course_with_teacher)
            ->with('all_sessional_course_with_teacher', $all_sessional_course_with_teacher)
            ->with('all_theory_sessional_courses_with_student_count', $all_theory_sessional_courses_with_student_count)
            ->with('all_advisor_with_student_count', $all_advisor_with_student_count)
            ->with('totalStudentInSession', $totalStudentInSession);
    }


    //Examination Moderation Committee
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
        $exam_type=1;

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
            // Step 3: Ensure RateHead exists
            $rateHead = RateHead::where('order_no', 1)->first();
            Log::info('rateHead', $rateHead ? $rateHead->toArray() : ['rateHead' => null]);
            if (!$rateHead) {
                $rateHead = new RateHead();
                $rateHead->head = 'Moderation';
                $rateHead->order_no = 1;
                $rateHead->dist_type = 'Individual';
                $rateHead->enable_min = 1;
                $rateHead->enable_max = 1;
                $rateHead->is_course = 0;
                $rateHead->is_student_count = 0;
                $rateHead->marge_with = null;
                $rateHead->status = 1;
                $rateHead->save();
                if ($rateHead->save()) {
                    Log::info('✅ New RateHead created', $rateHead->toArray());
                } else {
                    Log::error('❌ Failed to save RateHead');
                }
            }

            //ensure session exist
            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);


            // Step 4: Ensure  RateAmount exists(Rate Amount Exist for Rate Head=1)
            $rateAmount = RateAmount::where('rate_head_id', $rateHead->id)
                ->where('session_id', $session_info->id)
                ->where('exam_type_id',$exam_type)
                ->first();

            Log::info('rateAmount', $rateAmount ? $rateAmount->toArray() : ['rateAmount' => null]);
            if (!$rateAmount) {
                $rateAmount = new RateAmount();
                $rateAmount->default_rate = 0;
                $rateAmount->min_rate = $min_rate;
                $rateAmount->max_rate = $max_rate;
                $rateAmount->session_id = $session_info->id;
                $rateAmount->rate_head_id = $rateHead->id;
                $rateAmount->exam_type_id = $exam_type;
                $rateAmount->saved = 1;
                if ($rateAmount->save()) {
                    Log::info('✅ New RateAmount created', $rateAmount->toArray());
                } else {
                    Log::error('❌ Failed to save RateHead');
                }
            }

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

    //store PaperSetter/Examiner
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
        $exam_type=1;

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
            $rateHead_2 = RateHead::where('order_no', 2)->first();
            Log::info('rateHead2', $rateHead_2 ? $rateHead_2->toArray() : ['rateHead2' => null]);
            if (!$rateHead_2) {
                $rateHead_2 = new RateHead();
                $rateHead_2->head = 'Paper Setters';
                $rateHead_2->order_no = 2;
                $rateHead_2->dist_type = 'Individual';
                $rateHead_2->enable_min = 0;
                $rateHead_2->enable_max = 0;
                $rateHead_2->is_course = 1;
                $rateHead_2->is_student_count = 0;
                $rateHead_2->marge_with = null;
                $rateHead_2->status = 1;
                if ($rateHead_2->save()) {
                    Log::info('✅ New RateHead created', $rateHead_2->toArray());
                } else {
                    Log::error('❌ Failed to save RateHead');
                }
            }

            // RateHead 3 - Examiner
            $rateHead_3 = RateHead::where('order_no', 3)->first();
            Log::info('rateHead3', $rateHead_2 ? $rateHead_2->toArray() : ['rateHead3' => null]);
            if (!$rateHead_3) {
                $rateHead_3 = new RateHead();
                $rateHead_3->head = 'Examiner';
                $rateHead_3->order_no = 3;
                $rateHead_3->dist_type = 'Share';
                $rateHead_3->enable_min = 1;
                $rateHead_3->enable_max = 0;
                $rateHead_3->is_course = 1;
                $rateHead_3->is_student_count = 1;
                $rateHead_3->marge_with = null;
                $rateHead_3->status = 1;
                if ($rateHead_3->save()) {
                    Log::info('✅ New RateHead3 created', $rateHead_3->toArray());
                } else {
                    Log::error('❌ Failed to save RateHead3');
                }
            }

            // Ensure Session exists
            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

            // RateAmount for RateHead 2
            $rateAmount_2 = RateAmount::where('rate_head_id', $rateHead_2->id)
                ->where('session_id', $session_info->id)
                ->where('exam_type_id',$exam_type)
                ->first();

            Log::info('rateAmount2', $rateAmount_2 ? $rateAmount_2->toArray() : ['rateAmount_2' => null]);
            if (!$rateAmount_2) {
                $rateAmount_2 = new RateAmount();
                $rateAmount_2->default_rate = $paper_setter_rate;
                //null for min_rate , max_rate
                $rateAmount_2->session_id = $session_info->id;
                $rateAmount_2->rate_head_id = $rateHead_2->id;
                $rateAmount_2->exam_type_id = $exam_type;
                $rateAmount_2->saved = 1;
                if ($rateAmount_2->save()) {
                    Log::info('✅ New RateAmount created', $rateAmount_2->toArray());
                } else {
                    Log::error('❌ Failed to save RateAmount');
                }
            }

            // RateAmount for RateHead 3
            $rateAmount_3 = RateAmount::where('rate_head_id', $rateHead_3->id)
                ->where('session_id', $session_info->id)
                ->first();

            if (!$rateAmount_3) {
                $rateAmount_3 = new RateAmount();
                $rateAmount_3->default_rate = $script_rate;
                $rateAmount_3->min_rate = $examiner_min_rate;
                //max rate null(not defined)
                $rateAmount_3->session_id = $session_info->id;
                $rateAmount_3->rate_head_id = $rateHead_3->id;
                $rateAmount_3->exam_type_id = $exam_type;
                $rateAmount_3->saved = 1;
                if ($rateAmount_3->save()) {
                    Log::info('✅ New RateAmount created', $rateAmount_3->toArray());
                } else {
                    Log::error('❌ Failed to save RateHead');
                }
            }


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
                    $rateAssign->total_students = $request->input("registered_students_count.$courseId");
                    $rateAssign->total_teachers = $request->input("teacher_count.$courseId");



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
                $total_input_students = $noOfScripts[$courseId] ?? 0;
                $no_of_scripts = $noOfScripts[$courseId] ?? 0;

                $teacherCount = count($teacherIds);

                //hidden input
                $courseno = $request->input("courseno.$courseId");
                $coursetitle = $request->input("coursetitle.$courseId");
                $registered_students_count = $request->input("registered_students_count.$courseId");
                $teacher_count = $request->input("teacher_count.$courseId");//teacher comes from database

                // Fallback if teacher_count is not provided or invalid
                if (empty($teacher_count)) {
                    $teacher_count = $teacherCount;
                }

                Log::info('📘 Examiner Course-wise Input Data', [
                    'course_id' => $courseId,
                    'teacher_ids' => $teacherIds,
                    'total_input_students' => $total_input_students,
                    'no_of_scripts' => $no_of_scripts,
                    'teacher_count' => $teacherCount,
                    'course_code' => $courseno,
                    'course_title' => $coursetitle,
                    'registered_students_count' => $registered_students_count,
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
                        'total_students' => $total_input_students,
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
                        'total_students' => $total_input_students,
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
        $exam_type = 1;

        // ✅ Log incoming request
        Log::info('🔍 Incoming Class Test Teacher Submission', [
            'class_test_teachers_ids' => $classTestTeacherData,
            'no_of_students_ct' => $noOfStudents,
            'class_test_rate' => $class_test_rate,
            'session_id' => $sessionId,
        ]);

        try {
            DB::beginTransaction();

            $rateHead = RateHead::where('order_no', 4)->first();
            if (!$rateHead) {
                $rateHead = new RateHead();
                $rateHead->head = 'Class Test';
                $rateHead->order_no = 4;
                $rateHead->dist_type = 'Share';
                $rateHead->enable_min = 0;
                $rateHead->enable_max = 0;
                $rateHead->is_course = 1;
                $rateHead->is_student_count = 1;
                $rateHead->marge_with = null;
                $rateHead->status = 1;

                if ($rateHead->save()) {
                    Log::info('✅ New RateHead created', $rateHead->toArray());
                } else {
                    Log::error('❌ Failed to save RateHead');
                }
            }

            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

            $rateAmount = RateAmount::where('rate_head_id', $rateHead->id)
                ->where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->first();

            if (!$rateAmount) {
                $rateAmount = new RateAmount();
                $rateAmount->rate_head_id = $rateHead->id;
                $rateAmount->session_id = $session_info->id;
                $rateAmount->default_rate = $class_test_rate;
                $rateAmount->exam_type_id = $exam_type;
                $rateAmount->saved = 1;

                if ($rateAmount->save()) {
                    Log::info('✅ New RateAmount created', $rateAmount->toArray());
                } else {
                    Log::error('❌ Failed to save RateAmount');
                }
            }

            foreach ($classTestTeacherData as $courseId => $teacherIds) {
                $courseno = $request->input("courseno.$courseId");
                $coursetitle = $request->input("coursetitle.$courseId");
                $input_studentCount = $noOfStudents[$courseId] ?? 0;
                $teacher_count = $request->input("teacher_count.$courseId");
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
            $rateHead = RateHead::where('order_no', 5)->first();
            if (!$rateHead) {
                $rateHead = new RateHead();
                $rateHead->head = 'Laboratory/Survey works';
                $rateHead->order_no = 5;
                $rateHead->dist_type = 'Share';
                $rateHead->enable_min = 1;
                $rateHead->enable_max = 0;
                $rateHead->is_course = 1;
                $rateHead->is_student_count = 0;
                $rateHead->marge_with = null;
                $rateHead->status = 1;

                if ($rateHead->save()) {
                    Log::info('✅ New RateHead created', $rateHead->toArray());
                } else {
                    Log::error('❌ Failed to save RateHead');
                }
            }

            // ✅ Step 4: Get or create session info
            $session_info = LocalData::getOrCreateRegularSession($sessionId, $exam_type);

            // ✅ Step 5: Get or create RateAmount
            $rateAmount = RateAmount::where('rate_head_id', $rateHead->id)
                ->where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->first();

            if (!$rateAmount) {
                $rateAmount = new RateAmount();
                $rateAmount->rate_head_id = $rateHead->id;
                $rateAmount->session_id = $session_info->id;
                $rateAmount->default_rate = $sessional_per_hour_rate;
                $rateAmount->min_rate = $sessional_examiner_min_rate;
                $rateAmount->exam_type_id = $exam_type;
                $rateAmount->saved = 1;

                if ($rateAmount->save()) {
                    Log::info('✅ New RateAmount created', $rateAmount->toArray());
                } else {
                    Log::error('❌ Failed to save RateAmount');
                }
            }

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

            Log::info('🔍 Fetching or creating RateHead for Scrutinizer');
            $rateHead = RateHead::where('order_no', 9)->first();

            if (!$rateHead) {
                $rateHead = new RateHead();
                $rateHead->order_no = 9;
                $rateHead->head = 'Scrutinizing(Answre Script)';
                $rateHead->dist_type = 'Share';
                $rateHead->enable_min = 1;
                $rateHead->enable_max = 0;
                $rateHead->is_course = 1;
                $rateHead->is_student_count = 1;
                $rateHead->marge_with = null;
                $rateHead->status = 1;
                $rateHead->save();
            }


            Log::debug('✅ RateHead confirmed', $rateHead->toArray());

            $session_info = LocalData::getOrCreateRegularSession($sessionId, $exam_type);
            Log::info('✅ Session ensured', ['session_id' => $session_info->id]);

            $rateAmount = RateAmount::where('rate_head_id', $rateHead->id)
                ->where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->first();

            if (!$rateAmount) {
                $rateAmount = new RateAmount();
                $rateAmount->rate_head_id = $rateHead->id;
                $rateAmount->session_id = $session_info->id;
                $rateAmount->exam_type_id = $exam_type;
                $rateAmount->default_rate = $scrutinize_script_rate;
                $rateAmount->min_rate = $scrutinize_min_rate;
                $rateAmount->saved = 1;
                $rateAmount->save();
            }

            Log::debug('✅ RateAmount confirmed', $rateAmount->toArray());

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

    public function storeTheoryGradeSheet(Request $request)
    {
        $teacherData = $request->input('prepares_theory_grade_sheet_teacher_ids', []);
        $studentData = $request->input('prepares_theory_grade_sheet_no_of_students', []);
        $sessionId=$request->sid;
        $theory_grade_sheet_rate=$request->theory_grade_sheet_rate;
        $exam_type=1;

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
            if (!$rateHead) {
                $rateHead = new RateHead();
                $rateHead->order_no = '8.a';
                $rateHead->head = 'Gradesheet Preparation';
                $rateHead->sub_head = 'Theoretical';
                $rateHead->dist_type = 'Share';
                $rateHead->enable_min = 0;
                $rateHead->enable_max = 0;
                $rateHead->is_course = 1;
                $rateHead->is_student_count = 1;
                $rateHead->marge_with = null;
                $rateHead->status = 1;
                $rateHead->save();
                Log::info('✅ New RateHead created:', $rateHead->toArray());
            }

            Log::debug('✅ RateHead confirmed', $rateHead->toArray());

            // ✅ Step 3: Ensure Session exists
            $session_info = LocalData::getOrCreateRegularSession($sessionId, $exam_type); // adjust as needed
            Log::info('✅ Session ensured', ['session_id' => $session_info->id]);

            // ✅ Step 4: RateAmount
            $rateAmount = RateAmount::where('rate_head_id', $rateHead->id)
                ->where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->first();

            if (!$rateAmount) {
                $rateAmount = new RateAmount();
                $rateAmount->rate_head_id = $rateHead->id;
                $rateAmount->session_id = $session_info->id;
                $rateAmount->default_rate = $theory_grade_sheet_rate;  // ₹24 per script (example rate)
                $rateAmount->exam_type_id = $exam_type;
                $rateAmount->saved = 1;
                $rateAmount->save();

                Log::info('✅ RateAmount created (grade sheet):', $rateAmount->toArray());
            }


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

    public function storeSessionalGradeSheet(Request $request)
    {
        $teacherData = $request->input('prepare_sessional_grade_sheet_teacher_ids', []);
        $studentData = $request->input('prepare_sessional_grade_sheet_no_of_students', []);
        $sessionId   = $request->sid;
        $sessional_grade_sheet_rate   = $request->sessional_grade_sheet_rate;
        $exam_type=1;

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
            $rateHead = RateHead::where('order_no', '8.b')->first();
            if (!$rateHead) {
                $rateHead = new RateHead();
                $rateHead->order_no = '8.b';
                $rateHead->head = 'Gradesheet Preparation';
                $rateHead->sub_head = 'Sessional';
                $rateHead->dist_type = 'Share';
                $rateHead->enable_min = 0;
                $rateHead->enable_max = 0;
                $rateHead->is_course = 1;
                $rateHead->is_student_count = 1;
                $rateHead->marge_with = null;
                $rateHead->status = 1;
                $rateHead->save();

                Log::info('✅ New RateHead created:', $rateHead->toArray());
            }

            Log::debug('✅ RateHead confirmed', $rateHead->toArray());

            // ✅ Ensure Session exists
            $session_info = LocalData::getOrCreateRegularSession($sessionId ,$exam_type);

            // ✅ Step 3: Create or fetch RateAmount(need to work)
            $rateAmount = RateAmount::where('rate_head_id', $rateHead->id)
                ->where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->first();

            if (!$rateAmount) {
                $rateAmount = new RateAmount();
                $rateAmount->rate_head_id = $rateHead->id;
                $rateAmount->session_id = $session_info->id;
                $rateAmount->default_rate = $sessional_grade_sheet_rate; // Example rate
                $rateAmount->exam_type_id = $exam_type;
                $rateAmount->saved = 1;
                $rateAmount->save();

                Log::info('✅ New RateAmount created (Sessional):', $rateAmount->toArray());
            }

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

    public function storeScrutinizersTheoryGradeSheet(Request $request)
    {
        $teacherData = $request->input('scrutinizing_theory_grade_sheet_teacher_ids', []);
        $studentData = $request->input('scrutinizing_theory_grade_sheet_no_of_students', []);
        $sessionId = $request->sid;
        $scrutinize_theory_grade_sheet_rate = $request->scrutinize_theory_grade_sheet_rate;
        $exam_type=1;

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
            $rateHead = RateHead::where('order_no', '10.a')->first();
            if (!$rateHead) {
                $rateHead = new RateHead();
                $rateHead->head = 'Gradesheet Scrutinizing';
                $rateHead->order_no = '10.a';
                $rateHead->sub_head = 'Theoretical';
                $rateHead->dist_type = 'Share';
                $rateHead->enable_min = 0;
                $rateHead->enable_max = 0;
                $rateHead->is_course = 1;
                $rateHead->is_student_count = 1;
                $rateHead->marge_with = null;
                $rateHead->status = 1;
                $rateHead->save();

                Log::info('✅ RateHead created or found:', $rateHead->toArray());
            }

            Log::debug('✅ RateHead confirmed', $rateHead->toArray());
            // Step 3: Ensure session
            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);
            Log::info('✅ Session ensured', ['session_id' => $session_info->id]);


            // Step 4: Create or fetch RateAmount
            $rateAmount = RateAmount::where('rate_head_id', $rateHead->id)
                ->where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->first();


            if (!$rateAmount) {
                $rateAmount = new RateAmount();
                $rateAmount->rate_head_id = $rateHead->id;
                $rateAmount->session_id = $session_info->id;
                $rateAmount->default_rate = $scrutinize_theory_grade_sheet_rate;  // ₹24 per script (example rate)
                $rateAmount->exam_type_id = $exam_type;
                $rateAmount->saved = 1;
                $rateAmount->save();

                Log::info('✅ RateAmount created (grade sheet):', $rateAmount->toArray());
            }


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

    public function storeScrutinizersSessionalGradeSheet(Request $request)
    {
        $teacherData = $request->input('scrutinizing_sessional_grade_sheet_teacher_ids', []);
        $studentData = $request->input('scrutinizing_sessional_grade_sheet_no_of_students', []);
        $sessionId = $request->sid;
        $scrutinize_sessional_grade_sheet_rate = $request->scrutinize_sessional_grade_sheet_rate;
        $exam_type=1;

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
            $rateHead = RateHead::where('order_no', '10.b')->first();
            if (!$rateHead) {
                $rateHead = new RateHead();
                $rateHead->head = 'GradeSheet Scrutinizing (Sessional)';
                $rateHead->order_no = '10.b';
                $rateHead->sub_head = 'Sessional';
                $rateHead->dist_type = 'Share';
                $rateHead->enable_min = 0;
                $rateHead->enable_max = 0;
                $rateHead->is_course = 1;
                $rateHead->is_student_count = 1;
                $rateHead->marge_with = null;
                $rateHead->status = 1;
                $rateHead->save();

                Log::info('✅ RateHead created or found:', $rateHead->toArray());
            }


            // Step 3: Ensure session
            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

            // Step 4: Create or fetch RateAmount
            $rateAmount = RateAmount::where('rate_head_id', $rateHead->id)
                ->where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->first();


            if (!$rateAmount) {
                $rateAmount = new RateAmount();
                $rateAmount->rate_head_id = $rateHead->id;
                $rateAmount->session_id = $session_info->id;
                $rateAmount->default_rate = $scrutinize_sessional_grade_sheet_rate;  // ₹24 per script (example rate)
                $rateAmount->exam_type_id = $exam_type;
                $rateAmount->saved = 1;
                $rateAmount->save();

                Log::info('✅ RateAmount created (grade sheet):', $rateAmount->toArray());
            }

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

    public function storePreparedComputerizedResult(Request $request)
    {
        $teacherData = $request->input('prepared_computerized_result_teacher_ids', []);
        $studentData = $request->input('prepared_computerized_result_no_of_students', []);
        $sessionId = $request->sid;
        $prepare_computerized_result_rate=$request->input('prepare_computerized_result_rate');
        $exam_type=1;

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
            $rateHead = RateHead::where('order_no', '8.d')->first();
            if (!$rateHead) {
                $rateHead = new RateHead();
                $rateHead->head = 'Prepared Computerized Result';
                $rateHead->order_no = '8.d';
                $rateHead->dist_type = 'Share';
                $rateHead->enable_min = 0;
                $rateHead->enable_max = 0;
                $rateHead->is_course = 1;
                $rateHead->is_student_count = 1;
                $rateHead->marge_with = null;
                $rateHead->status = 1;
                $rateHead->save();

                Log::info('✅ RateHead created or found:', $rateHead->toArray());
            }

            Log::info('✅ RateHead confirmed', $rateHead->toArray());

            // Step 3: Get or create session
            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

            // Step 4: Create or fetch RateAmount
            $rateAmount = RateAmount::where('rate_head_id', $rateHead->id)
                ->where('session_id', $session_info->id)
                ->where('exam_type_id', $exam_type)
                ->first();


            if (!$rateAmount) {
                $rateAmount = new RateAmount();
                $rateAmount->rate_head_id = $rateHead->id;
                $rateAmount->session_id = $session_info->id;
                $rateAmount->default_rate = $prepare_computerized_result_rate;  // ₹24 per script (example rate)
                $rateAmount->exam_type_id = $exam_type;
                $rateAmount->saved = 1;
                $rateAmount->save();

                Log::info('✅ RateAmount created (computerized result):', $rateAmount->toArray());
            }

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

    public function storeVerifiedComputerizedGradeSheet(Request $request)
    {
        $teacherIds = $request->input('verified_computerized_result_teachers', []);
        $totalStudents = (int) $request->input('verified_computerized_result_total_students');
        $sessionId = $request->sid;
        $verified_computerized_grade_sheet_rate = $request->verified_computerized_grade_sheet_rate;
        $exam_type=1;

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
            $rateHead = RateHead::firstOrNew([
                'order_no' => '8.c',
            ]);

            if(!$rateHead->exist){
                $rateHead->head = 'Grade Sheeets/GPA Verification';
                $rateHead->dist_type = 'Share';
                $rateHead->enable_min = 0;
                $rateHead->enable_max = 0;
                $rateHead->is_course = 0;
                $rateHead->is_student_count = 1;
                $rateHead->marge_with = null;
                $rateHead->status = 1;
                $rateHead->save();
                Log::info('✅ RateHead created or found:', $rateHead->toArray());
            }


            Log::info('✅ RateHead created or updated.', $rateHead->toArray());

            // Step 2: Get or create session
            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

            // Step 3: RateAmount
            $rateAmount = RateAmount::firstOrNew([
                'rate_head_id' => $rateHead->id,
                'session_id' => $session_info->id,
                'exam_type_id' => $exam_type,
            ]);

            if (!$rateAmount->exists) {
                $rateAmount->default_rate = $verified_computerized_grade_sheet_rate; // Set your rate here
                $rateAmount->exam_type_id = $exam_type;
                $rateAmount->saved = 1;
                $rateAmount->save();
                Log::info('✅ RateAmount created.', $rateAmount->toArray());
            }

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
        $exam_type=1;


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
            $rateHead = RateHead::where('order_no', '12.a')->first();
            Log::info('rateHead', $rateHead ? $rateHead->toArray() : ['rateHead' => null]);
            if (!$rateHead) {
                $rateHead = new RateHead();
                $rateHead->head = 'Question';
                $rateHead->sub_head='Stencil Cutting';
                $rateHead->order_no = '12.a';
                $rateHead->is_course = 1;
                $rateHead->dist_type = 'Individual';
                $rateHead->is_student_count = 1;
                $rateHead->marge_with = null;
                $rateHead->status = 1;
                $rateHead->save();
                if ($rateHead->save()) {
                    Log::info('✅ New RateHead created', $rateHead->toArray());
                } else {
                    Log::error('❌ Failed to save RateHead');
                }
            }

            //ensure session exist
            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);





            // Step 4: Ensure  RateAmount exists(Rate Amount Exist for Rate Head=1)
            $rateAmount = RateAmount::where('rate_head_id', $rateHead->id)
                ->where('session_id', $session_info->id)
                ->where('exam_type_id',$exam_type)
                ->first();

            Log::info('rateAmount', $rateAmount ? $rateAmount->toArray() : ['rateAmount' => null]);
            if (!$rateAmount) {
                $rateAmount = new RateAmount();
                $rateAmount->default_rate = $per_stencil_cutting_rate;
                $rateAmount->session_id = $session_info->id;
                $rateAmount->rate_head_id = $rateHead->id;
                $rateAmount->exam_type_id = $exam_type;
                $rateAmount->saved = 1;
                if ($rateAmount->save()) {
                    Log::info('✅ New RateAmount created', $rateAmount->toArray());
                } else {
                    Log::error('❌ Failed to save RateHead');
                }
            }



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
                    'teacher_id' => $teacherId,
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
        $exam_type=1;


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
            $rateHead = RateHead::where('order_no', '12.b')->first();
            Log::info('rateHead', $rateHead ? $rateHead->toArray() : ['rateHead' => null]);
            if (!$rateHead) {
                $rateHead = new RateHead();
                $rateHead->head = 'Question';
                $rateHead->sub_head='Printing';
                $rateHead->order_no = '12.b';
                $rateHead->is_course = 0;
                $rateHead->dist_type = 'Individual';
                $rateHead->is_student_count = 1;
                $rateHead->marge_with = null;
                $rateHead->status = 1;
                $rateHead->save();
                if ($rateHead->save()) {
                    Log::info('✅ New RateHead created', $rateHead->toArray());
                } else {
                    Log::error('❌ Failed to save RateHead');
                }
            }

            //ensure session exist
            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);





            // Step 4: Ensure  RateAmount exists(Rate Amount Exist for Rate Head=1)
            $rateAmount = RateAmount::where('rate_head_id', $rateHead->id)
                ->where('session_id', $session_info->id)
                ->where('exam_type_id',$exam_type)
                ->first();

            Log::info('rateAmount', $rateAmount ? $rateAmount->toArray() : ['rateAmount' => null]);
            if (!$rateAmount) {
                $rateAmount = new RateAmount();
                $rateAmount->default_rate = $per_printing_question_paper_rate;
                $rateAmount->session_id = $session_info->id;
                $rateAmount->rate_head_id = $rateHead->id;
                $rateAmount->exam_type_id = $exam_type;
                $rateAmount->saved = 1;
                if ($rateAmount->save()) {
                    Log::info('✅ New RateAmount created', $rateAmount->toArray());
                } else {
                    Log::error('❌ Failed to save RateHead');
                }
            }



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
                    'teacher_id' => $teacherId,
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
        $number_of_correction = $request->input('comparison_question_committee_amounts');        // array (indexed)
        $sessionId = $request->sid;
        $per_comparsion_rate=$request->comparison_question_paper_rate;
        $exam_type=1;


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
            $rateHead = RateHead::where('order_no', 11)->first();
            Log::info('rateHead', $rateHead ? $rateHead->toArray() : ['rateHead' => null]);
            if (!$rateHead) {
                $rateHead = new RateHead();
                $rateHead->head = 'Question Typing,Sketching & Misc.';
                $rateHead->order_no = '11';
                $rateHead->is_course = 1;
                $rateHead->dist_type = 'Individual';
                $rateHead->is_student_count = 1;
                $rateHead->marge_with = null;
                $rateHead->status = 1;
                $rateHead->save();
                if ($rateHead->save()) {
                    Log::info('✅ New RateHead created', $rateHead->toArray());
                } else {
                    Log::error('❌ Failed to save RateHead');
                }
            }

            //ensure session exist
            $session_info = LocalData::getOrCreateRegularSession($sessionId,$exam_type);





            // Step 4: Ensure  RateAmount exists(Rate Amount Exist for Rate Head=1)
            $rateAmount = RateAmount::where('rate_head_id', $rateHead->id)
                ->where('session_id', $session_info->id)
                ->where('exam_type_id',$exam_type)
                ->first();

            Log::info('rateAmount', $rateAmount ? $rateAmount->toArray() : ['rateAmount' => null]);
            if (!$rateAmount) {
                $rateAmount = new RateAmount();
                $rateAmount->default_rate = $per_comparsion_rate;
                $rateAmount->session_id = $session_info->id;
                $rateAmount->rate_head_id = $rateHead->id;
                $rateAmount->exam_type_id = $exam_type;
                $rateAmount->saved = 1;
                if ($rateAmount->save()) {
                    Log::info('✅ New RateAmount created', $rateAmount->toArray());
                } else {
                    Log::error('❌ Failed to save RateHead');
                }
            }



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

    public function storeAdvisorStudent(Request $request)
    {
        $teacherIds = $request->input('advisorTeacherIds', []);
        $studentCounts = $request->input('advisorTotal_students', []);
        $sessionId = $request->sid;
        $advisor_per_student_rate = $request->advisor_per_student_rate;
        $exam_type=1;

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
            $rateHead = RateHead::where('order_no', '13')
                ->first();

            if (!$rateHead) {
                $rateHead = new RateHead();
                $rateHead->order_no = 13;
                $rateHead->head = 'Advisor Fee';
                $rateHead->dist_type = 'Individual';
                $rateHead->is_course = 0;
                $rateHead->is_student_count = 1;
                $rateHead->marge_with = null;
                $rateHead->status = 1;
                $rateHead->save();
                Log::info('✅ RateHead Created or Fetched (Advisor-Student)', $rateHead->toArray());
            }

            Log::info('✅ RateHead confirmed', $rateHead->toArray());

            // ✅ Step 2: Create or fetch Session
            $session = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

            // ✅ Step 3: Create or fetch RateAmount
            $rateAmount = RateAmount::firstOrNew([
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'exam_type_id' => $exam_type
            ]);

            if (!$rateAmount->exists) {
                $rateAmount->default_rate = $advisor_per_student_rate; // Example rate per student
                $rateAmount->saved = 1;
                $rateAmount->save();

                Log::info('✅ RateAmount Created (Advisor-Student)', $rateAmount->toArray());
            }

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

    public function storeVerifiedFinalGraduationResult(Request $request)
    {
        $teacherIds = $request->input('verified_grade_teacher_ids', []);
        $no_of_students = $request->input('verified_grade_amounts', []);
        $sessionId = $request->sid;
        $final_result_per_student_rate=$request->final_result_per_student_rate;
        $exam_type=1;


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
            $rateHead = RateHead::where('order_no', '16')->first();

            if (!$rateHead) {
                $rateHead = RateHead::create([
                    'order_no' => 16,
                    'head' => 'Verified of Final Graduation Result',
                    'dist_type' => 'Individual',
                    'enable_min' => 0,
                    'enable_max' => 0,
                    'is_course' => 0,
                    'is_student_count' => 1,
                    'marge_with' => null,
                    'status' => 1,
                ]);
                Log::info('✅ RateHead Created', $rateHead->toArray());
            }

            Log::info('✅ RateHead confirmed', $rateHead->toArray());
            // Step 2: Get or create Session
            $session = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

            // Step 3: Get or create RateAmount
            $rateAmount = RateAmount::firstOrNew([
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'exam_type_id' => $exam_type
            ]);

            if (!$rateAmount->exists) {
                $rateAmount->default_rate = $final_result_per_student_rate; // Set your rate per student
                $rateAmount->saved = 1;
                $rateAmount->save();

                Log::info('✅ RateAmount Created', $rateAmount->toArray());
            }

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

    public function storeConductedCentralOralExam(Request $request)
    {
        $teacherIds = $request->input('conducted_central_oral_examination_teacher_ids', []);
        $no_of_students = $request->input('conducted_central_oral_examination_student_amounts', []);
        $sessionId = $request->sid;
        $central_examination_thesis_rate = $request->oral_central_exam_thesis_project;
        $exam_type=1;

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
            $rateHead = RateHead::where('order_no', '7.e')->first();

            if (!$rateHead) {
                $rateHead = RateHead::create([
                    'order_no' => '7.e',
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
                Log::info('✅ RateHead Created', $rateHead->toArray());
            }

            Log::info('✅ RateHead confirmed', $rateHead->toArray());

            // Step 2: Get or create Session
            $session = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

            // Step 3: Get or create RateAmount
            $rateAmount = RateAmount::firstOrNew([
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'exam_type_id' => $exam_type
            ]);

            if (!$rateAmount->exists) {
                $rateAmount->default_rate = $central_examination_thesis_rate; // Set your rate per student
                $rateAmount->saved = 1;
                $rateAmount->save();

                Log::info('✅ RateAmount Created', $rateAmount->toArray());
            }

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

    public function storeInvolvedSurvey(Request $request)
    {
        $teacherIds = $request->input('involved_survey_teacher_ids'); // array
        $no_of_students = $request->input('involved_survey_student_amounts');        // array (indexed)
        $sessionId = $request->sid;
        $servey_rate = $request->servey_rate;
        $exam_type=1;

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
            $rateHead = RateHead::where('order_no', '7.f')->first();

            if (!$rateHead) {
                $rateHead = RateHead::create([
                    'order_no' => '7.f',
                    'head' => 'Sessional',
                    'sub_head' => 'Survey',
                    'dist_type' => 'Individual',
                    'is_course' => 0,
                    'is_student_count' => 1,
                    'marge_with' => null,
                    'status' => 1,
                ]);
                Log::info('✅ RateHead Created', $rateHead->toArray());
            }

            Log::info('✅ RateHead confirmed', $rateHead->toArray());

            // Step 2: Get or create Session
            $session = LocalData::getOrCreateRegularSession($sessionId,$exam_type);

            // Step 3: Get or create RateAmount
            $rateAmount = RateAmount::firstOrNew([
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'exam_type_id' => $exam_type
            ]);

            if (!$rateAmount->exists) {
                $rateAmount->default_rate = $servey_rate; // Set your rate per student
                $rateAmount->saved = 1;
                $rateAmount->save();

                Log::info('✅ RateAmount Created', $rateAmount->toArray());
            }
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

    public function storeConductedPreliminaryViva(Request $request)
    {
        $teacherIds = $request->input('conducted_preliminary_viva_teacher_ids'); // array
        $no_of_students = $request->input('conducted_preliminary_viva_student_amounts');        // array (indexed)
        $sessionId = $request->sid;
        $viva_thesis_project_rate = $request->viva_thesis_project_rate;
        $exam_type=1;


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
            $rateHead = RateHead::where('order_no', '6.c')->first();

            if (!$rateHead) {
                $rateHead = RateHead::create([
                    'order_no' => '6.c',
                    'head' => 'Project/Thesis',
                    'sub_head' =>  'Initial Viva ' . ($session->year . '/' . $session->semester),
                    'dist_type' => 'Individual',
                    'is_course' => 0,
                    'is_student_count' => 1,
                    'marge_with' => null,
                    'status' => 1,
                ]);
                Log::info('✅ RateHead Created', $rateHead->toArray());
            }
            Log::info('✅ RateHead confirmed', $rateHead->toArray());


            // Step 3: Get or create RateAmount
            $rateAmount = RateAmount::firstOrNew([
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'exam_type_id' => $exam_type
            ]);

            if (!$rateAmount->exists) {
                $rateAmount->default_rate = $viva_thesis_project_rate; // Set your rate per student
                $rateAmount->saved = 1;
                $rateAmount->save();

                Log::info('✅ RateAmount Created', $rateAmount->toArray());
            }
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

    public function storeExaminedThesisProject(Request $request)
    {
        $teacherIds = $request->input('examined_thesis_project_teacher_ids', []);
        $internal_no_of_students = $request->input('examined_internal_thesis_project_student_amounts', []);
        $external_no_of_students = $request->input('examined_external_thesis_project_student_amounts', []);
        $sessionId = $request->sid;
        $examined_thesis_project_rate = $request->examined_thesis_project_rate;
        $exam_type=1;


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
            $rateHead = RateHead::where('order_no', '6.a')->first();
            if (!$rateHead) {
                $rateHead = RateHead::create([
                    'order_no' => '6.a',
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
                Log::info('✅ RateHead Created', $rateHead->toArray());
            }
            Log::info('✅ RateHead confirmed', $rateHead->toArray());


            // 3. Get or Create RateAmount
            $rateAmount = RateAmount::firstOrNew([
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'exam_type_id' => $exam_type
            ]);

            if (!$rateAmount->exists) {
                $rateAmount->default_rate = $examined_thesis_project_rate;
                $rateAmount->saved = 1;
                $rateAmount->save();
                Log::info('✅ RateAmount Created', $rateAmount->toArray());
            }
            Log::info('✅ RateAmount Confirmed', $rateAmount->toArray());

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


                    $rateAssign = RateAssign::create([
                        'teacher_id' => $teacherId,
                        'rate_head_id' => $rateHead->id,
                        'session_id' => $session->id,
                        'no_of_items' => $totalStudents,
                        'total_amount' => $totalAmount,
                        'total_students'=>$totalStudents,
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

    public function storeConductedOralExamination(Request $request)
    {
        $teacherIds = $request->input('conducted_oral_examination_teacher_ids'); // array
        $no_of_students = $request->input('conducted_oral_examination_student_amounts');        // array (indexed)
        $sessionId = $request->sid;
        $oral_exam_thesis_project_rate = $request->oral_exam_thesis_project;
        $exam_type=1;

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
            $rateHead = RateHead::where('order_no', '6.d')->first();

            if (!$rateHead) {
                $rateHead = RateHead::create([
                    'order_no' => '6.d',
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
                Log::info('✅ RateHead Created', $rateHead->toArray());
            }
            Log::info('✅ RateHead confirmed', $rateHead->toArray());


            // Step 3: Get or create RateAmount
            $rateAmount = RateAmount::firstOrNew([
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'exam_type_id' => $exam_type
            ]);

            if (!$rateAmount->exists) {
                $rateAmount->default_rate = $oral_exam_thesis_project_rate; // Set your rate per student
                $rateAmount->saved = 1;
                $rateAmount->save();

                Log::info('✅ RateAmount Created', $rateAmount->toArray());
            }
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

    public function storeSupervisedThesisProject(Request $request)
    {
        $teacherIds = $request->input('supervised_thesis_project_teacher_ids'); // array
        $no_of_students = $request->input('supervised_thesis_project_student_amounts');        // array (indexed)
        $sessionId = $request->sid;
        $supervised_thesis_project_rate = $request->supervised_thesis_project_rate;
        $exam_type=1;

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
            $rateHead = RateHead::where('order_no', '6.b')->first();

            if (!$rateHead) {
                $rateHead = RateHead::create([
                    'order_no' => '6.b',
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
                Log::info('✅ RateHead Created', $rateHead->toArray());
            }
            Log::info('✅ RateHead confirmed', $rateHead->toArray());


            // Step 3: Get or create RateAmount
            $rateAmount = RateAmount::firstOrNew([
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'exam_type_id' => $exam_type
            ]);

            if (!$rateAmount->exists) {
                $rateAmount->default_rate = $supervised_thesis_project_rate; // Set your rate per student
                $rateAmount->saved = 1;
                $rateAmount->save();

                Log::info('✅ RateAmount Created', $rateAmount->toArray());
            }
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

    public function storeHonorariumCoordinator(Request $request)
    {
        // If validation passes, extract values
        $teacherId = $request->input('coordinator_id');
        $coordinator_rate = $request->input('coordinator_amount');
        $sessionId=$request->input('sid');
        $exam_type=1;

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
            $rateHead = RateHead::where('order_no', '14')->first();

            if (!$rateHead) {
                $rateHead = RateHead::create([
                    'order_no' => '14',
                    'head' => 'Course Co-ordinator Fee',
                    'dist_type' => 'Individual',
                    'is_course' => 0,
                    'is_student_count' => 0,
                    'marge_with' => null,
                    'status' => 1,
                ]);
                Log::info('✅ RateHead Created:', $rateHead->toArray());
            }
            Log::info('✅ RateHead confirmed', $rateHead->toArray());

            // Step 3: Get or create RateAmount
            $rateAmount = RateAmount::firstOrNew([
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'exam_type_id' => $exam_type
            ]);

            if (!$rateAmount->exists) {
                $rateAmount->default_rate = $coordinator_rate; // Set your rate per student
                $rateAmount->save();
                Log::info('✅ RateAmount Created', $rateAmount->toArray());
            }
            Log::info('✅ RateAmount Confirmed', $rateAmount->toArray());

            // Step 4: Create RateAssign

            Log::info('📘 Preparation Of RateAssign', [
                'teacher_id' => $teacherId,
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'total_amount' => $coordinator_rate,
                'exam_type_id'=>$exam_type,
            ]);
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

    public function storeHonorariumChairman(Request $request)
    {
        // If validation passes, extract values
        $teacherId = $request->input('chairman_id');
        $chairman_rate = $request->input('chairman_amount');
        $sessionId=$request->input('sid');
        $exam_type=1;

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
            $rateHead = RateHead::where('order_no', '15')->first();

            if (!$rateHead) {
                $rateHead = RateHead::create([
                    'order_no' => '15',
                    'head' => 'Chairman Fee',
                    'dist_type' => 'Individual',
                    'is_course' => 0,
                    'is_student_count' => 0,
                    'marge_with' => null,
                    'status' => 1,
                ]);
                Log::info('✅ RateHead Created:', $rateHead->toArray());
            }
            Log::info('✅ RateHead confirmed', $rateHead->toArray());

            // Step 3: Get or create RateAmount
            $rateAmount = RateAmount::firstOrNew([
                'rate_head_id' => $rateHead->id,
                'session_id' => $session->id,
                'exam_type_id' => $exam_type
            ]);

            if (!$rateAmount->exists) {
                $rateAmount->default_rate = $chairman_rate; // Set your rate per student
                $rateAmount->save();
                Log::info('✅ RateAmount Created', $rateAmount->toArray());
            }
            Log::info('✅ RateAmount Confirmed', $rateAmount->toArray());

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

            return response()->json(['message' => 'Course Co-ordinator Honorarium saved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error Storing Chairman Honorarium:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }



}


