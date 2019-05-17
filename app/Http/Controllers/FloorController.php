<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Vendor;
use App\Facility;
use App\Floor;
use Validator;
use Auth;
use App\Http\Controllers\FacilityAccessController;

class FloorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      if (!Auth::user()->can('View-Floors')) {
        return view('pages.550');
      }

        if ((new FacilityAccessController)->isSuper() == true) {

          $floors = Floor::where(['deleted'=>'0'])->orderBy('created_at', 'DESC')->get();
          $vendors = Vendor::where(['deleted'=>'0', 'active'=>'1'])->orderBy('name', 'ASC')->get();

        } else {
          $floors = Floor::where(['deleted'=>'0'])
                    ->whereIn('floors.facility_id', (new FacilityAccessController)->userFacilities())
                    ->orderBy('created_at', 'DESC')
                    ->get();

          $vendors = DB::table('vendors')
                     ->join('facilities', 'vendors.id', '=', 'facilities.vendor_id')
                     ->where(['vendors.deleted' => '0'])
                     ->whereIn('facilities.id',(new FacilityAccessController)->userFacilities())
                     ->select('vendors.*')
                     ->get();

        }

        return view('pages.floors', compact('floors','vendors'));
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

      if (!Auth::user()->can('Create-Floor')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }

        $validator = Validator::make($request->all(), [

            'facility_id' => 'required',
            'name' => 'required|max:255',

        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        DB::beginTransaction();

        try {

            $id = DB::table('floors')->insertGetId(
                [
                    'code' => time(),
                    'name' => $request->name,
                    'facility_id' => $request->facility_id,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                ]
            );

            $code = 'FLR'.sprintf('%06d', $id);

            Floor::where(['id' => $id])->update(
              [
                'code' => $code,
              ]
            );

            DB::table('flooractions')->insert(
                  [
                      'floor_id' => $id,
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
          return response()->json(['error'=>array('Could not add floor')]);

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

      if (!Auth::user()->can('View-Floor-Details')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

        $floor = Floor::where(['id' => $id, 'deleted' => '0'])->first();

        if ((new FacilityAccessController)->getPemissionById($floor->facility_id) == false) {
          return "Permission Denied!";
        }


        $data = "
          <div class='box box-primary' style='font-weight:bold'>
            <div class='box-body box-profile'>

              <h3 class='profile-username text-center'>$floor->name</h3>

              <p class='text-muted text-center'>$floor->code</p>

              <table class='table table-striped details-view'>
                <tr>
                  <td>Name</td><td>$floor->name</td>
                </tr>
                <tr>
                  <td>Code</td><td>$floor->code</td>
                </tr>
                <tr>
                  <td>Building/Facility</td><td>".$floor->Facility->name."</td>
                </tr>
                <tr>
                  <td>Vendor</td><td>".$floor->Facility->Vendor->name."</td>
                </tr>
                <tr>
                  <td>Status</td><td>".($floor->active == '1' ? 'YES' : 'NO')."</td>
                </tr>
              </table>

              <a href='#' onclick='getEditForm(".$floor->id.")' class='btn btn-primary btn-block'><b>Edit</b></a>
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
      if (!Auth::user()->can('Update-Floor')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }
        $floor = Floor::where(['id' => $id])->first();

        if ((new FacilityAccessController)->getPemissionById($floor->facility_id) == false) {
          return "Permission Denied!";
        }

        if ((new FacilityAccessController)->isSuper() == true) {

          $floors = Floor::where(['deleted'=>'0'])->orderBy('created_at', 'DESC')->get();
          $vendors = Vendor::where(['deleted'=>'0', 'active'=>'1'])->orderBy('name', 'ASC')->get();
          $facilities = Facility::where(['id' => $floor->Facility->id])->get();

        } else {
          $floors = Floor::where(['deleted'=>'0'])
                    ->whereIn('floors.facility_id', (new FacilityAccessController)->userFacilities())
                    ->orderBy('created_at', 'DESC')
                    ->get();

          $vendors = DB::table('vendors')
                     ->join('facilities', 'vendors.id', '=', 'facilities.vendor_id')
                     ->whereIn('facilities.id',(new FacilityAccessController)->userFacilities())
                     ->select('vendors.*')
                     ->get();

          $facilities = Facility::where(['id' => $floor->Facility->id])->whereIn('id',(new FacilityAccessController)->userFacilities())->get();

        }

        $vendroOps = "";
        $facilityOps = "";

        foreach ($vendors as $key => $value) {

            if ($value->id == $floor->Facility->vendor_id) {
                $vendroOps .= "<option value='$value->id' selected>$value->name</option>";
            } else {
                $vendroOps .= "<option value='$value->id'>$value->name</option>";
            }
               
        }


        foreach ($facilities as $k => $v) {

            if ($v->id == $floor->Facility->id) {
                $facilityOps .= "<option value='$v->id' selected>$v->name</option>";
            } else {
                $facilityOps .= "<option value='$v->id'>$v->name</option>";
            }
               
        }



        $data = "
            <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
                <ul></ul>
            </div>

            <form id='editFloors' action='".route('floors.update', $floor->id)."' method='POST'>

              ".csrf_field()."

              <input type='hidden' name='_method' value='PUT'>
              <div class='form-group'>
               <label>Name <code>*</code></label>
               <input type='text' name='name' placeholder='Name' class='form-control' value='$floor->name' required>
              </div>

              <div class='form-group'>
               <label>Vendor</label>
               <select id='vendorEdit' name='vendor' class='form-control' required>
                 <option value=''>SELECT</option>
                 ".$vendroOps."
               </select>
              </div>

              <div class='form-group'>
               <label>Building <code>*</code></label>
               <select id='facility_idEdit' name='facility_id' class='form-control' required>
                    <option value=''>SELECT</option>
                    ".$facilityOps."
               </select>
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
      if (!Auth::user()->can('Update-Floor')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }

        $validator = Validator::make($request->all(), [

            'facility_id' => 'required',
            'name' => 'required|max:255',

        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        try {

            Floor::where(['id' => $id])->update(
                [
                    'name' => $request->name,
                    'facility_id' => $request->facility_id,
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d'),
                ]
            );

            return response()->json(['success'=>'Record updated.']);


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


    /*
    *
    * Get Facility against vendors
    *
    */

    public function getVendorFacility($id){
    
        if ((new FacilityAccessController)->isSuper() == true){
          $facilities = Facility::where(['vendor_id' => $id, 'active' => '1', 'deleted' => '0'])
                ->orderBy('code','ASC')->get();
        } else {
          $facilities = Facility::where(['vendor_id' => $id, 'active' => '1', 'deleted' => '0'])
                          ->whereIn('id', (new FacilityAccessController)->userFacilities())
                          ->orderBy('code','ASC')->get();
        }
        return $facilities;
    }


    public function getFloorActivation($id) {
      
      if (!Auth::user()->can('Floor-Activation')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      $isActive = Floor::where(['id' => $id])->first();

      if ($isActive->active == 1) {
        $action = "<option value='0'>Offline</option>";
      } else {
        $action = "<option value='1'>Activate</option>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editFloors' action='".route('postFloorActivation',$id)."' method='POST'>

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


    public function getDeletion($id) {

      if (!Auth::user()->can('Delete-Floor')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editFloors' action='".route('postFloorDeletion',$id)."' method='POST'>

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

        if (!Auth::user()->can('Delete-Floor')) {
          return response()->json(['error' => array('You do not have enough permission(s)')]);
        }

        $floor = Floor::where(['id' => $id])->first();

        $isOk = count($floor->Apartment->where('deleted','0'));

        if ($isOk < 1) {
            
          try {

              DB::beginTransaction();

              Floor::where(['id' => $id])->update(
                  [
                      'deleted' => '1',
                      'updatedbyuser_id' => Auth::user()->id,
                      'updated_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::table('flooractions')->insert(
                  [
                      'floor_id' => $id,
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
          return response()->json(['error'=>array("Floor has $isOk apartments")]);
        }     

    }


    public function postFloorActivation(Request $request,$id){

      if (!Auth::user()->can('Floor-Activation')) {
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
          $action = "Offline";
        }

        $floor = Floor::where(['id' => $id])->first();

        $isOk = count($floor->Apartment->where('active','1'));

        if ($isOk < 1) {
            
          try {

              DB::beginTransaction();

              Floor::where(['id' => $id])->update(
                  [
                      'active' => $request->active,
                      'updatedbyuser_id' => Auth::user()->id,
                      'updated_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::table('flooractions')->insert(
                  [
                      'floor_id' => $id,
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
          return response()->json(['error'=>array("Floor has $isOk active apartments")]);
        }     

    }


}
