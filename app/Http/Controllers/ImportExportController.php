<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Faculty;
use App\Models\ImportHistory;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class ImportExportController extends Controller
{
    public function ImportAllTable(){
        $all_import_history = ImportHistory::latest()->get();
        return view('import_export.import_table',compact('all_import_history'));
    }

    public function ImportUserTable()
    {
        //return "Hi";
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get('https://ugr.duetbd.org/api/users');

        if ($response->failed()) {
            Log::error('UGR API fetch failed.');
            return response()->json(['error' => 'Failed to fetch data from UGR API'], 500);
        }

        $users = $response->json();

        //return $users;

        DB::beginTransaction();

        try {
            $inserted = 0;
            $updated = 0;

            foreach ($users as $userData) {
                $user = User::where('email', $userData['email'])->first();
                Log::info("User and UserData", [
                    'userData' => $userData,
                    'user' => $user ? $user->toArray() : 'no data',
                ]);
                if ($user) {
                    $user->update([
                        'name' => $userData['name'],
                        'phone' => $userData['phoneno'],
                        //'password' => $userData['password'], // already hashed
                    ]);
                    $updated++;
                    Log::info("User updated: {$user->email}");
                } else {
                    $user = User::create([
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'password' => $userData['password'], // already hashed
                        'phone' => $userData['phoneno'],
                    ]);
                    $inserted++;
                    //Log::info("User inserted: {$userData['email']}");
                    Log::info('User:', $user ? $user->toArray() : null);
                }


                // Assign 'Teacher' role if user has no role
                if ($user->roles()->count() === 0) {
                    $teacherRole = Role::where('name', 'Teacher')->first();
                    if ($teacherRole) {
                        $user->assignRole($teacherRole);
                        Log::info("Assigned 'Teacher' role to: {$user->email}");
                    } else {
                        Log::warning("Role 'Teacher' not found. Skipped role assignment for: {$user->email}");
                    }
                }
            }

            // Record the import history
            ImportHistory::create([
                'table_name' => 'users',
                'records_inserted' => $inserted,
                'records_updated' => $updated,
                'imported_by_name' => auth()->user()->name,
                'imported_by_email' => auth()->user()->email,
                'details' => 'User data imported from UGR API'
            ]);



            DB::commit();

            Log::info("User sync completed. Inserted: $inserted, Updated: $updated");

            $notification=array(
                "message"=>"User Import Success",
                "alert-type"=>"success"
            );
            return redirect()->back()->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('User sync failed: ' . $e->getMessage());

            $notification=array(
                "message"=>"User Import Failed",
                "alert-type"=>"error"
            );
            return redirect()->back()->with($notification);
        }
    }

    public function ImportFacultyTable()
    {
        //return "Hi";
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get('https://ugr.duetbd.org/api/faculties');

        if ($response->failed()) {
            Log::error('UGR API fetch failed.');
            return response()->json(['error' => 'Failed to fetch data from UGR API'], 500);
        }

        $faculties = $response->json();
       // return $faculties;

        //return $users;

        DB::beginTransaction();

        try {
            $inserted = 0;
            $updated = 0;

            foreach ($faculties as $singleFaculty) {
                $faculty = Faculty::where('id', $singleFaculty['id'])->first();


                if ($faculty) {
                    $faculty->update([
                        'facultyname' => $singleFaculty['facultyname'],
                        'shortname' => $singleFaculty['shortname'],
                    ]);
                    $updated++;
                    Log::info("User inserted: {$singleFaculty['facultyname']}");
                } else {
                    Faculty::create([
                        'id'=> $singleFaculty['id'],
                        'facultyname' => $singleFaculty['facultyname'],
                        'shortname' => $singleFaculty['shortname'],
                    ]);
                    $inserted++;
                    Log::info("Faculty inserted: {$singleFaculty['facultyname']}");
                }
            }

            // Record the import history
            ImportHistory::create([
                'table_name' => 'faculties',
                'records_inserted' => $inserted,
                'records_updated' => $updated,
                'imported_by_name' => auth()->user()->name,
                'imported_by_email' => auth()->user()->email,
                'details' => 'Faculty data imported from UGR API'
            ]);



            DB::commit();

            Log::info("Faculty sync completed. Inserted: $inserted, Updated: $updated");

            $notification=array(
                "message"=>"Faculty Import Success",
                "alert-type"=>"success"
            );
            return redirect()->back()->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Faculty sync failed: ' . $e->getMessage());

            $notification=array(
                "message"=>"Faculty Import Failed",
                "alert-type"=>"error"
            );
            return redirect()->back()->with($notification);
        }
    }//End Method

    public function ImportDepartmentTable()
    {
        //return "Hi";
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get('https://ugr.duetbd.org/api/departments');

        if ($response->failed()) {
            Log::error('UGR API fetch failed.');
            return response()->json(['error' => 'Failed to fetch data from UGR API'], 500);
        }

        $departments = $response->json();
         //return $departments;

        //return $users;

        DB::beginTransaction();

        try {
            $inserted = 0;
            $updated = 0;

            foreach ($departments as $singleDepartment) {
                $department = Department::where('id', $singleDepartment['id'])->first();


                if ($department) {
                    $department->update([
                        'shortname' => $singleDepartment['shortname'],
                        'fullname' => $singleDepartment['fullname'],
                        'faculty_id' => $singleDepartment['faculty_id'],
                    ]);
                    $updated++;
                    Log::info("Department inserted: {$singleDepartment['shortname']}");
                } else {
                    Department::create([
                        'id'=> $singleDepartment['id'],
                        'shortname' => $singleDepartment['shortname'],
                        'fullname' => $singleDepartment['fullname'],
                        'faculty_id' => $singleDepartment['faculty_id'],
                    ]);
                    $inserted++;
                    Log::info("Department inserted: {$singleDepartment['shortname']}");
                }
            }

            // Record the import history
            ImportHistory::create([
                'table_name' => 'departments',
                'records_inserted' => $inserted,
                'records_updated' => $updated,
                'imported_by_name' => auth()->user()->name,
                'imported_by_email' => auth()->user()->email,
                'details' => 'Department data imported from UGR API'
            ]);



            DB::commit();

            Log::info("Department sync completed. Inserted: $inserted, Updated: $updated");

            $notification=array(
                "message"=>"Department Import Success",
                "alert-type"=>"success"
            );
            return redirect()->back()->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Department sync failed: ' . $e->getMessage());

            $notification=array(
                "message"=>"Department Import Failed",
                "alert-type"=>"error"
            );
            return redirect()->back()->with($notification);
        }
    }//End Method

    public function ImportDesignationTable()
    {
        //return "Hi";
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get('https://ugr.duetbd.org/api/designations');

        if ($response->failed()) {
            Log::error('UGR API fetch failed.');
            return response()->json(['error' => 'Failed to fetch data from UGR API'], 500);
        }

        $designations = $response->json();
        //return $designations;

        //return $users;

        DB::beginTransaction();

        try {
            $inserted = 0;
            $updated = 0;

            foreach ($designations as $singleDesignation) {
                $designation = Designation::where('id', $singleDesignation['id'])->first();


                if ($designation) {
                    $designation->update([
                        'designation' => $singleDesignation['designation'],
                    ]);
                    $updated++;
                    Log::info("Department inserted: {$singleDesignation['designation']}");
                } else {
                    Designation::create([
                        'id'=> $singleDesignation['id'],
                        'designation' => $singleDesignation['designation'],
                    ]);
                    $inserted++;
                    Log::info("Department inserted: {$singleDesignation['designation']}");
                }
            }

            // Record the import history
            ImportHistory::create([
                'table_name' => 'designations',
                'records_inserted' => $inserted,
                'records_updated' => $updated,
                'imported_by_name' => auth()->user()->name,
                'imported_by_email' => auth()->user()->email,
                'details' => 'Designation data imported from UGR API'
            ]);



            DB::commit();

            Log::info("Designation sync completed. Inserted: $inserted, Updated: $updated");

            $notification=array(
                "message"=>"Designation Import Sucess",
                "alert-type"=>"success"
            );
            return redirect()->back()->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Designation sync failed: ' . $e->getMessage());

            $notification=array(
                "message"=>"Designation Import Failed",
                "alert-type"=>"error"
            );
            return redirect()->back()->with($notification);
            //return response()->json(['error' => 'Designation sync failed.'], 500);
        }
    }//End Method

    //Teacher Import
    public function ImportTeacherTable()
    {
       // return 'hi';
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get('https://ugr.duetbd.org/api/teachers');

        if ($response->failed()) {
            Log::error('UGR teacher API fetch failed.');
            return response()->json(['error' => 'Failed to fetch data from UGR API'], 500);
        }

        //$teachers = $response->json();
        $teachers = json_decode($response->body());

       // return $teachers;
        DB::beginTransaction();

        try {
            $inserted = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($teachers as $teacherData) {
                $userEmail = $teacherData->user->email ?? null;
                Log::info('User email: ' . $userEmail);
                if (!$userEmail) {
                    Log::warning("Skipped teacher with no user email.");
                    $skipped++;
                    continue;
                }

                // Check if user email exist in User Table
                $user = User::where('email', $userEmail)->first();

                if (!$user) {
                    Log::info("User/User Email Not Exist For This Teacher in User Table(ExamBill): {$teacherData->teachername}");
                    $skipped++;
                    continue;
                } else {
                    // User exists — check if teacher;Teacher table user_id==User table id
                    //than means teacher and user exist already '
                    //we need to update

                    /*SELECT * FROM teachers
                    JOIN users ON teachers.user_id = users.id
                    WHERE users.email = 'teacher@example.com($teacherData->user->email)'*/
                    $teacher =Teacher::whereHas('user', function ($query) use ($teacherData) {
                        $query->where('email', $teacherData->user->email);
                    })->where('user_id', $user->id)->first();

                    if ($teacher) {
                        // Update teacher
                        $teacher->update([
                            'teachername'    => $teacherData->teachername,
                            'specialization' => $teacherData->specialization,
                            'availability'   => $teacherData->availability,
                            'phoneno'        => $teacherData->phoneno,
                            'photo'          => $teacherData->photo,
                            'preaddress'     => $teacherData->preaddress,
                            'peraddress'     => $teacherData->peraddress,
                            'designation_id' => $teacherData->designation_id,
                            'department_id'  => $teacherData->department_id,
                        ]);

                        $updated++;
                        Log::info("Teacher updated: {$teacherData->teachername}");
                    } else {
                        // Create teacher for existing user
                        Teacher::create([
                            'teachername'    => $teacherData->teachername,
                            'specialization' => $teacherData->specialization,
                            'availability'   => $teacherData->availability,
                            'phoneno'        => $teacherData->phoneno,
                            'photo'          => $teacherData->photo,
                            'preaddress'     => $teacherData->preaddress,
                            'peraddress'     => $teacherData->peraddress,
                            'designation_id' => $teacherData->designation_id,
                            'department_id'  => $teacherData->department_id,
                            'user_id'        => $user->id,
                        ]);

                        $inserted++;
                        Log::info("Teacher created for existing user: {$teacherData->teachername}");
                    }
                }
            }

            // Log import history
            ImportHistory::create([
                'table_name' => 'teachers',
                'records_inserted' => $inserted,
                'records_updated' => $updated,
                'records_skipped' => $skipped,
                'imported_by_name' => auth()->user()->name,
                'imported_by_email' => auth()->user()->email,
                'details' => 'Teacher data imported from UGR API'
            ]);

            DB::commit();

            Log::info("Teacher import done. Inserted: $inserted, Updated: $updated, Skipped: $skipped");

            $notification=array(
                "message"=>"Teacher Import Success",
                "alert-type"=>"success"
            );
            return redirect()->back()->with($notification);

           // return redirect()->back()->with('success', "Imported: $inserted, Updated: $updated, Skipped: $skipped");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Teacher import failed: ' . $e->getMessage());

            $notification=array(
                "message"=>"Teacher Import Failed",
                "alert-type"=>"error"
            );
            return redirect()->back()->with($notification);
            //return response()->json(['error' => 'Teacher import failed.'], 500);
        }
    }

}
