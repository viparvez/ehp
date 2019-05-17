<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Deficiencyconcern;
use App\Deficiencydetail;
use App\Deficiencycategory;
use Validator;
use Auth;

class DefdetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      if (!Auth::user()->can('View-Deficiency-Details-List')) {
        return view('pages.550');
      }

        $defdetails = Deficiencydetail::where(['deleted'=>'0'])->orderBy('created_at', 'DESC')->get();
        $concerns = Deficiencyconcern::where(['deleted'=>'0', 'active'=>'1'])->orderBy('name', 'DESC')->get();
        $cats = Deficiencycategory::where(['deleted'=>'0', 'active'=>'1'])->orderBy('name', 'DESC')->get();
        return view('pages.deficiencydetails', compact('defdetails','concerns','cats'));
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
      if (!Auth::user()->can('Create-Deficiency-Detail')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }

        $validator = Validator::make($request->all(), [

            'category_id' => 'required',
            'concern_id' => 'required',
            'weightage' => 'required|numeric',
            'description' => 'required'

        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        DB::beginTransaction();

        try {

            $id = DB::table('deficiencydetails')->insertGetId(
                [
                    'code' => time(),
                    'category_id' => $request->category_id,
                    'concern_id' => $request->concern_id,
                    'weightage' => $request->weightage,
                    'description' => $request->description,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                ]
            );
            
            $code = 'DDT'.sprintf('%06d', $id);

            Deficiencydetail::where(['id' => $id])->update(
              [
                'code' => $code,
              ]
            );

            DB::table('deficiencydetailactions')->insert(
                [
                    'deficiencydetail_id' => $id,
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
          return response()->json(['error'=>array('Could not add details')]);

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
      if (!Auth::user()->can('View-Full-Deficiency-Detail')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }
        $detail = Deficiencydetail::where(['id' => $id])->first();

        $data = "
          <div class='box box-primary' style='font-weight:bold'>
            <div class='box-body box-profile'>

              <h3 class='profile-username text-center'>$detail->name</h3>

              <table class='table table-striped details-view'>
                <tr>
                  <td>Code</td><td>$detail->code</td>
                </tr>
                <tr>
                  <td>Deficiency Concern</td><td>{$detail->Concern->name}</td>
                </tr>
                <tr>
                  <td>Deficiency Category</td><td>{$detail->Category->name}</td>
                </tr>
                <tr>
                  <td>Weightage</td><td>$detail->weightage</td>
                </tr>
                <tr>
                  <td>Description</td><td>$detail->description</td>
                </tr>
              </table>

              <a href='#' onclick='getEditForm(".$detail->id.")' class='btn btn-primary btn-block'><b>Edit</b></a>
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
      if (!Auth::user()->can('Update-Deficiency-Detail')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

        $detail = Deficiencydetail::where(['id' => $id])->first();
        $categories = Deficiencycategory::where(['deleted' => '0', 'active' => '1'])->orderBy('name', 'ASC')->get();
        $concerns = Deficiencyconcern::where(['deleted' => '0', 'active' => '1'])->orderBy('name', 'ASC')->get();

        $catOps = "";
        $conOps = "";

        foreach ($categories as $key => $value) {
            if ($value->id == $detail->category_id) {
                $catOps .= "<option value='".$value->id."' selected>".$value->name."</option>";
            } else {
                $catOps .= "<option value='".$value->id."'>".$value->name."</option>";
            }
        }

        foreach ($concerns as $k => $v) {
            if ($v->id == $detail->concern_id) {
                $conOps .= "<option value='".$v->id."' selected>".$v->name."</option>";
            } else {
                $conOps .= "<option value='".$v->id."'>".$v->name."</option>";
            }
        }
        
        $data = "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editDefdet' action='".route('deficiencydetails.update',$id)."' method='POST'>

            ".csrf_field()."

            <input type='hidden' name='_method' value='PUT'>

            <div class='form-group'>
             <label>Code <code>*</code></label>
             <input type='text' name='code' placeholder='Code' class='form-control' value='{$detail->code}' readonly required>
            </div>

            <div class='form-group'>
             <label>Category <code>*</code></label>
             <select name='category_id' class='form-control'>
                ".$catOps."
             </select>
            </div>

            <div class='form-group'>
             <label>Concern <code>*</code></label>
             <select name='concern_id' class='form-control'>
                ".$conOps."
             </select>
            </div>

            <div class='form-group'>
             <label>Weightage  <code>*</code></label>
             <input type='text' name='weightage' placeholder='Weightage' class='form-control' value='{$detail->weightage}' required>
            </div>

            <div class='form-group'>
             <label>Description <code>*</code></label>
             <textarea class='form-control' name='description'>{$detail->description}</textarea>
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

      if (!Auth::user()->can('Update-Deficiency-Detail')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }

        $validator = Validator::make($request->all(), [

            'category_id' => 'required',
            'concern_id' => 'required',
            'weightage' => 'required|numeric',
            'description' => 'required',

        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        
        try {

            Deficiencydetail::where(['id' => $id])->update(
                [
                    'category_id' => $request->category_id,
                    'concern_id' => $request->concern_id,
                    'weightage' => $request->weightage,
                    'description' => $request->description,
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d'),
                ]
            );
            
            return response()->json(['success'=>'Added new records.']);


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


    public function getDefdetailActivation($id) {
      
      if (!Auth::user()->can('Deficiency-Details-Activation')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      $isActive = Deficiencydetail::where(['id' => $id])->first();

      if ($isActive->active == 1) {
        $action = "<option value='0'>Deativate</option>";
      } else {
        $action = "<option value='1'>Activate</option>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editDefdet' action='".route('postDefdetailActivation',$id)."' method='POST'>

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


    public function postDefdetailActivation(Request $request,$id){

      if (!Auth::user()->can('Deficiency-Details-Activation')) {
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

        $defdet = Deficiencydetail::where(['id' => $id])->first();

        $isOk = count($defdet->Inspectiondetails->where('status','INCOMPLETE'));

        if ($isOk < 1) {
            
          try {

              DB::beginTransaction();

              Deficiencydetail::where(['id' => $id])->update(
                  [
                      'active' => $request->active,
                      'updatedbyuser_id' => Auth::user()->id,
                      'updated_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::table('deficiencydetailactions')->insert(
                  [
                      'deficiencydetail_id' => $id,
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
          return response()->json(['error'=>array("Cannot execute action. Associated with INCOMPLETE inspection.")]);
        }     

    }


    public function getDeletion($id) {

      if (!Auth::user()->can('Delete-Deficiency-Details')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editDefdet' action='".route('postDefdetDeletion',$id)."' method='POST'>

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

      if (!Auth::user()->can('Delete-Deficiency-Details')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }
      
        $defdet = Deficiencydetail::where(['id' => $id])->first();

        $isOk = count($defdet->Inspectiondetails->where('deleted','0'));

        if ($isOk < 1) {
            
          try {

              DB::beginTransaction();

              Deficiencydetail::where(['id' => $id])->update(
                  [
                      'deleted' => '1',
                      'updatedbyuser_id' => Auth::user()->id,
                      'updated_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::table('deficiencydetailactions')->insert(
                  [
                      'deficiencydetail_id' => $id,
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
          return response()->json(['error'=>array("Cannot delete")]);
        }     

    }


}
