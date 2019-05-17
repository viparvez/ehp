<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Permission;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Auth::user()->can('View-Permissions')) {
          return view('pages.550');
        }

        $permissions = Permission::where(['active' => '1', 'deleted' => '0'])->orderBy('created_at', 'ASC')->get();
        return view('pages.permissions',compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('Create-Permission')) {
          return response()->json(['error' => array('You do not have enough permission(s)')]);
        }

        $validator = Validator::make($request->all(), [

            'display_name' => 'required|max:100|unique:roles'

        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }
        /*return $request->all();*/
        DB::beginTransaction();

        try {

            $id = DB::table('permissions')->insertGetId(
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

            $name = preg_replace("/[\s_]/", "-", $request->display_name);

            Permission::where(['id' => $id])->update(
              [
                'name' => $name,
              ]
            );

            DB::commit();

            return response()->json(['success'=>'Added new records.']);
        
        } catch (\Exception $e) {

          DB::rollback();
          return response()->json(['error'=>array('Could not add permission')]);

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
        if (!Auth::user()->can('View-Permission-Details')) {
          return "<p style='color:red; text-align:center'>Permission Denied</p>";
        }

        $permission = Permission::where(['id' => $id])->first();

        $data = "
          <div class='box box-primary' style='font-weight:bold'>
            <div class='box-body box-profile'>

              <h3 class='profile-username text-center'>$permission->name</h3>

              <table class='table table-striped details-view'>
                <tr>
                    <td>Code</td>
                    <td>$permission->name</td>
                </tr>
                
                <tr>
                    <td>Name</td>
                    <td>$permission->display_name</td>
                </tr>
                
                <tr>
                    <td>Description</td>
                    <td>$permission->description</td>
                </tr>
              </table>

              <a href='#' onclick='getEditForm(".$permission->id.")' class='btn btn-primary btn-block'><b>Edit</b></a>
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
        if (!Auth::user()->can('Update-Permission')) {
          return "<p style='color:red; text-align:center'>Permission Denied</p>";
        }

        $permission = Permission::where(['id' => $id])->first();
        
        $data = "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editPermission' action='".route('permissions.update',$id)."' method='POST'>

            ".csrf_field()."

            <input type='hidden' name='_method' value='PUT'>

            <div class='form-group'>
             <label>Code</label>
             <input type='text' name='name' placeholder='Name' class='form-control' value='{$permission->name}' readonly required>
            </div>

            <div class='form-group'>
             <label>Name <code>*</code></label>
             <input type='text' name='display_name' placeholder='Name' class='form-control' value='{$permission->display_name}' required>
            </div>

            <div class='form-group'>
             <label>Description <code>*</code></label>
             <textarea name='description' class='form-control'>{$permission->description}</textarea>
            </div>

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
        if (!Auth::user()->can('Update-Permission')) {
          return response()->json(['error' => array('You do not have enough permission(s)')]);
        }
        
        $validator = Validator::make($request->all(), [

            'display_name' => 'required|max:100|unique:roles,display_name,'.$id,

        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        try {

            Permission::where(['id' => $id])->update(
                [
                    'display_name' => $request->display_name,
                    'description' => $request->description,
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d'),
                ]
            );
            
            return response()->json(['success'=>'Data updated.']);


        } catch (\Exception $e) {
            return response()->json(['error'=>array('Could not update. Encountered internel error.')]);
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
