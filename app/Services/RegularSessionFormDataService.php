<?php

namespace App\Services;

use App\Models\RateAmount;
use App\Models\RateAssign;
use App\Models\RateHead;

class RegularSessionFormDataService
{
    /**
     * Preload rate settings and existing assignments for regular session form.
     *
     * @param mixed $session_info
     * @param int|string $exam_type
     * @return array
     */
    public static function getPreloadedFormData($session_info, $exam_type): array
    {
        // Initialize variables with default values
        $mc_min_rate = null;
        $mc_max_rate = null;
        $paper_setter_rate = null;
        $examiner_rate_per_script = null;
        $examiner_min_rate = null;

        $ct_per_class_test_rate = null;
        $ca_per_class_assignment_rate = null;

        $sessional_per_contact_hour_rate = null;
        $sessional_min_exam_rate = null;
        $sessional_total_week_semester_rate = null;
        $sessional_total_students = null;

        $scrutinizer_per_script_rate = null;
        $scrutinizer_min_rate = null;

        $theory_grade_sheet_per_subject_rate = null;

        $sessional_grade_sheet_per_subject_rate = null;

        $scrunizing_theory_grade_sheet_per_subject_rate = null;

        $scrunizing_sessional_grade_sheet_per_subject_rate = null;

        $prepared_computerized_per_student_per_subject_rate = null;

        $verified_computerized_per_student_per_subject_rate = null;

        $tabulation_per_student_rate = null;

        $stencill_cutting_per_stencil_rate = null;

        $print_question_paper_rate = null;

        $comparison_rate = null;

        $advisor_per_student_rate = null;

        $final_graduation_per_student_rate = null;

        $conducted_central_oral_per_thesis_rate = null;

        $involved_survey_per_student_rate = null;

        $conducted_preliminary_viva_per_student_rate = null;

        $examined_thesis_per_student_rate = null;

        $conducted_oral_per_student_rate = null;

        $supervised_theis_per_student_rate = null;

        $course_profile_per_course_rate = null;

        $honorium_coordinator = null;

        $honorium_chairman = null;

        $savedModerationAssigns = collect();
        $savedRateAssignPaperSetter = collect();
        $savedRateAssignExaminer = collect();
        $savedRateAssignClassTest = collect();
        $savedRateAssignSessionalCourseTeacher = collect();
        $savedRateAssignScrutinizers = collect();
        $savedRateAssignTheoryGradeSheet = collect();
        $savedRateAssignSessionalGradeSheet = collect();
        $savedRateAssignScrutinizersTheoryGradeSheet = collect();
        $savedRateAssignScrutinizersSessionalGradeSheet = collect();
        $savedRateAssignPreparedComputerizedResult = collect();
        $savedRateAssignVerifiedComputerizedGradeSheet = collect();
        $savedRateAssignTabulation = collect();
        $savedRateAssignStencilCuttingCommittee = collect();
        $savedRateAssignPrintingQuestion = collect();
        $savedRateAssignComparisonCommittee = collect();
        $savedRateAssignAdvisorStudent = collect();
        $savedRateAssignVerifiedFinalGraduationResult = collect();
        $savedRateAssignConductedCentralOralExam = collect();
        $savedRateAssignInvolvedSurvey = collect();
        $savedRateAssignInvolvedIndustrialAttachment = collect();
        $savedRateAssignConductedPreliminaryViva = collect();
        $savedRateAssignExaminedThesisProject = collect();
        $savedRateAssignConductedOralExamination = collect();
        $savedRateAssignSupervisedThesisProject = collect();
        $savedRateAssignCourseProfile = collect();
        $savedRateAssignHonorariumCoordinator = collect();
        $savedRateAssignHonorariumChairman = collect();

        // Ensure session_info is not null before proceeding
        if ($session_info) {
            // Moderation Committee
            $rateHead = RateHead::where('order_no', 1)->first();
            if ($rateHead) {
                $mc_data = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateHead->id)
                    ->where('session_id', $session_info->id)
                    ->first();

                // Check if mc_data is found before accessing properties
                $mc_min_rate = $mc_data ? $mc_data->min_rate : null;
                $mc_max_rate = $mc_data ? $mc_data->max_rate : null;
            }

            // Paper Setter Examiner
            $rateHeadPaperSetter = RateHead::where('order_no', 2)->first();
            $rateHeadExaminer = RateHead::where('order_no', 3)->first();

            if ($rateHeadPaperSetter && $rateHeadExaminer) {
                $ps_data = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateHeadPaperSetter->id)
                    ->where('session_id', $session_info->id)
                    ->first();

                $examiner_data = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateHeadExaminer->id)
                    ->where('session_id', $session_info->id)
                    ->first();

