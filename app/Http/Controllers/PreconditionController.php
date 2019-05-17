<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Precondition;
use App\Preconditionchange;
use Validator;
use Auth;

class PreconditionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    { 
        if (!Auth::user()->can('View-Preconditions')) {
          return view('pages.550');
        }

        $preconditions = Precondition::where(['deleted'=>'0'])->orderBy('created_at', 'DESC')->get();
        return view('pages.preconditions', compact('preconditions'));
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
        if (!Auth::user()->can('Create-Precondition')) {
          return response()->json(['error' => array('You do not have enough permission(s)')]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|unique:preconditions',
        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        DB::beginTransaction();

        try {

            $id = DB::table('preconditions')->insertGetId(
                [
                    'code' => time(),
                    'name' => $request->name,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                ]
            );

            sleep(1);

            $code = 'CLP'.sprintf('%06d', $id);

            Precondition::where(['id' => $id])->update(
              [
                'code' => $code,
              ]
            );

            DB::table('preconditionactions')->insert(
                [
                    'precondition_id' => $id,
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
          return response()->json(['error'=>array('Could not add precondition')]);

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
        if (!Auth::user()->can('View-Precondition-Details')) {
          return "<p style='color:red; text-align:center'>Permission Denied</p>";
        }

        $precondition = Precondition::where(['id' => $id, 'deleted' => '0', 'active' => '1'])->first();

        $data = "
          <div class='box box-primary' style='font-weight:bold'>
            <div class='box-body box-profile'>

              <h3 class='profile-username text-center'>$precondition->name</h3>

              <p class='text-muted text-center'>$precondition->code</p>

              <table class='table table-striped details-view'>
                <tr>
                  <td>Name</td><td>$precondition->name</td>
                </tr>
                <tr>
                  <td>Code</td><td>$precondition->code</td>
                </tr>
                <tr>
                  <td>Active</td><td>".($precondition->active == '1' ? 'YES' : 'NO')."</td>
                </tr>
              </table>

              <a href='#' onclick='getEditForm(".$precondition->id.")' class='btn btn-primary btn-block'><b>Edit</b></a>
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

      if (!Auth::user()->can('Update-Precondition')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

        $precondition = Precondition::where(['id' => $id])->first();

        $data = "
            <div class='alert alert-danger print-error-msg' style='display:none' id='error_messages'>
                <ul></ul>
            </div>

            <form id='editPrecondition' action='".route('preconditions.update',$id)."' method='POST'>

              ".csrf_field()."

              <input type='hidden' name='_method' value='PUT'>
              <div class='form-group'>
               <label>Name <code>*</code></label>
               <input type='text' name='name' placeholder='Name' value='$precondition->name' class='form-control' required>
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

        if (!Auth::user()->can('Update-Precondition')) {
          return response()->json(['error'=>array('You do not have enough permission')]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        try {

            Precondition::where(['id' => $id])->update(
                [
                    'name' => $request->name,
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d'),
                ]
            );

            return response()->json(['success'=>'Record updated.']);
   
        } catch (\Exception $e) {

          DB::rollback();
          return response()->json(['error'=>array('Could not update')]);

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


    public function getPrecActivation($id){

        if (!Auth::user()->can('Precondition-Activation')) {
          return "<p style='color:red; text-align:center'>Permission Denied</p>";
        }

        $isActive = Precondition::where(['id' => $id])->first();

        if ($isActive->active == 1) {
          $action = "<option value='0'>Deactivate</option>";
        } else {
          $action = "<option value='1'>Activate</option>";
        }

        return "
            <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
                <ul></ul>
            </div>

            <form id='editPrecondition' action='".route('postPrecActivation',$id)."' method='POST'>

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


    public function postPrecActivation(Request $request,$id){ 

          if (!Auth::user()->can('Precondition-Activation')) {
            return response()->json(['error'=>array('You do not have enough permission(s)')]);
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
            $action = "Deactivated";
          }

          $prec = Precondition::where(['id' => $id])->first();

          $isOk = count($prec->Client->where('active','1'));

          if ($isOk < 1) {
              
            try {

                DB::beginTransaction();

                Precondition::where(['id' => $id])->update(
                    [
                        'active' => $request->active,
                        'updatedbyuser_id' => Auth::user()->id,
                        'updated_at' => date('Y-m-d h:i:s'),
                    ]
                );

                DB::table('preconditionactions')->insert(
                    [
                        'precondition_id' => $id,
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
                return response()->json(['error'=>array('Can not execute')]);
            }

          } else {
            return response()->json(['error'=>array('Action can not be performed. Active user associated with precondition '.$prec->name.'')]);
          }   

    }


    public function getDeletion($id) {

      if (!Auth::user()->can('Delete-Precondition')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editPrecondition' action='".route('postPreconditionDeletion',$id)."' method='POST'>

            ".csrf_field()."

            <input type='hidden' name='_method' value='PUT'>

            <h3 style='text-align: center'>You will not be able to revert changes.
              <br>Are you sure to submit?
            </h3>


            <button class='btn btn-block btn-danger btn-sm' id='submitEdit' type='submit'>YES</button>
            <button class='btn btn-block btn-success btn-sm' id='loadingEdit' style='display: none' disabled=''>Working...</button>

          </form>
      ";

    }


    public function postDeletion(Request $request,$id){

        if (!Auth::user()->can('Delete-Precondition')) {
          return response()->json(['error'=>array("You do not have enough permission(s)")]);
        }

        $prechange = Preconditionchange::where(['precondition_id' => $id, 'deleted' => '0'])->get();

        $isOk = count($prechange);

        if ($isOk < 1) {
            
          try {

              DB::beginTransaction();

              Precondition::where(['id' => $id])->update(
                  [
                      'deleted' => '1',
                      'updatedbyuser_id' => Auth::user()->id,
                      'updated_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::table('preconditionactions')->insert(
                  [
                      'precondition_id' => $id,
                      'action' => 'deleted',
                      'deleted' => '0',
                      'createdbyuser_id' => Auth::user()->id,
                      'created_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::commit();

              return response()->json(['success'=>'Deleted Successfully!.']);

              
          } catch (\Exception $e) {
              DB::rollback();
              return response()->json(['error'=>array('Deletion not applied!')]);
          }

        } else {
          return response()->json(['error'=>array("Cannot be deleted")]);
        }     

    }


}
