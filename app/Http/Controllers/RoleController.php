<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Role;
use App\Permission;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;


class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      if (!Auth::user()->can('View-Roles')) {
        return view('pages.550');
      }

      $roles = Role::where(['active' => '1', 'deleted' => '0'])->whereNotIn('name',['Admin'])->orderBy('created_at', 'ASC')->get();
      $role_groups = DB::select(
        "
          SELECT
            count(id) AS count,
            `group`
          FROM
            permissions
          GROUP BY
            `group`
          ORDER BY
            count DESC
        "
      );

      $permissions = Permission::all();
      return view('pages.roles',compact('roles','permissions','role_groups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      if (!Auth::user()->can('Create-Role')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }

        $validator = Validator::make($request->all(), [
            'display_name' => 'required|max:100|unique:roles',
            'permission' => 'required',

        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        DB::beginTransaction();

        try {

            $id = DB::table('roles')->insertGetId(
                [
                    'name' => time(),
                    'display_name' => $request->display_name,
                    'description' => $request->description,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                ]
            );

            foreach ($request->permission as $key => $value) {
                DB::table('permission_role')->insert(
                    [
                        'permission_id' => $value,
                        'role_id' => $id,
                    ]
                );
            }
            
            $name = preg_replace("/[\s_]/", "-", $request->display_name);

            Role::where(['id' => $id])->update(
              [
                'name' => $name,
              ]
            );

            DB::commit();

            return response()->json(['success'=>'Added new records.']);
        
        } catch (\Exception $e) {

          DB::rollback();
          return response()->json(['error'=>array('Could not add role')]);

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      if (!Auth::user()->can('View-Role-Details')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

        $role = Role::where(['id' => $id])->first();

        $permissions = Permission::all();

        $rolePermissions = DB::table("permission_role")
                        ->where("role_id",$id)
                        ->pluck('permission_id')
                        ->toArray();

        $permissionsAttached = "";

        foreach($permissions as $key => $value) {
            $permissionsAttached .= in_array($value->id, $rolePermissions) ? "$value->display_name | " : ''." &nbsp";
        }

        $data = "
          <div class='box box-primary' style='font-weight:bold'>
            <div class='box-body box-profile'>

              <h3 class='profile-username text-center'>$role->name</h3>

              <table class='table table-striped details-view'>
                <tr>
                    <td>Code</td>
                    <td>$role->name</td>
                </tr>
                
                <tr>
                    <td>Name</td>
                    <td>$role->display_name</td>
                </tr>
                
                <tr>
                    <td>Permissions</td>
                    <td>".$permissionsAttached."</td>
                </tr>

                <tr>
                    <td>Description</td>
                    <td>$role->description</td>
                </tr>
              </table>

              <a href='#' onclick='getEditForm(".$role->id.")' class='btn btn-primary btn-block'><b>Edit</b></a>
            </div>
          </div>
        ";

        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

      if (!Auth::user()->can('Update-Role')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

        $role = Role::where(['id' => $id])->first();

        $permissions = Permission::all();

        $rolePermissions = DB::table("permission_role")
                        ->where("role_id",$id)
                        ->pluck('permission_id')
                        ->toArray();
                        
        $permissionsAttached = "";

        $roles = Role::where(['active' => '1', 'deleted' => '0'])->orderBy('created_at', 'ASC')->get();
        $role_groups = DB::select(
          "
            SELECT
              count(id) AS count,
              `group`
            FROM
              permissions
            GROUP BY
              `group`
            ORDER BY
              count DESC
          "
        );

        $fields = view('includes.role_update',compact('roles','role_groups','permissions','rolePermissions'));
        
        $data = "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editRole' action='".route('roles.update',$id)."' method='POST'>

            ".csrf_field()."

            <input type='hidden' name='_method' value='PUT'>

            <div class='form-group'>
             <label>Code </label>
             <input type='text' name='name' placeholder='Name' class='form-control' value='{$role->name}' readonly required>
            </div>

            <div class='form-group'>
             <label>Name <code>*</code></label>
             <input type='text' name='display_name' placeholder='Name' class='form-control' value='{$role->display_name}' required>
            </div>

            <div class='form-group'>
             <label>Description <code>*</code></label>
             <textarea name='description' class='form-control'>{$role->description}</textarea>
            </div>

            <fieldset class='scheduler-border'>
              <legend>Permissions <code>*</code></legend>
                ".$fields."
            </fieldset>

            <button class='btn btn-block btn-success btn-sm' id='submitEdit' type='submit'>SAVE</button>
            <button class='btn btn-block btn-success btn-sm' id='loadingEdit' style='display: none' disabled=''>Working...</button>

          </form>
        ";

        return $data;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

      if (!Auth::user()->can('Update-Role')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }

        $validator = Validator::make($request->all(), [

            'display_name' => 'required|max:100|unique:roles,display_name,'.$id,

        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        try {

            Role::where(['id' => $id])->update(
                [
                    'display_name' => $request->display_name,
                    'description' => $request->description,
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d'),
                ]
            );

            DB::table('permission_role')->where('role_id',$id)->delete();

            foreach ($request->permission as $key => $value) {
                DB::table('permission_role')->insert(
                    [
                        'permission_id' => $value,
                        'role_id' => $id,
                    ]
                );
            }
            
            return response()->json(['success'=>'Data updated.']);


        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
