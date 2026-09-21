<?php

namespace App\Services;

use App\Models\RateAmount;
use App\Models\RateAssign;
use App\Models\RateHead;

class ReviewSessionFormDataService
{
    /**
     * Preload rate settings and existing assignments for review session form.
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

        $scrutinizer_per_script_rate = null;
        $scrutinizer_min_rate = null;

        $theory_grade_sheet_per_subject_rate = null;

        $scrunizing_theory_grade_sheet_per_subject_rate = null;

        $stencill_cutting_per_stencil_rate = null;

        $print_question_paper_rate = null;

        $comparison_rate = null;

        $honorium_chairman = null;

        $prepared_computerized_per_student_per_subject_rate = null;
        $verified_computerized_per_student_per_subject_rate = null;
        $tabulation_per_student_rate = null;

        $savedModerationAssigns = collect();
        $savedRateAssignPaperSetter = collect();
        $savedRateAssignExaminer = collect();
        $savedRateAssignScrutinizers = collect();
        $savedRateAssignTheoryGradeSheet = collect();
        $savedRateAssignScrutinizersTheoryGradeSheet = collect();
        $savedRateAssignPreparedComputerizedResult = collect();
        $savedRateAssignVerifiedComputerizedGradeSheet = collect();
        $savedRateAssignTabulation = collect();
        $savedRateAssignStencilCuttingCommittee = collect();
        $savedRateAssignPrintingQuestion = collect();
        $savedRateAssignComparisonCommittee = collect();
        $savedRateAssignHonorariumChairman = collect();

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

            // For TheoryGradeSheet
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

            // For ScrutinizersTheoryGradeSheet
            $rateScrutinizersTheoryGradeSheet = RateHead::where('order_no', '=', '10.a')->first();
            if ($rateScrutinizersTheoryGradeSheet) {
                $savedRateAssignScrutinizersTheoryGradeSheet = RateAssign::getTeacherWithCourse(
                    $session_info->id,
                    $exam_type,
                    $rateScrutinizersTheoryGradeSheet->id
                );

                $preparatonSessionalGradeSheetData = RateAmount::where('exam_type_id', $exam_type)
                    ->where('rate_head_id', $rateScrutinizersTheoryGradeSheet->id)
                    ->where('session_id', $session_info->id)
                    ->first();
                $scrunizing_theory_grade_sheet_per_subject_rate = $preparatonSessionalGradeSheetData?->default_rate;
            }

            // For PreparedComputerizedResult (8.d)
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

            // For VerifiedComputerizedGradeSheet (8.c)
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

            // For HonorariumChairman
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
            'scrutinizer_per_script_rate',
            'scrutinizer_min_rate',
            'theory_grade_sheet_per_subject_rate',
            'scrunizing_theory_grade_sheet_per_subject_rate',
            'prepared_computerized_per_student_per_subject_rate',
            'verified_computerized_per_student_per_subject_rate',
            'tabulation_per_student_rate',
            'stencill_cutting_per_stencil_rate',
            'print_question_paper_rate',
            'comparison_rate',
            'honorium_chairman',
            'savedModerationAssigns',
            'savedRateAssignPaperSetter',
            'savedRateAssignExaminer',
            'savedRateAssignScrutinizers',
            'savedRateAssignTheoryGradeSheet',
            'savedRateAssignScrutinizersTheoryGradeSheet',
            'savedRateAssignPreparedComputerizedResult',
            'savedRateAssignVerifiedComputerizedGradeSheet',
            'savedRateAssignTabulation',
            'savedRateAssignStencilCuttingCommittee',
            'savedRateAssignPrintingQuestion',
            'savedRateAssignComparisonCommittee',
            'savedRateAssignHonorariumChairman'
        );
    }
}
