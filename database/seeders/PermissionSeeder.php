<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User
            ['name' => 'user.logout', 'group_name' => 'user'],
            ['name' => 'user.profile', 'group_name' => 'user'],
            ['name' => 'user.profile.store', 'group_name' => 'user'],
            ['name' => 'user.password.change', 'group_name' => 'user'],
            ['name' => 'user.password.update', 'group_name' => 'user'],

            // Import/Export
            ['name' => 'import.table.all', 'group_name' => 'import'],
            ['name' => 'import.table.users', 'group_name' => 'import'],
            ['name' => 'import.table.faculties', 'group_name' => 'import'],
            ['name' => 'import.table.departments', 'group_name' => 'import'],
            ['name' => 'import.table.designations', 'group_name' => 'import'],
            ['name' => 'import.table.teachers', 'group_name' => 'import'],

            // Committee Input Regular
            ['name' => 'committee.input.regular.session', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.regular.session.form', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.regular.examination.moderation.committee.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.regular.examiner.paper.setter.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.regular.class.test.teacher.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.regular.sessional.course.teacher.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.regular.scrutinizers.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.regular.theory.grade.sheet.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.sessional.grade.sheet.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.scrutinizers.theory.grade.sheet.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.scrutinizers.sessional.grade.sheet.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.prepare.computerized.result.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.verified.computerized.grade.sheet.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.conducted.central.oral.exam.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.stencil.cutting.committee.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.printing.question.committee.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.comparison.committee.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.advisor.student.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.verified.final.graduation.result.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.involved.survey.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.conducted.preliminary.viva.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.examined.thesis.project.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.conducted.oral.examination.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.supervised.thesis.project.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.honorarium.coordinator.store', 'group_name' => 'committee_input_regular'],
            ['name' => 'committee.input.honorarium.chairman.store', 'group_name' => 'committee_input_regular'],

            // Committee Input Review
            ['name' => 'committee.input.review.session', 'group_name' => 'committee_input_review'],
            ['name' => 'committee.input.review.session.form', 'group_name' => 'committee_input_review'],
            ['name' => 'committee.input.review.examination.moderation.committee.store', 'group_name' => 'committee_input_review'],
            ['name' => 'committee.input.review.examiner.paper.setter.store', 'group_name' => 'committee_input_review'],
            ['name' => 'committee.input.review.scrutinizers.store', 'group_name' => 'committee_input_review'],
            ['name' => 'committee.input.review.theory.grade.sheet.store', 'group_name' => 'committee_input_review'],
            ['name' => 'committee.input.review.scrutinizers.theory.grade.sheet.store', 'group_name' => 'committee_input_review'],
            ['name' => 'committee.input.review.stencil.cutting.committee.store', 'group_name' => 'committee_input_review'],
            ['name' => 'committee.input.review.printing.question.committee.store', 'group_name' => 'committee_input_review'],
            ['name' => 'committee.input.review.comparison.committee.store', 'group_name' => 'committee_input_review'],
            ['name' => 'committee.input.review.chairman.coordinator.store', 'group_name' => 'committee_input_review'],

            // Report Regular
            ['name' => 'report.regular.session', 'group_name' => 'report_regular'],
            ['name' => 'report.regular.generate', 'group_name' => 'report_regular'],

            // Report Review
            ['name' => 'report.review.session', 'group_name' => 'report_review'],
            ['name' => 'report.review.generate', 'group_name' => 'report_review'],

            // Teacher
            ['name' => 'teacher.all', 'group_name' => 'teacher'],
            ['name' => 'teacher.add', 'group_name' => 'teacher'],
            ['name' => 'teacher.store', 'group_name' => 'teacher'],
            ['name' => 'teacher.edit', 'group_name' => 'teacher'],
            ['name' => 'teacher.update', 'group_name' => 'teacher'],
            ['name' => 'teacher.delete', 'group_name' => 'teacher'],

            // Employee
            ['name' => 'employee.all', 'group_name' => 'employee'],
            ['name' => 'employee.add', 'group_name' => 'employee'],
            ['name' => 'employee.store', 'group_name' => 'employee'],
            ['name' => 'employee.edit', 'group_name' => 'employee'],
            ['name' => 'employee.update', 'group_name' => 'employee'],
            ['name' => 'employee.delete', 'group_name' => 'employee'],

            // Permission
            ['name' => 'permission.all', 'group_name' => 'permission'],
            ['name' => 'permission.add', 'group_name' => 'permission'],
            ['name' => 'permission.store', 'group_name' => 'permission'],
            ['name' => 'permission.edit', 'group_name' => 'permission'],
            ['name' => 'permission.update', 'group_name' => 'permission'],
            ['name' => 'permission.delete', 'group_name' => 'permission'],

            // Role
            ['name' => 'roles.all', 'group_name' => 'role'],
            ['name' => 'roles.add', 'group_name' => 'role'],
            ['name' => 'roles.store', 'group_name' => 'role'],
            ['name' => 'roles.edit', 'group_name' => 'role'],
            ['name' => 'roles.update', 'group_name' => 'role'],
            ['name' => 'roles.delete', 'group_name' => 'role'],

            // Role Permissions
            ['name' => 'roles.permissions.add', 'group_name' => 'role_permission'],
            ['name' => 'role.permission.store', 'group_name' => 'role_permission'],
            ['name' => 'roles.permission.all', 'group_name' => 'role_permission'],
            ['name' => 'role.permission.edit', 'group_name' => 'role_permission'],
            ['name' => 'role.permission.update', 'group_name' => 'role_permission'],
            ['name' => 'role.permission.delete', 'group_name' => 'role_permission'],

            // Role Assignment
            ['name' => 'role.assignments.all', 'group_name' => 'role_assignment'],
            ['name' => 'role.assignments.add', 'group_name' => 'role_assignment'],
            ['name' => 'role.assignments.store', 'group_name' => 'role_assignment'],
            ['name' => 'role.assignments.edit', 'group_name' => 'role_assignment'],
            ['name' => 'role.assignments.update', 'group_name' => 'role_assignment'],
            ['name' => 'role.assignments.delete', 'group_name' => 'role_assignment'],

            // Rate Head
            ['name' => 'rate_head.all', 'group_name' => 'rate_head'],
            ['name' => 'rate_head.add', 'group_name' => 'rate_head'],
            ['name' => 'rate_head.store', 'group_name' => 'rate_head'],
            ['name' => 'rate_head.edit', 'group_name' => 'rate_head'],
            ['name' => 'rate_head.update', 'group_name' => 'rate_head'],
            ['name' => 'rate_head.delete', 'group_name' => 'rate_head'],

            // Github Deploy
            ['name' => 'github_deploy.view', 'group_name' => 'github_deploy'],
            ['name' => 'github_deploy.pull', 'group_name' => 'github_deploy'],


            // menu
            ['name' => 'import.menu', 'group_name' => 'menu'],
            ['name' => 'committee_input.menu', 'group_name' => 'menu'],
            ['name' => 'committee_record.menu', 'group_name' => 'menu'],
            ['name' => 'teacher.menu', 'group_name' => 'menu'],
            ['name' => 'employee.menu', 'group_name' => 'menu'],
            ['name' => 'report.menu', 'group_name' => 'menu'],
            ['name' => 'role_permission.menu', 'group_name' => 'menu'],
            ['name' => 'role_assign.menu', 'group_name' => 'menu'],
            ['name' => 'system_setting.menu', 'group_name' => 'menu'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                [
                    'group_name' => $permission['group_name'],
                    'created_at' => Carbon::now(),
                ]
            );
        }
    }
}
