<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Spatie\Permission\Models\Role;

class RoleAssignmentController extends Controller
{
    public function AllRoleAssignments(){
        //$allUsers = User::latest()->get();
        $allUsers = User::with('teacher.department')->get()->sortBy(function ($user) {
            return optional(optional($user->teacher)->department)->id === 2 ? 0 : 1;
        });
        return view('access_control.role_assignment.all_user',compact('allUsers'));
    }

    public function AddRoleAssignments(){
        $roles = Role::all();
        return view('access_control.role_assignment.add_user',compact('roles'));
    }



    public function EditRoleAssignments($id){

        $roles = Role::all();
        $user = User::findOrFail($id);
        return view('access_control.role_assignment.edit_user',compact('roles','user'));

    }// End Method


    public function UpdateRoleAssignments(Request $request)
    {
        //return $request;
        try {
            // Validate request data
            $validated = $request->validate([
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,id',
            ]);

            // Find user
            $user = User::findOrFail($request->id);

            /*// Sync roles (using sync instead of detach+assign for better handling)
            $user->syncRoles($request->roles);*/
            if ($request->roles) {
                /*$role = Role::findById($request->roles);
                $user->syncRoles([$role]);*/
                $roles = Role::whereIn('id', $request->roles)->get();
                $user->syncRoles($roles); // Pass Role instances instead of IDs
            }

            $notification = [
                'message' => 'User Role Updated Successfully',
                'alert-type' => 'success'
            ];

            return redirect()->route('role.assignments.all')->with($notification);

        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage(), [
                'user_id' => $request->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            $notification = [
                'message' => 'Error Updating User: ' . $e->getMessage(),
                'alert-type' => 'error'
            ];

            return redirect()->back()->withInput()->with($notification);
        }
    }



    public function DeleteRoleAssignments($id){

        try {
            $user = User::findOrFail($id);

            // Remove all roles (Spatie)
            $user->roles()->detach();

            $notification = [
                'message' => 'User Role Deleted Successfully',
                'alert-type' => 'success'
            ];

            return redirect()->route('role.assignments.all')->with($notification);

        } catch (\Exception $e) {
            $notification = [
                'message' => 'Error Deleting User: ' . $e->getMessage(),
                'alert-type' => 'error'
            ];

            return redirect()->back()->with($notification);
        }

    }// End Method
}
