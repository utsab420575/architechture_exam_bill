<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Teacher;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Spatie\Permission\Models\Role;

class TeacherController extends Controller
{
    public function AddTeacher(){
        $designations=Designation::all();
        $departments=Department::all();
        $universities=University::all();
        return view('teacher.add_teacher', compact('designations', 'departments', 'universities'));
    }

    public function StoreTeacher(Request $request)
    {
        Log::info('StoreTeacher called', ['user_id' => auth()->id()]);

        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'phone' => [
                'required',
                'regex:/^(\+8801|8801|01)[0-9]{9}$/',
                Rule::unique('users', 'phone'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'address' => 'nullable|string|max:500',
            'designation' => 'required|exists:designations,id',
            'department' => 'required|exists:departments,id',
            'university' => 'required|exists:universities,id', // <-- NEW
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Create user
            $user = new User();
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->email = $request->email;
            $user->password = Hash::make('12345678');

            // Handle image
            if ($request->file('photo')) {
                $recive_image = $request->file('photo');
                $name_gen = hexdec(uniqid()) . '.' . $recive_image->getClientOriginalExtension();

                $manager = new ImageManager(new Driver());
                $image = $manager->read($recive_image)->resize(300, 300);

                $path = public_path('upload/user_image/');
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                $image->toJpeg()->save($path . $name_gen);
                $user->photo = 'upload/user_image/' . $name_gen;
            }

            $teacherRole = Role::where('name', 'Teacher')->first();
            if ($teacherRole) {
                $user->assignRole($teacherRole);
                Log::info("Assigned 'Teacher' role to: {$user->email}");
            } else {
                Log::warning("Role 'Teacher' not found. Skipped role assignment for: {$user->email}");
            }

            $user->save();
            Log::info('User saved', ['user_id' => $user->id]);

            // Create teacher
            $teacher = new Teacher();
            $teacher->teachername     = $request->name;
            $teacher->phoneno         = $request->phone;
            $teacher->preaddress      = $request->address ?? null;
            $teacher->designation_id  = $request->designation;
            $teacher->department_id   = $request->department;
            $teacher->university_id   = $request->university;   // <-- NEW
            $teacher->user_id         = $user->id;
            $teacher->photo           = $user->photo ?? null;

            $teacher->save();
            Log::info('Teacher saved', ['teacher_id' => $teacher->id]);

            DB::commit();

            return redirect()->route('dashboard')->with([
                'message' => 'Teacher Added Successfully',
                'alert-type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('StoreTeacher failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with([
                'message' => 'Something went wrong. Teacher not added.',
                'alert-type' => 'error'
            ]);
        }
    }



    public function AllTeacher(){
        $teachers = Teacher::with('department')
            ->orderByRaw('department_id = 2 DESC') // Architecture department (id=2) first
            ->orderBy('department_id')             // Then sort others by department if needed
            ->get();
        return view('teacher.all_teacher',compact('teachers'));
    }

    public function EditTeacher($id){
        $teacher=Teacher::find($id);
        $designations=Designation::all();
        $departments=Department::all();
        $universities=University::all();
        return view('teacher.edit_teacher',compact('teacher','designations','departments','universities'));
    }

    public function UpdateTeacher(Request $request)
    {
        $teacher = Teacher::findOrFail($request->id);

        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'phone' => [
                'required',
                'regex:/^(\+8801|8801|01)[0-9]{9}$/',
                Rule::unique('users', 'phone')->ignore($teacher->user_id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($teacher->user_id),
            ],
            'address' => 'nullable|string|max:500',
            'designation' => 'required|exists:designations,id',
            'department' => 'required|exists:departments,id',
            'university' => 'required|exists:universities,id', // <-- NEW
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $user = $teacher->user;

            if ($user) {
                $user->name  = $request->name;
                $user->email = $request->email;
                $user->phone = $request->phone;

                if ($request->file('photo')) {
                    if ($user->photo && file_exists(public_path($user->photo))) {
                        unlink(public_path($user->photo));
                    }

                    $recive_image = $request->file('photo');
                    $name_gen = hexdec(uniqid()) . '.' . $recive_image->getClientOriginalExtension();

                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($recive_image)->resize(300, 300);

                    $path = public_path('upload/user_image/');
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    $image->toJpeg()->save($path . $name_gen);
                    $user->photo = 'upload/user_image/' . $name_gen;
                }

                $user->save();
            }

            $teacher->preaddress     = $request->address;
            $teacher->designation_id = $request->designation;
            $teacher->department_id  = $request->department;
            $teacher->university_id  = $request->university; // <-- NEW
            $teacher->save();

            DB::commit();

            return redirect()->route('teacher.all')->with([
                'message' => 'Teacher Updated successfully',
                'alert-type' => 'success',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with([
                'message' => 'Something went wrong: ' . $e->getMessage(),
                'alert-type' => 'error',
            ]);
        }
    }


    public function DeleteTeacher($id)
    {
        $teacher = Teacher::find($id);

        if (!$teacher) {
            return redirect()->route('teacher.all')->with([
                'message' => 'Teacher not found.',
                'alert-type' => 'error'
            ]);
        }

        DB::beginTransaction();

        try {
            $user = $teacher->user;

            // First delete teacher (child)
            $teacher->delete();

            // Delete user's photo if exists
            if ($user && $user->photo && file_exists(public_path($user->photo))) {
                unlink(public_path($user->photo));
            }

            // Then delete user (parent)
            if ($user) {
                $user->delete();
            }

            DB::commit();

            return redirect()->route('teacher.all')->with([
                'message' => 'Delete Successfully',
                'alert-type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Optional: Log error
            Log::error('Teacher delete failed', ['error' => $e->getMessage()]);

            return redirect()->route('teacher.all')->with([
                'message' => 'Delete failed: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }



}
