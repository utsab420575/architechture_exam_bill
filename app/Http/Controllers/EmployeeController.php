<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function AddEmployee(){
        $designations = Designation::whereIn('designation', ['Officer', 'Staff','Admin'])->get();
        $departments=Department::all();
        return view('employee.add_employee',compact('designations','departments'));
    }

    public function Storeemployee(Request $request)
    {
        Log::info('StoreEmployee called', ['user_id' => auth()->id()]);

        // Validate request
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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Create user
        $user = new User();
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->password= Hash::make('12345678');

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

        $employeeRole = Role::where('name', 'Employee')->first();
        if ($employeeRole) {
            $user->assignRole($employeeRole);
            Log::info("Assigned 'Teacher' role to: {$user->email}");
        } else {
            Log::warning("Role 'Teacher' not found. Skipped role assignment for: {$user->email}");
        }


        $user->save();
        Log::info('User saved', ['user_id' => $user->id]);

        // Create teacher
        $employee = new Employee();
        $employee->employeename = $request->name;
        $employee->phoneno = $request->phone;
        $employee->preaddress = $request->address ?? null;
        $employee->designation_id = $request->designation;
        $employee->department_id = $request->department;
        $employee->user_id = $user->id;
        $employee->photo = $user->photo ?? null;

        $employee->save();

        Log::info('Exmployee saved', ['employee_id' => $employee->id]);
        $notifications=array(
            "message"=>'Employee Added Successfully',
            "alert-type"=>'success'
        );


        return redirect()->route('dashboard')->with($notifications);
    }

    public function AllEmployee(){
        $employees = Employee::with('department')
            ->orderByRaw('department_id = 2 DESC') // Architecture department (id=2) first
            ->orderBy('department_id')             // Then sort others by department if needed
            ->get();
        return view('employee.all_employee',compact('employees'));
    }
    public function Editemployee($id){
        $employee=Employee::find($id);
        $designations=Designation::all();
        $departments=Department::all();
        return view('employee.edit_employee',compact('employee','designations','departments'));
    }

    public function DeleteEmployee($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return redirect()->route('teacher.all')->with([
                'message' => 'Teacher not found.',
                'alert-type' => 'error'
            ]);
        }

        DB::beginTransaction();

        try {
            $user = $employee->user;

            // First delete teacher (child)
            $employee->delete();

            // Delete user's photo if exists
            if ($user && $user->photo && file_exists(public_path($user->photo))) {
                unlink(public_path($user->photo));
            }

            // Then delete user (parent)
            if ($user) {
                $user->delete();
            }

            DB::commit();

            return redirect()->route('employee.all')->with([
                'message' => 'Delete Successfully',
                'alert-type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Optional: Log error
            Log::error('Teacher delete failed', ['error' => $e->getMessage()]);

            return redirect()->route('employee.all')->with([
                'message' => 'Delete failed: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    public function UpdateEmployee(Request $request)
    {
        $employee = Employee::findOrFail($request->id);

        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'phone' => [
                'required',
                'regex:/^(\+8801|8801|01)[0-9]{9}$/',
                Rule::unique('users', 'phone')->ignore($employee->user_id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($employee->user_id),
            ],
            'address' => 'nullable|string|max:500',
            'designation' => 'required|exists:designations,id',
            'department' => 'required|exists:departments,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $user = $employee->user;

            if ($user) {
                $user->name = $request->name;
                $user->email = $request->email;
                $user->phone = $request->phone;

                if ($request->file('photo')) {
                    // Delete old photo
                    if ($user->photo && file_exists(public_path($user->photo))) {
                        unlink(public_path($user->photo));
                    }

                    $recive_image = $request->file('photo');
                    $name_gen = hexdec(uniqid()) . '.' . $recive_image->getClientOriginalExtension();

                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($recive_image);
                    $image->resize(300, 300);

                    $path = public_path('upload/user_image/');
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    $image->toJpeg()->save($path . $name_gen);
                    $user->photo = 'upload/user_image/' . $name_gen;
                }

                $user->save();
            }

            $employee->preaddress = $request->address;
            $employee->designation_id = $request->designation;
            $employee->department_id = $request->department;
            $employee->save();

            DB::commit();

            return redirect()->route('employee.all')->with([
                'message' => 'Employee Updated successfully',
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
}
