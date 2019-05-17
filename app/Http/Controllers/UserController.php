<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\Facility;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;
use App\Userfacilityaccess;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      if (!Auth::user()->can('View-Users')) {
        return view('pages.550');
      }
        $users = User::where(['deleted' => '0'])->orderBy('name', 'ASC')->get();
        $roles = Role::all();
        $facilities = Facility::where(['deleted' => '0', 'active' => '1'])->get();
        return view('pages.users',compact('users','roles','facilities'));
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
      if (!Auth::user()->can('Create-User')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }
        $validator = Validator::make($request->all(), [
            'name' => 'required|Regex:/^[\D]+$/i|max:70',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
            'img_url' => 'image|mimes:jpeg,png,jpg|max:2048',
            'designation' => 'required',
            'company' => 'required',
            'phone' => 'required',
            'role' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        DB::beginTransaction();

        try {

            $id = DB::table('users')->insertGetId(
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password'   => bcrypt($request->password),
                    'active' => '1',
                    'deleted' => '0',
                    'designation' => $request->designation,
                    'project' => $request->project,
                    'company' => $request->company,
                    'phone' => $request->phone,
                    'img_url' => url('/')."/public/images/users/demo.jpg",
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                ]
            );

            foreach ($request->role as $key => $value) {
                DB::table('role_user')->insert([
                    'user_id' => $id,
                    'role_id' => $value,
                ]);
            }
      
            if(!empty($request->facility)){
              foreach ($request->facility as $key => $value) {
                DB::table('userfacilityaccesses')->insert(
                  [
                    'user_id' => $id,
                    'facility_id' => $value,
                    'createdbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                  ]
                );
              }
            }


            if($request->hasFile('img_url')) {
              
               $file = $request->file('img_url');
               $name = $id.'.'.$file->getClientOriginalExtension();
               $file->move(public_path().'/images/users', $name);

            } else {
                $name = 'demo.jpg';
            }

            User::where(['id' => $id])->update(
                [
                    'img_url' => url('/')."/public/images/users/".$name,
                ]
            );

            DB::table('useractions')->insert(
                [
                    'user_id' => $id,
                    'action' => 'Created',
                    'deleted' => '0',
                    'createdbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                ]
            );


            DB::commit();

            return response()->json(['success'=>'Added new records.']);            
   
        } catch (\Exception $e) {

          DB::rollback();
          return response()->json(['error'=>array('Could not crete user')]);

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
      if (!Auth::user()->can('View-User-Details')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

        $user = User::where(['id' => $id])->first();

        $userRoles = $user->roles->pluck('display_name')->toArray();

        $roles = "";

        foreach ($userRoles as $role) {
            $roles .= $role."</br>";
        }

        $data = "
          <div class='box box-primary' style='font-weight:bold'>
            <div class='box-body box-profile'>

            <img class='profile-user-img img-responsive img-responsive' src='$user->img_url' alt='Picture'>

            <h3 class='profile-username text-center'>".$user->name."</h3>

              <table class='table table-striped details-view'>
                  <tr>
                    <td>Name</td><td>".$user->name."</td>
                  </tr>
                  <tr>
                    <td>Email</td><td>".$user->email."</td>
                  </tr>
                  <tr>
                    <td>Company</td><td>".$user->company."</td>
                  </tr>
                  <tr>
                    <td>Project</td><td>".$user->project."</td>
                  </tr>
                  <tr>
                    <td>Designation</td><td>".$user->designation."</td>
                  </tr>
                  <tr>
                    <td>Phone</td><td>".$user->phone."</td>
                  </tr>
                  <tr>
                    <td>Role(s)</td><td>".$roles."</td>
                  </tr>
                  <tr>
                    <td>Join Date</td><td>".$user->created_at->format('m-d-Y h:i:s')."</td>
                  </tr>
                  <tr>
                    <td>Created By</td><td>".$user->CreatedBy->name."</td>
                  </tr>
                </table>

                <a href='#' onclick='getEditForm(".$user->id.")' class='btn btn-primary btn-block'><b>Edit</b></a>
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

      if (!Auth::user()->can('Update-User')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

        $user = User::where(['id' => $id])->first();
        $userRoles = $user->roles->pluck('id')->toArray();
        $role = Role::all();

        $facility = Facility::where(['active' => '1', 'deleted' => '0'])->get();
        $userfacilityaccesses = $user->Facility->pluck('facility_id')->toArray();


        $facilities = "";
        $roles = "";

        foreach ($role as $k => $v) {

            if (in_array($v->id, $userRoles)) {
                $checked = 'checked';
            } else {
                $checked = null;
            }

            $roles .= "<div class='col-md-4 col-sm-12'><input type='checkbox' name='role[]' value='{$v->id}' {$checked}> {$v->display_name}</br></div>";
        }


        foreach ($facility as $k => $v) {

            if (in_array($v->id, $userfacilityaccesses)) {
                $checked_2 = 'checked';
            } else {
                $checked_2 = null;
            }

            $facilities .= "<div class='col-md-4 col-sm-12'><input type='checkbox' name='facility[]' value='{$v->id}' {$checked_2}> {$v->code}</br></div>";
        }
        

        $data = "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editUser' action='".route('users.update',$id)."' method='POST' enctype='multipart/form-data'>

            ".csrf_field()."

            <input type='hidden' name='_method' value='PUT'>

            <div class='col-md-6 col-sm-12'>
            
              <div class='form-group'>
               <label>Name <code>*</code></label>
               <input type='text' name='name' placeholder='Name' class='form-control' value='{$user->name}' required>
              </div>

              <div class='form-group'>
                <label>Email <code>*</code></label>
                <input type='email' name='email' class='form-control' placeholder='Email' value='{$user->email}' required>
              </div>

              <div class='form-group'>
               <label>Company <code>*</code></label>
               <input type='text' name='company' placeholder='Company' class='form-control' value='{$user->company}' required>
              </div>

              <div class='form-group'>
                <a href='#' onclick='getPass(".$user->id.")' data-toggle='modal'><b>CHANGE PASSWORD</b></a>
              </div>

            </div>

            <div class='col-md-6 col-sm-12'>

              <div class='form-group'>
               <label>Project </label>
               <input type='text' name='project' placeholder='Project' class='form-control' value='{$user->project}' required>
              </div>

              <div class='form-group'>
               <label>Designation <code>*</code></label>
               <input type='text' name='designation' placeholder='Designation' class='form-control' value='{$user->designation}' required>
              </div>

              <div class='form-group'>
               <label>Phone <code>*</code></label>
               <input type='text' name='phone' placeholder='Phone' class='form-control' value='{$user->phone}' required>
              </div>

              <div class='form-group'>
                <label>Image</label>
                <input type='file' name='img_url' id='img_url' >
              </div>

            </div>

            <div class='col-md-12 col-sm-12'>

              <fieldset class='scheduler-border'>
                <legend>Roles: <code>*</code></legend>
                  <div class='col-md-12 col-sm-12'>
                      ".$roles."
                  </div>
              </fieldset><br>

            </div>

            <div class='col-md-12 col-sm-12'>

              <fieldset class='scheduler-border'>
                  <legend>Has Access to Facilities: </legend>
                  <div class='col-md-12 col-sm-12'>
                      ".$facilities."
                  </div>
                </fieldset><br>
              </br>

            </div>

            <button class='btn btn-block btn-primary btn-sm' id='submitEdit' type='submit'>SUBMIT</button>
            <button class='btn btn-block btn-primary btn-sm' id='loadingEdit' style='display: none' disabled=''>Working...</button>

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
      if (!Auth::user()->can('Update-User')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }

        $validator = Validator::make($request->all(), [
            'name' => 'required|Regex:/^[\D]+$/i|max:70',
            'email' => 'required|email|unique:users,email,'.$id,
            'designation' => 'required',
            'company' => 'required',
            'phone' => 'required',
            'img_url' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = User::find($id);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        DB::beginTransaction();

        try {

            if($request->hasFile('img_url')) {
              
               $file = $request->file('img_url');
               $name = time().'.'.$file->getClientOriginalExtension();
               $file->move(public_path().'/images/users', $name);

               User::where(['id' => $id])->update(
                   [
                       'name' => $request->name,
                       'email' => $request->email,
                       'designation' => $request->designation,
                       'project' => $request->project,
                       'company' => $request->company,
                       'phone' => $request->phone,
                       'img_url' => url('/')."/public/images/users/".$name,
                       'updatedbyuser_id' => Auth::user()->id,
                       'updated_at' => date('Y-m-d h:i:s'),
                   ]
               );



               DB::table('role_user')->where('user_id',$id)->delete();

               foreach ($request->role as $key => $value) {
                   $user->attachRole($value);
               }

               if (!empty($request->facility)) {
                 Userfacilityaccess::where(['user_id' => $id])->delete();

                 foreach ($request->facility as $key => $value) {
                     DB::table('userfacilityaccesses')->insert(
                         [
                             'user_id' => $id,
                             'facility_id' => $value,
                             'createdbyuser_id' => Auth::user()->id,
                             'created_at' => date('Y-m-d h:i:s'),
                             'updated_at' => date('Y-m-d h:i:s'),
                         ]
                     );
                 }

               } else {
                  Userfacilityaccess::where(['user_id' => $id])->delete();
               }

            } else {
                User::where(['id' => $id])->update(
                    [
                        'name' => $request->name,
                        'email' => $request->email,
                        'designation' => $request->designation,
                        'project' => $request->project,
                        'company' => $request->company,
                        'phone' => $request->phone,
                        'updatedbyuser_id' => Auth::user()->id,
                        'updated_at' => date('Y-m-d h:i:s'),
                    ]
                );

                DB::table('role_user')->where('user_id',$id)->delete();

                foreach ($request->role as $key => $value) {
                    $user->attachRole($value);
                }

               if (!empty($request->facility)) {
                 Userfacilityaccess::where(['user_id' => $id])->delete();

                 foreach ($request->facility as $key => $value) {
                     DB::table('userfacilityaccesses')->insert(
                         [
                             'user_id' => $id,
                             'facility_id' => $value,
                             'createdbyuser_id' => Auth::user()->id,
                             'created_at' => date('Y-m-d h:i:s'),
                             'updated_at' => date('Y-m-d h:i:s'),
                         ]
                     );
                 }

               }  else {
                  Userfacilityaccess::where(['user_id' => $id])->delete();
               }
            }

            DB::commit();

            return response()->json(['success'=>'User updated']);            
        
        } catch (\Exception $e) {

          DB::rollback();
          return response()->json(['error'=>array($e->getMessage())]);

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


    public function getUserActivation($id) {
      
      if (!Auth::user()->can('User-Activation')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      $isActive = User::where(['id' => $id])->first();

      if ($isActive->active == 1) {
        $action = "<option value='0'>Deactivate</option>";
      } else {
        $action = "<option value='1'>Activate</option>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editUser' action='".route('postUserActivation',$id)."' method='POST'>

            ".csrf_field()."

            <input type='hidden' name='_method' value='PUT'>

            <div class='form-group'>
              <label>Status <code>*</code></label>
              <select name='active' class='form-control'>
                $action
              </select>
            </div>


            <button class='btn btn-block btn-success btn-sm' id='submitEdit' type='submit'>SAVE</button>
            <button class='btn btn-block btn-success btn-sm' id='loadingEdit' style='display: none' disabled=''>Working...</button>

          </form>
      ";

    }


    public function postUserActivation(Request $request,$id){

      if (!Auth::user()->can('User-Activation')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }
      
      $validator = Validator::make($request->all(), [

            'active' => 'required',

        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        if ($request->active == 1) {
          $action = 'Activated';
        } else {
          $action = "Deactivate";
        }
            
        try {

            DB::beginTransaction();

            User::where(['id' => $id])->update(
                [
                    'active' => $request->active,
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d h:i:s'),
                ]
            );

            DB::table('useractions')->insert(
                [
                    'user_id' => $id,
                    'action' => $action,
                    'deleted' => '0',
                    'createdbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                ]
            );

            DB::commit();

            return response()->json(['success'=>'Updated Successfully!.']);

            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error'=>array('Update not applied!')]);
        }

    }


    public function get_password_change($user_id){
      
      User::where(['id' => $user_id])->first();

      return "
        <div class='alert alert-danger print-error-msg' id='error_messages_2' style='display:none'>
            <ul></ul>
        </div>

        <form id='edit_2' action='".route('postPassword',$user_id)."' method='POST'>

          ".csrf_field()."

          <input type='hidden' name='_method' value='PUT'>

          <div class='form-group'>
            <label>New Password <code>*</code></label>
            <input name='password' placeholder='New Password' type='password' class='form-control'>
          </div>

          <div class='form-group'>
            <label>Re Type New Password <code>*</code></label>
            <input name='password_confirmation' placeholder='Re Password' type='password' class='form-control'>
          </div>

          <button class='btn btn-block btn-success btn-sm' id='submitEdit' type='submit'>SAVE</button>
          <button class='btn btn-block btn-success btn-sm' id='loadingEdit' style='display: none' disabled=''>Working...</button>

        </form>
      ";
    }


    public function post_password_change(Request $request, $id) {
      $user = User::where(['id' => $id])->first();

      $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed|min:6',
        ]);

      if ($validator->fails()) {
          return response()->json(['error'=>$validator->errors()->all()]);
      }

      try {

            DB::beginTransaction();

            User::where(['id' => $id])->update(
                [
                    'password' => bcrypt($request->password),
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d h:i:s'),
                ]
            );

            DB::table('useractions')->insert(
                [
                    'user_id' => $id,
                    'action' => 'Password Change',
                    'deleted' => '0',
                    'createdbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                ]
            );

            DB::commit();

            return response()->json(['success'=>'Password updated successfully!.']);

            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error'=>array('Password not updated')]);
        }

    }


}
