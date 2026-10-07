<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RoleNew;
use App\Models\RoleModule;
use App\Models\FeeType;
use App\Classes\CommonLibrary;
use commonHelper;
use App\Models\Activity;
use Session;
use Validator;
use Illuminate\Support\Facades\Hash;
use Image;
use File;
use DB;

class RoleNewControllor extends Controller
{

    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function index(){
        // $checkRoleView = commonHelper::getCheckPermission(1);
        // if($checkRoleView != null){
            $data['title'] = 'Manage Roles';
            $data['class'] = 'users';
            $data['table'] = 'Manage Roles';

            $data['role'] = RoleNew::get();
            return view('admin/roles_new/index' , $data);
        // }else{
        //     return view('admin/denied');
        // }
    }

    public function permission(){
            $data['title'] = 'Manage Roles';
            $data['class'] = 'users';
            $data['table'] = 'Role Permission Manager';

            $data['roles'] = RoleNew::orderby('role_id' , 'ASC')->get();
            $data['role_modules'] = RoleModule::orderby('main_title' , 'ASC')->get();
            return view('admin/roles_new/permissions' , $data);
        
    }

    public function ajaxPermission($role_id = ''){
        $data['role_id'] = $role_id;
        $role = RoleNew::where('role_id' , $role_id)->first();
        if(!$role){
            return response('Role not found.', 404);
        }
        $data['roles_title'] = $role->title;
        $data['role_modules'] = RoleModule::orderby('main_title' , 'ASC')->get();
        $data['role_permissions'] = DB::table('role_permissions')->where('role_id' , $role_id)->get();
        return view('admin/roles_new/ajaxPermissions' , $data);
    }

    public function assignRole($module_id = '' , $role_id = ''){
        $data['title'] = 'Assign Role';
        $data['class'] = 'users';
        $data['table'] = 'Assign Role';
        if(!RoleNew::where('role_id' , $role_id)->exists() || !RoleModule::where('id' , $module_id)->exists()){
            return response('not-found', 404);
        }
        DB::table('role_permissions')->updateOrInsert(
            ['role_id'=>$role_id,'module_id'=>$module_id],
            ['role_id'=>$role_id,'module_id'=>$module_id]
        );
        return 'success';
    }

    public function unassignRole($module_id = '' , $role_id = ''){
        $data['title'] = 'Assign Role';
        $data['class'] = 'users';
        $data['table'] = 'Assign Role';
        if(!RoleNew::where('role_id' , $role_id)->exists() || !RoleModule::where('id' , $module_id)->exists()){
            return response('not-found', 404);
        }
        $data['role_modules'] = DB::table('role_permissions')->where('module_id' , $module_id)->where('role_id' , $role_id)->delete();

        return 'success';
    }

    public function create(Request $request , $parent_id = ''){
        
        // $checkRoleAdd = commonHelper::getCheckPermission(2);
        // if($checkRoleAdd != null){
            $data['title'] = 'Add Roles';
            $data['class'] = 'users';
            $data['table'] = 'Add Roles';

            if ($request->isMethod('get')) {
                return view('admin/roles_new/create' , $data);
            }else{
                //return 'trtertert';
                $validator = Validator::make($request->all(), [
                                    'title' => 'required|unique:roles_new|max:255',
                                    'lable' => 'required|unique:roles_new|max:255',
                                ]);
                if ($validator->fails()) {
                    return redirect('admin/roles/add')
                                    ->withInput()
                                    ->withErrors($validator);
                }else{
                    $roles = new RoleNew();
                    $roles->title = $request->title;
                    $roles->lable = $request->lable;
                    $roles->save();
                    Session::flash('success_msg', 'roles has been added successfully');
                    return redirect('admin/roles/new');
                }
            }
        // }else{
        //     return view('admin/denied');
        // }
    }

    public function edit(Request $request , $role_id = ''){
        // $checkRoleEdit = commonHelper::getCheckPermission(3);
        // if($checkRoleEdit != null){
            $data['title'] = 'Edit Roles';
            $data['class'] = 'users';
            $data['table'] = 'Edit Roles';

            if ($request->isMethod('get')) {
                $data['role'] = RoleNew::where('role_id' , $role_id)->first();
                return view('admin/roles_new/edit' , $data);
            }else{
                if($request->title != $request->old_name){
                    $validator = Validator::make($request->all(), [
                                    'title' => 'required|unique:roles_new|max:255',
                                    //'lable' => 'required|unique:roles|max:255',

                                ]);
                }else{
                    $validator = Validator::make($request->all(), [
                                    //'code' => 'required|unique:locations|max:255',
                                ]);
                }
                if ($validator->fails()) {
                    return redirect('admin/roles/edit/'.$role_id)
                                    ->withInput()
                                    ->withErrors($validator);
                }else{

                    RoleNew::where('role_id', $role_id)
                            ->update([
                                'title' => $request->title,
                                 'lable' => $request->lable,
                            ]);
                    Session::flash('success_msg', 'roles has been updated successfully.');
                    return redirect('admin/roles/new');
                }
            }
        // }else{
        //     return view('admin/denied');
        // }
    }

    public function delete($role_id = '') {
        // $checkRoleDel = commonHelper::getCheckPermission(4);
        // if($checkRoleDel != null){
            if((int) $role_id === 1){
                Session::flash('error_msg', 'Super admin role cannot be deleted.');
                return redirect('admin/roles/new');
            }
            $delete = RoleNew::where('role_id' , $role_id)->delete();
            DB::table('role_permissions')->where('role_id' , $role_id)->delete();
            Session::flash('success_msg', 'role has been deleted successfully.');
            return redirect('admin/roles/new');
        // }else{
        //     return view('admin/denied');
        // }

    }

}
