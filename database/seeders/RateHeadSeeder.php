<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RateHeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rate_heads')->insert([
            ['head' => 'Moderation', 'sub_head' => null, 'order_no' => '1', 'dist_type' => 'Individual', 'enable_min' => 1, 'enable_max' => 1, 'is_course' => 0, 'is_student_count' => 0, 'marge_with' => null, 'status' => 1],
            ['head' => 'Paper Setters', 'sub_head' => null, 'order_no' => '2', 'dist_type' => 'Individual', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 1, 'is_student_count' => 0, 'marge_with' => null, 'status' => 1],
            ['head' => 'Examiner', 'sub_head' => null, 'order_no' => '3', 'dist_type' => 'Share', 'enable_min' => 1, 'enable_max' => 0, 'is_course' => 1, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Class Test', 'sub_head' => null, 'order_no' => '4', 'dist_type' => 'Share', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 1, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Laboratory/Survey works', 'sub_head' => null, 'order_no' => '5', 'dist_type' => 'Share', 'enable_min' => 1, 'enable_max' => 0, 'is_course' => 1, 'is_student_count' => 0, 'marge_with' => null, 'status' => 1],
            ['head' => 'Scrutinizing(Answre Script)', 'sub_head' => null, 'order_no' => '9', 'dist_type' => 'Share', 'enable_min' => 1, 'enable_max' => 0, 'is_course' => 1, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Gradesheet Preparation', 'sub_head' => 'Theoretical', 'order_no' => '8.a', 'dist_type' => 'Share', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 1, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Gradesheet Preparation', 'sub_head' => 'Sessional', 'order_no' => '8.b', 'dist_type' => 'Share', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 1, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Gradesheet Scrutinizing', 'sub_head' => 'Theoretical', 'order_no' => '10.a', 'dist_type' => 'Share', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 1, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'GradeSheet Scrutinizing (Sessional)', 'sub_head' => 'Sessional', 'order_no' => '10.b', 'dist_type' => 'Share', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 1, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Prepared Computerized Result', 'sub_head' => null, 'order_no' => '8.d', 'dist_type' => 'Share', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 1, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Grade Sheeets/GPA Verification', 'sub_head' => null, 'order_no' => '8.c', 'dist_type' => 'Share', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 0, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Question', 'sub_head' => 'Stencil Cutting', 'order_no' => '12.a', 'dist_type' => 'Individual', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 1, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Question', 'sub_head' => 'Printing', 'order_no' => '12.b', 'dist_type' => 'Individual', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 0, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Question Typing,Sketching & Misc.', 'sub_head' => null, 'order_no' => '11', 'dist_type' => 'Individual', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 1, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Advisor Fee', 'sub_head' => null, 'order_no' => '13', 'dist_type' => 'Individual', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 0, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Verified of Final Graduation Result', 'sub_head' => null, 'order_no' => '16', 'dist_type' => 'Individual', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 0, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Sessional', 'sub_head' => 'Central Viva', 'order_no' => '7.e', 'dist_type' => 'Individual', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 0, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Sessional', 'sub_head' => 'Survey', 'order_no' => '7.f', 'dist_type' => 'Individual', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 0, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Project/Thesis', 'sub_head' => 'Initial Viva 2/1', 'order_no' => '6.c', 'dist_type' => 'Individual', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 0, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Project/Thesis', 'sub_head' => 'Examination', 'order_no' => '6.a', 'dist_type' => 'Individual', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 0, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Project/Thesis', 'sub_head' => 'Final Viva 2/1', 'order_no' => '6.d', 'dist_type' => 'Individual', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 0, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Project/Thesis', 'sub_head' => 'Supervising', 'order_no' => '6.b', 'dist_type' => 'Individual', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 0, 'is_student_count' => 1, 'marge_with' => null, 'status' => 1],
            ['head' => 'Course Co-ordinator Fee', 'sub_head' => null, 'order_no' => '14', 'dist_type' => 'Individual', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 0, 'is_student_count' => 0, 'marge_with' => null, 'status' => 1],
            ['head' => 'Chairman Fee', 'sub_head' => null, 'order_no' => '15', 'dist_type' => 'Individual', 'enable_min' => 0, 'enable_max' => 0, 'is_course' => 0, 'is_student_count' => 0, 'marge_with' => null, 'status' => 1],
        ]);
    }
}
