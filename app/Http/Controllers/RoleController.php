<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    //showing all permission
    public function AllPermission()
    {

        $permissions = Permission::all();
        return view('access_control.permission.all_permission', compact('permissions'));

    } // End Method

    public function AddPermission()
    {
        return view('access_control.permission.add_permission');
    } // End Method


    public function StorePermission(Request $request)
    {

        //this method from spaite for create permission
        $role = Permission::create([
            'name' => $request->name,
            'group_name' => $request->group_name,

        ]);

        $notification = array(
            'message' => 'Permission Added Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('permission.all')->with($notification);

    }// End Method


    public function EditPermission($id)
    {

        $permission = Permission::findOrFail($id);
        return view('access_control.permission.edit_permission', compact('permission'));

    }// End Method

    public function UpdatePermission(Request $request)
    {

        $per_id = $request->id;

        Permission::findOrFail($per_id)->update([
            'name' => $request->name,
            'group_name' => $request->group_name,

        ]);

        $notification = array(
            'message' => 'Permission Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('permission.all')->with($notification);

    }// End Method


    public function DeletePermission($id)
    {

        Permission::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Permission Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);

    }// End Method




    //////////////////////////////////////////Role Work//////////////////////////////////////////
    public function AllRoles()
    {

        $roles = Role::all();
        return view('access_control.roles.all_roles', compact('roles'));

    }// End Method


    public function AddRoles()
    {

        return view('access_control.roles.add_roles');

    }// End Method


    public function StoreRoles(Request $request)
    {

        $role = Role::create([
            'name' => $request->name,
        ]);

        $notification = array(
            'message' => 'Role Added Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('roles.all')->with($notification);

    }// End Method



    public function EditRoles($id){
        $roles = Role::findOrFail($id);
        return view('access_control.roles.edit_roles',compact('roles'));

    }// End Method

    public function UpdateRoles(Request $request){

        $role_id = $request->id;

        Role::findOrFail($role_id)->update([
            'name' => $request->name,

        ]);

        $notification = array(
            'message' => 'Role Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('roles.all')->with($notification);

    }// End Method


    public function DeleteRoles($id){

        Role::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Role Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);

    }// End Method









    ///////////////////////////////////////////////////////////////////Role Permission/////////////////////////////////////////////////////

    public function AddRolesPermission(){
        //need all role and all permission
        $roles = Role::all();
        //$permissions = Permission::all();
        // Group all permissions by group_name(it will return group wise all permission)
        //dd(Permission::all()->groupBy('group_name'));
        /*Collection {
            'employee' => Collection [
                Permission { id: 1, name: 'employee.all', group_name: 'employee' },
                Permission { id: 2, name: 'employee.add', group_name: 'employee' },
            ],
            'user' => Collection [
                Permission { id: 3, name: 'user.view', group_name: 'user' },
                Permission { id: 4, name: 'user.reset', group_name: 'user' },
            ],
            'student' => Collection [
                Permission { id: 5, name: 'student.list', group_name: 'student' },
                Permission { id: 6, name: 'student.add', group_name: 'student' },
            ]
        }*/
        $permissions = Permission::all()->groupBy('group_name');
        return view('access_control.role_permission.add_roles_permission',compact('roles','permissions'));
    }// End Method

    public function StoreRolesPermission(Request $request){
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission' => 'required|array',
            'permission.*' => 'exists:permissions,id',
        ]);

        //dd($request->role_id);
        // Get the role
        $role = Role::findOrFail($request->role_id);

        /* Way1:
         // Convert IDs to names
         $permissionNames = Permission::whereIn('id', $request->permission)
             ->pluck('name')
             ->toArray();

         // Sync by names (Spatie needs names, not IDs)
         $role->syncPermissions($permissionNames);*/

        // Clear old permissions for this role(optional)
        $role->revokePermissionTo(Permission::all());

        // Assign new permissions using loop
        foreach ($request->permission as $permId) {
            $permission = Permission::findById($permId);
            $role->givePermissionTo($permission);
        }

        $notification = array(
            'message' => 'Role Permission Added Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('roles.permission.all')->with($notification);
    }
    public function AllRolesPermission(){
        $roles = Role::with('permissions')->get();
        //return($roles);
        return view('access_control.role_permission.all_roles_permission', compact('roles'));
    } // End Method



    public function EditRolePermissions($id){

        $role = Role::findOrFail($id);
        $permissions = Permission::all()->groupBy('group_name');
        return view('access_control.role_permission.edit_roles_permission',compact('role','permissions','permissions'));

    } // End Method

    public function UpdateRolePermission(Request $request){

        //return $request->id;
        $request->validate([
            'permission' => 'required|array',
            'permission.*' => 'exists:permissions,id',
        ]);
        $role = Role::findOrFail($request->id);
        $permissions = $request->permission;

        /* $role->syncPermissions($permissionModels); eqivalent to :
         DELETE FROM role_has_permissions WHERE role_id = ?
         INSERT INTO role_has_permissions (role_id, permission_id) VALUES (?, ?), (?, ?), ...*/

        if (!empty($permissions)) {
            $permissionModels = Permission::whereIn('id', $permissions)->get();
            $role->syncPermissions($permissionModels);
        }

        $notification = array(
            'message' => 'Role Permission Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('roles.permission.all')->with($notification);

    }// End Method



    public function DeleteRolesPermission($id){


        $role = Role::findOrFail($id);

        //return $role;
        Log::info('role', $role->toArray());
        if (!is_null($role)) {
            Log::info('inside role permission', $role->toArray());
            // Remove all permissions from this role
            $role->syncPermissions([]); // Equivalent to "detach all"
        }

        $notification = [
            'message' => 'All Permissions Removed from Role Successfully',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);

    }// End Method


}