                // Check if ps_data and examiner_data are found before accessing properties
                $paper_setter_rate = $ps_data ? $ps_data->default_rate : null;
                $examiner_rate_per_script = $examiner_data ? $examiner_data->default_rate : null;
                $examiner_min_rate = $examiner_data ? $examiner_data->min_rate : null;
            }

            // For Moderation Committee (Ensure session_info, rateHead, and exam_type are not null)
            $savedModerationAssigns = ($session_info && $rateHead && $exam_type)
                ? RateAssign::getModerationCommitteeData($session_info->id, $exam_type, $rateHead->id)
                : collect();

            // For Paper Setter Examiner (Ensure all needed data exists)
            if ($rateHeadPaperSetter && $rateHeadExaminer) {
                $savedRateAssignPaperSetter = RateAssign::getTeacherWithCourse(
                    $session_info->id,
                    $exam_type,
                    $rateHeadPaperSetter->id
                );

                $savedRateAssignExaminer = RateAssign::getTeacherWithCourse(
                    $session_info->id,
                    $exam_type,
                    $rateHeadExaminer->id
                );
            }

            // For Class Test
            $rateHeadCT = RateHead::where('order_no', 4)->first();
            if ($rateHeadCT) {
                $savedRateAssignClassTest = RateAssign::getTeacherWithCourse(
                    $session_info->id,
                    $exam_type,
                    $rateHeadCT->id
                );

                $classTestData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateHeadCT->id)
                    ->where('session_id', $session_info->id)
                    ->first();
                $ct_per_class_test_rate = $classTestData?->default_rate;
            }

            /*
            // For Class Assignment (Order 4.b) (Commented out)
            $rateHeadCA = RateHead::where('order_no', '4.b')->first();
            if ($rateHeadCA) {
                $classAssignmentData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateHeadCA->id)
                    ->where('session_id', $session_info->id)
                    ->first();
                $ca_per_class_assignment_rate = $classAssignmentData?->default_rate;
            }
            */

            // For Sessional Course Teacher
            $rateHeadSCT = RateHead::where('order_no', 5)->first();
            if ($rateHeadSCT) {
                $savedRateAssignSessionalCourseTeacher = RateAssign::getTeacherWithCourse(
                    $session_info->id,
                    $exam_type,
                    $rateHeadSCT->id
                );

                $SessionalCourseTeacherData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateHeadSCT->id)
                    ->where('session_id', $session_info->id)
                    ->first();
                $sessional_per_contact_hour_rate = $SessionalCourseTeacherData?->default_rate;
                $sessional_min_exam_rate = $SessionalCourseTeacherData?->min_rate;
                $sessional_total_week_semester_rate = $SessionalCourseTeacherData?->total_week;
                $sessional_total_students = $savedRateAssignSessionalCourseTeacher->flatten()->first()?->total_students;
            }

            // For Scrutinizers
            $rateHeadScrutinizers = RateHead::where('order_no', 9)->first();
            if ($rateHeadScrutinizers) {
                $savedRateAssignScrutinizers = RateAssign::getTeacherWithCourse(
                    $session_info->id,
                    $exam_type,
                    $rateHeadScrutinizers->id
                );

                $ScrutinizersData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateHeadScrutinizers->id)
                    ->where('session_id', $session_info->id)
                    ->first();

                $scrutinizer_per_script_rate = $ScrutinizersData?->default_rate;
                $scrutinizer_min_rate = $ScrutinizersData?->min_rate;
            }

            // For Preparation Theory Grade Sheet
            $rateTheoryGradeSheet = RateHead::where('order_no', '=', '8.a')->first();
            if ($rateTheoryGradeSheet) {
                $savedRateAssignTheoryGradeSheet = RateAssign::getTeacherWithCourse(
                    $session_info->id,
                    $exam_type,
                    $rateTheoryGradeSheet->id
                );

                $preparatonTheroyGradeSheetData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateTheoryGradeSheet->id)
                    ->where('session_id', $session_info->id)
                    ->first();
                $theory_grade_sheet_per_subject_rate = $preparatonTheroyGradeSheetData?->default_rate;
            }

            // For SessionalGradeSheet
            $rateSessionalGradeSheet = RateHead::where('order_no', '=', '8.b')->first();
            if ($rateSessionalGradeSheet) {
                $savedRateAssignSessionalGradeSheet = RateAssign::getTeacherWithCourse(
                    $session_info->id,
                    $exam_type,
                    $rateSessionalGradeSheet->id
                );
                $preparatonSessionalGradeSheetData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateSessionalGradeSheet->id)
                    ->where('session_id', $session_info->id)
                    ->first();
                $sessional_grade_sheet_per_subject_rate = $preparatonSessionalGradeSheetData?->default_rate;
            }

            // For ScrutinizersTheoryGradeSheet
            $rateScrutinizersTheoryGradeSheet = RateHead::where('order_no', '=', '10.a')->first();
            if ($rateScrutinizersTheoryGradeSheet) {
                $savedRateAssignScrutinizersTheoryGradeSheet = RateAssign::getTeacherWithCourse(
                    $session_info->id,
                    $exam_type,
                    $rateScrutinizersTheoryGradeSheet->id
                );

                $preparatonTheoryScrutinizersData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateScrutinizersTheoryGradeSheet->id)
                    ->where('session_id', $session_info->id)
                    ->first();
                $scrunizing_theory_grade_sheet_per_subject_rate = $preparatonTheoryScrutinizersData?->default_rate;
            }

            // For ScrutinizersSessionalGradeSheet
            $rateScrutinizersSessionalGradeSheet = RateHead::where('order_no', '=', '10.b')->first();
            if ($rateScrutinizersSessionalGradeSheet) {
                $savedRateAssignScrutinizersSessionalGradeSheet = RateAssign::getTeacherWithCourse(
                    $session_info->id,
                    $exam_type,
                    $rateScrutinizersSessionalGradeSheet->id
                );

                $ScrutinizersSessionalGradeSheetData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateScrutinizersSessionalGradeSheet->id)
                    ->where('session_id', $session_info->id)
                    ->first();
                $scrunizing_sessional_grade_sheet_per_subject_rate = $ScrutinizersSessionalGradeSheetData?->default_rate;
            }

            // For PreparedComputerizedResult
            $ratePreparedComputerizedResult = RateHead::where('order_no', '=', '8.d')->first();
            if ($ratePreparedComputerizedResult) {
                $savedRateAssignPreparedComputerizedResult = RateAssign::getTeacherWithCourse(
                    $session_info->id,
                    $exam_type,
                    $ratePreparedComputerizedResult->id
                );

                $PreparedComputerizedResultData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $ratePreparedComputerizedResult->id)
                    ->where('session_id', $session_info->id)
                    ->first();
                $prepared_computerized_per_student_per_subject_rate = $PreparedComputerizedResultData?->default_rate;
            }

            // For VerifiedComputerizedGradeSheet
            $rateVerifiedComputerizedGradeSheet = RateHead::where('order_no', '=', '8.c')->first();
            if ($rateVerifiedComputerizedGradeSheet) {
                $savedRateAssignVerifiedComputerizedGradeSheet = RateAssign::getTeachersFromCommittee(
                    $session_info->id,
                    $exam_type,
                    $rateVerifiedComputerizedGradeSheet->id
                );

                $VerifiedComputerizedGradeSheetData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateVerifiedComputerizedGradeSheet->id)
                    ->where('session_id', $session_info->id)
                    ->first();
                $verified_computerized_per_student_per_subject_rate = $VerifiedComputerizedGradeSheetData?->default_rate;
            }

            // For Tabulation (8.e)
            $rateTabulation = RateHead::where('order_no', '=', '8.e')->first();
            if ($rateTabulation) {
                $savedRateAssignTabulation = RateAssign::getTeachersFromCommittee(
                    $session_info->id,
                    $exam_type,
                    $rateTabulation->id
                );

                $TabulationData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateTabulation->id)
                    ->where('session_id', $session_info->id)
                    ->first();
                $tabulation_per_student_rate = $TabulationData?->default_rate;
            }

            // For StencilCuttingCommittee
            $rateStencilCuttingCommittee = RateHead::where('order_no', '=', '12.a')->first();
            if ($rateStencilCuttingCommittee) {
                $savedRateAssignStencilCuttingCommittee = RateAssign::getTeacherWithGroup(
                    $session_info->id,
                    $exam_type,
                    $rateStencilCuttingCommittee->id
                );

                $StencilCuttingData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateStencilCuttingCommittee->id)
                    ->where('session_id', $session_info->id)
                    ->first();

                $stencill_cutting_per_stencil_rate = $StencilCuttingData?->default_rate;
            }

            // For PrintingQuestion
            $ratePrintingQuestion = RateHead::where('order_no', '=', '12.b')->first();
            if ($ratePrintingQuestion) {
                $savedRateAssignPrintingQuestion = RateAssign::getTeacherWithGroup(
                    $session_info->id,
                    $exam_type,
                    $ratePrintingQuestion->id
                );

                $PrintingQuestionData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $ratePrintingQuestion->id)
                    ->where('session_id', $session_info->id)
                    ->first();
                $print_question_paper_rate = $PrintingQuestionData?->default_rate;
            }

            // For ComparisonCommittee
            $rateComparisonCommittee = RateHead::where('order_no', '=', '11')->first();
            if ($rateComparisonCommittee) {
                $savedRateAssignComparisonCommittee = RateAssign::getTeacherWithGroup(
                    $session_info->id,
                    $exam_type,
                    $rateComparisonCommittee->id
                );

                $ComparisonData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateComparisonCommittee->id)
                    ->where('session_id', $session_info->id)
                    ->first();
                $comparison_rate = $ComparisonData?->default_rate;
            }

            // For AdvisorStudent
            $rateAdvisorStudent = RateHead::where('order_no', '=', '13')->first();
            if ($rateAdvisorStudent) {
                $savedRateAssignAdvisorStudent = RateAssign::getTeachersFromCommittee(
                    $session_info->id,
                    $exam_type,
                    $rateAdvisorStudent->id
                );

                $AdvisorStudentData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateAdvisorStudent->id)
                    ->where('session_id', $session_info->id)
                    ->first();
                $advisor_per_student_rate = $AdvisorStudentData?->default_rate;
            }

            // For VerifiedFinalGraduationResult
            $rateVerifiedFinalGraduationResult = RateHead::where('order_no', '=', '16')->first();
            if ($rateVerifiedFinalGraduationResult) {
                $savedRateAssignVerifiedFinalGraduationResult = RateAssign::getTeachersFromCommittee(
                    $session_info->id,
                    $exam_type,
                    $rateVerifiedFinalGraduationResult->id
                );

                $VerifiedFinalGraduationResultData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateVerifiedFinalGraduationResult->id)
                    ->where('session_id', $session_info->id)
                    ->first();
                $final_graduation_per_student_rate = $VerifiedFinalGraduationResultData?->default_rate;
            }

            // For ConductedCentralOralExam
            $rateConductedCentralOralExam = RateHead::where('order_no', '=', '7.e')->first();
            if ($rateConductedCentralOralExam) {
                $savedRateAssignConductedCentralOralExam = RateAssign::getTeacherWithGroup(
                    $session_info->id,
                    $exam_type,
                    $rateConductedCentralOralExam->id
                );

                $ConductedCentralOralData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateConductedCentralOralExam->id)
                    ->where('session_id', $session_info->id)
                    ->first();

                $conducted_central_oral_per_thesis_rate = $ConductedCentralOralData?->default_rate;
            }

            // For InvolvedSurvey
            $rateInvolvedSurvey = RateHead::where('order_no', '=', '7.f')->first();
            if ($rateInvolvedSurvey) {
                $savedRateAssignInvolvedSurvey = RateAssign::getTeacherWithCourse(
                    $session_info->id,
                    $exam_type,
                    $rateInvolvedSurvey->id
                );

                $InvolvedSurveyData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateInvolvedSurvey->id)
                    ->where('session_id', $session_info->id)
                    ->first();

                $involved_survey_per_student_rate = $InvolvedSurveyData?->default_rate;
            }

            // For InvolvedIndustrialAttachment (7.g)
            $rateInvolvedIndustrialAttachment = RateHead::where('order_no', '=', '7.g')->first();
            if ($rateInvolvedIndustrialAttachment) {
                $savedRateAssignInvolvedIndustrialAttachment = RateAssign::getTeacherWithCourse(
                    $session_info->id,
                    $exam_type,
                    $rateInvolvedIndustrialAttachment->id
                );

                $InvolvedIndustrialAttachmentData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateInvolvedIndustrialAttachment->id)
                    ->where('session_id', $session_info->id)
                    ->first();

                $involved_industrial_attachment_per_student_rate = $InvolvedIndustrialAttachmentData?->default_rate;
            }

            // For CourseProfile (17)
            $rateCourseProfile = RateHead::where('order_no', '=', '17')->first();
            if ($rateCourseProfile) {
                $savedRateAssignCourseProfile = RateAssign::getTeacherWithCourse(
                    $session_info->id,
                    $exam_type,
                    $rateCourseProfile->id
                );

                $CourseProfileData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateCourseProfile->id)
                    ->where('session_id', $session_info->id)
                    ->first();

                $course_profile_per_course_rate = $CourseProfileData?->default_rate;
            }

            // For ConductedPreliminaryViva
            $rateConductedPreliminaryViva = RateHead::where('order_no', '=', '6.c')->first();
            if ($rateConductedPreliminaryViva) {
                $savedRateAssignConductedPreliminaryViva = RateAssign::getTeachersFromCommittee(
                    $session_info->id,
                    $exam_type,
                    $rateConductedPreliminaryViva->id
                );

                $ConductedPreliminaryVivaData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateConductedPreliminaryViva->id)
                    ->where('session_id', $session_info->id)
                    ->first();

                $conducted_preliminary_viva_per_student_rate = $ConductedPreliminaryVivaData?->default_rate;
            }

            // For ExaminedThesisProject 6.a
            $rateExaminedThesisProject = RateHead::where('order_no', '=', '6.a')->first();
            if ($rateExaminedThesisProject) {
                $savedRateAssignExaminedThesisProject = RateAssign::getInternalExternalTeacher(
                    $session_info->id,
                    $exam_type,
                    $rateExaminedThesisProject->id
                );

                $ExaminedThesisProjectData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateExaminedThesisProject->id)
                    ->where('session_id', $session_info->id)
                    ->first();

                $examined_thesis_per_student_rate = $ExaminedThesisProjectData?->default_rate;
            }

            // For ConductedOralExamination 6.d
            $rateConductedOralExamination = RateHead::where('order_no', '=', '6.d')->first();
            if ($rateConductedOralExamination) {
                $savedRateAssignConductedOralExamination = RateAssign::getTeachersFromCommittee(
                    $session_info->id,
                    $exam_type,
                    $rateConductedOralExamination->id
                );

                $ConductedOralExaminationData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateConductedOralExamination->id)
                    ->where('session_id', $session_info->id)
                    ->first();

                $conducted_oral_per_student_rate = $ConductedOralExaminationData?->default_rate;
            }

            // For SupervisedThesisProject 6.b
            $rateSupervisedThesisProject = RateHead::where('order_no', '=', '6.b')->first();
            if ($rateSupervisedThesisProject) {
                $savedRateAssignSupervisedThesisProject = RateAssign::getTeachersFromCommittee(
                    $session_info->id,
                    $exam_type,
                    $rateSupervisedThesisProject->id
                );

                $SupervisedThesisProjectData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateSupervisedThesisProject->id)
                    ->where('session_id', $session_info->id)
                    ->first();

                $supervised_theis_per_student_rate = $SupervisedThesisProjectData?->default_rate;
            }

            // For HonorariumCoordinator 14
            $rateHonorariumCoordinator = RateHead::where('order_no', '=', '14')->first();
            if ($rateHonorariumCoordinator) {
                $savedRateAssignHonorariumCoordinator = RateAssign::getTeachersFromCommittee(
                    $session_info->id,
                    $exam_type,
                    $rateHonorariumCoordinator->id
                );

                $HonorariumCoordinatorData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateHonorariumCoordinator->id)
                    ->where('session_id', $session_info->id)
                    ->first();

                $honorium_coordinator = $HonorariumCoordinatorData?->default_rate;
            }

            // For HonorariumChairman 15
            $rateHonorariumChairman = RateHead::where('order_no', '=', '15')->first();
            if ($rateHonorariumChairman) {
                $savedRateAssignHonorariumChairman = RateAssign::getTeachersFromCommittee(
                    $session_info->id,
                    $exam_type,
                    $rateHonorariumChairman->id
                );

                $HonorariumChairmanData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateHonorariumChairman->id)
                    ->where('session_id', $session_info->id)
                    ->first();

                $honorium_chairman = $HonorariumChairmanData?->default_rate;
            }
        }

        return compact(
            'mc_min_rate',
            'mc_max_rate',
            'paper_setter_rate',
            'examiner_rate_per_script',
            'examiner_min_rate',
            'ct_per_class_test_rate',
            'ca_per_class_assignment_rate',
            'sessional_per_contact_hour_rate',
            'sessional_min_exam_rate',
            'sessional_total_week_semester_rate',
            'sessional_total_students',
            'scrutinizer_per_script_rate',
            'scrutinizer_min_rate',
            'theory_grade_sheet_per_subject_rate',
            'sessional_grade_sheet_per_subject_rate',
            'scrunizing_theory_grade_sheet_per_subject_rate',
            'scrunizing_sessional_grade_sheet_per_subject_rate',
            'prepared_computerized_per_student_per_subject_rate',
            'verified_computerized_per_student_per_subject_rate',
            'tabulation_per_student_rate',
            'stencill_cutting_per_stencil_rate',
            'print_question_paper_rate',
            'comparison_rate',
            'advisor_per_student_rate',
            'final_graduation_per_student_rate',
            'conducted_central_oral_per_thesis_rate',
            'involved_survey_per_student_rate',
            'involved_industrial_attachment_per_student_rate',
            'conducted_preliminary_viva_per_student_rate',
            'examined_thesis_per_student_rate',
            'conducted_oral_per_student_rate',
            'supervised_theis_per_student_rate',
            'course_profile_per_course_rate',
            'honorium_coordinator',
            'honorium_chairman',
            'savedModerationAssigns',
            'savedRateAssignPaperSetter',
            'savedRateAssignExaminer',
            'savedRateAssignClassTest',
            'savedRateAssignSessionalCourseTeacher',
            'savedRateAssignScrutinizers',
            'savedRateAssignTheoryGradeSheet',
            'savedRateAssignSessionalGradeSheet',
            'savedRateAssignScrutinizersTheoryGradeSheet',
            'savedRateAssignScrutinizersSessionalGradeSheet',
            'savedRateAssignPreparedComputerizedResult',
            'savedRateAssignVerifiedComputerizedGradeSheet',
            'savedRateAssignTabulation',
            'savedRateAssignStencilCuttingCommittee',
            'savedRateAssignPrintingQuestion',
            'savedRateAssignComparisonCommittee',
            'savedRateAssignAdvisorStudent',
            'savedRateAssignVerifiedFinalGraduationResult',
            'savedRateAssignConductedCentralOralExam',
            'savedRateAssignInvolvedSurvey',
            'savedRateAssignInvolvedIndustrialAttachment',
            'savedRateAssignConductedPreliminaryViva',
            'savedRateAssignExaminedThesisProject',
            'savedRateAssignConductedOralExamination',
            'savedRateAssignSupervisedThesisProject',
            'savedRateAssignCourseProfile',
            'savedRateAssignHonorariumCoordinator',
            'savedRateAssignHonorariumChairman'
        );
    }
}
