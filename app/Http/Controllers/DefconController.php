<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Deficiencyconcern;
use Validator;
use Auth;

class DefconController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      if (!Auth::user()->can('View-Deficiency-Concerns')) {
        return view('pages.550');
      }

        $concerns = Deficiencyconcern::where(['deleted'=>'0'])->orderBy('created_at', 'DESC')->get();
        return view('pages.deficiencyconcern', compact('concerns'));
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

      if (!Auth::user()->can('Create-Deficiency-Concern')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }

        $validator = Validator::make($request->all(), [

            'name' => 'required|max:100'

        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        DB::beginTransaction();

        try {

            $id = DB::table('deficiencyconcerns')->insertGetId(
                [
                    'code' => time(),
                    'name' => $request->name,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                ]
            );
            
            $code = 'DCO'.sprintf('%06d', $id);

            Deficiencyconcern::where(['id' => $id])->update(
              [
                'code' => $code,
              ]
            );

            DB::table('deficiencyconcernactions')->insert(
                [
                    'deficiencyconcern_id' => $id,
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
          return response()->json(['error'=>array('Could not add concern')]);

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
      if (!Auth::user()->can('View-Deficiency-Concern-Details')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }
        $concern = Deficiencyconcern::where(['id' => $id])->first();

        $data = "
          <div class='box box-primary' style='font-weight:bold'>
            <div class='box-body box-profile'>

              <h3 class='profile-username text-center'>$concern->name</h3>

              <table class='table table-striped details-view'>
                <tr>
                  <td>Name</td><td>$concern->name</td>
                </tr>
                <tr>
                  <td>Code</td><td>$concern->code</td>
                </tr>
              </table>

              <a href='#' onclick='getEditForm(".$concern->id.")' class='btn btn-primary btn-block'><b>Edit</b></a>
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

      if (!Auth::user()->can('Update-Deficiency-Concern')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

        $con = Deficiencyconcern::where(['id' => $id])->first();
        
        $data = "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editDefcon' action='".route('deficiencyconcerns.update',$id)."' method='POST'>

            ".csrf_field()."

            <input type='hidden' name='_method' value='PUT'>

            <div class='form-group'>
             <label>Name <code>*</code></label>
             <input type='text' name='name' placeholder='Name' class='form-control' value='{$con->name}' required>
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

      if (!Auth::user()->can('Update-Deficiency-Concern')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }

        $validator = Validator::make($request->all(), [

            'name' => 'required|max:100'

        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        try {

            Deficiencyconcern::where(['id' => $id])->update(
                [
                    'name' => $request->name,
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d'),
                ]
            );
            
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


    public function getDefconActivation($id) {
      
      if (!Auth::user()->can('Deficiency-Concern-Activation')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      $isActive = Deficiencyconcern::where(['id' => $id])->first();

      if ($isActive->active == 1) {
        $action = "<option value='0'>Deativate</option>";
      } else {
        $action = "<option value='1'>Activate</option>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editDefcon' action='".route('postDefconActivation',$id)."' method='POST'>

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


    public function postDefconActivation(Request $request,$id){

      if (!Auth::user()->can('Deficiency-Concern-Activation')) {
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
          $action = "Deactivated";
        }

        $defcon = Deficiencyconcern::where(['id' => $id])->first();

        $isOk = count($defcon->Defdetails->where('active','1'));

        if ($isOk < 1) {
            
          try {

              DB::beginTransaction();

              Deficiencyconcern::where(['id' => $id])->update(
                  [
                      'active' => $request->active,
                      'updatedbyuser_id' => Auth::user()->id,
                      'updated_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::table('deficiencyconcernactions')->insert(
                  [
                      'deficiencyconcern_id' => $id,
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

        } else {
          return response()->json(['error'=>array("Cannot execute action. Already associated with active Deficiency Details.")]);
        }     

    }



    public function getDeletion($id) {

      if (!Auth::user()->can('Delete-Deficiency-Concern')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editDefcon' action='".route('postDefconDeletion',$id)."' method='POST'>

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

        if (!Auth::user()->can('Delete-Deficiency-Concern')) {
          return response()->json(['error' => array('You do not have enough permission(s)')]);
        }
        
        $defcon = Deficiencyconcern::where(['id' => $id])->first();

        $isOk = count($defcon->Defdetails->where('deleted','0'));

        $hasInspection = count($defcon->Inspection($id));

        if ($isOk < 1 && $hasInspection < 1) {
            
          try {

              DB::beginTransaction();

              Deficiencyconcern::where(['id' => $id])->update(
                  [
                      'deleted' => '1',
                      'updatedbyuser_id' => Auth::user()->id,
                      'updated_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::table('deficiencyconcernactions')->insert(
                  [
                      'deficiencyconcern_id' => $id,
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
