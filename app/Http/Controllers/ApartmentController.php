<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Apartment;
use App\Vendor;
use App\Floor;
use App\Facility;
use Validator;
use Auth;
use App\Inspection;
use Illuminate\Routing\Route;

class ApartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      if (!Auth::user()->can('View-Apartments')) {
        return view('pages.550');
      }
        if ((new FacilityAccessController)->isSuper() == true) {

          $vendors = Vendor::where(['deleted'=>'0', 'active' => '1'])->orderBy('created_at', 'DESC')->get();
          $apartments = DB::table('apartments')
                        ->join('floors', 'floors.id', '=', 'apartments.floor_id')
                        ->join('facilities', 'facilities.id', '=', 'floors.facility_id')
                        ->join('vendors', 'vendors.id', '=', 'facilities.vendor_id')
                        ->where(['apartments.deleted' => '0'])
                        ->select('apartments.*', 'floors.name AS floor_name', 'facilities.code AS facility_code', 'facilities.type AS facility_type', 'facilities.name AS facility_name', 'vendors.name AS vendor_name')
                        ->get();

        } else {

          $vendors = DB::table('vendors')
                     ->join('facilities', 'vendors.id', '=', 'facilities.vendor_id')
                     ->whereIn('facilities.id',(new FacilityAccessController)->userFacilities())
                     ->where('vendors.deleted', '=', '0')
                     ->where('vendors.active', '=', '1')
                     ->select('vendors.*')
                     ->get();

          $apartments = DB::table('apartments')
                        ->join('floors', 'floors.id', '=', 'apartments.floor_id')
                        ->join('facilities', 'facilities.id', '=', 'floors.facility_id')
                        ->join('vendors', 'vendors.id', '=', 'facilities.vendor_id')
                        ->where(['apartments.deleted' => '0'])
                        ->whereIn('facilities.id',(new FacilityAccessController)->userFacilities())
                        ->select('apartments.*', 'floors.name AS floor_name', 'facilities.code AS facility_code', 'facilities.type AS facility_type', 'facilities.name AS facility_name', 'vendors.name AS vendor_name')
                        ->get();

        }

        return view('pages.apartments', compact('apartments','vendors'));
    }


    public function facilityApts($facility_id)
    {

        if ((new FacilityAccessController)->isSuper() == true) {

          $vendors = Vendor::where(['deleted'=>'0'])->orderBy('created_at', 'DESC')->get();
          $apartments = DB::table('apartments')
                        ->join('floors', 'floors.id', '=', 'apartments.floor_id')
                        ->join('facilities', 'facilities.id', '=', 'floors.facility_id')
                        ->join('vendors', 'vendors.id', '=', 'facilities.vendor_id')
                        ->where(['apartments.deleted' => '0', 'facilities.id' => $facility_id])
                        ->select('apartments.*', 'floors.name AS floor_name', 'facilities.code AS facility_code', 'facilities.type AS facility_type', 'facilities.name AS facility_name', 'vendors.name AS vendor_name')
                        ->get();

        } else {

          $vendors = DB::table('vendors')
                     ->join('facilities', 'vendors.id', '=', 'facilities.vendor_id')
                     ->whereIn('facilities.id',(new FacilityAccessController)->userFacilities())
                     ->select('vendors.*')
                     ->get();

          $apartments = DB::table('apartments')
                        ->join('floors', 'floors.id', '=', 'apartments.floor_id')
                        ->join('facilities', 'facilities.id', '=', 'floors.facility_id')
                        ->join('vendors', 'vendors.id', '=', 'facilities.vendor_id')
                        ->where(['apartments.deleted' => '0', 'facilities.id' => $facility_id])
                        ->whereIn('facilities.id',(new FacilityAccessController)->userFacilities())
                        ->select('apartments.*', 'floors.name AS floor_name', 'facilities.code AS facility_code', 'facilities.type AS facility_type', 'facilities.name AS facility_name', 'vendors.name AS vendor_name')
                        ->get();

        }

        return view('pages.apartments', compact('apartments','vendors'));
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
      if (!Auth::user()->can('Create-Apartment')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }
        $validator = Validator::make($request->all(), [

            'floor_id' => 'required',
            'name' => 'required|max:255',
            'vacantfrom' => 'required',

        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        DB::beginTransaction();

        try {

            $id = DB::table('apartments')->insertGetId(
                [
                    'code' => time(),
                    'name' => $request->name,
                    'vacantfrom' => (new \App\Custom\Custom)->convertDate($request->vacantfrom, "Y-m-d"),
                    'comment' => $request->comment,
                    'floor_id' => $request->floor_id,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                ]
            );
            
            $code = 'APT'.sprintf('%06d', $id);

            Apartment::where(['id' => $id])->update(
              [
                'code' => $code,
              ]
            );

             DB::table('apartmentactions')->insert(
                [
                    'apartment_id' => $id,
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
          return response()->json(['error'=>array('Could not add apartment')]);

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
      if (!Auth::user()->can('View-Apartment-Details')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

        $apartment = Apartment::where(['id' => $id, 'deleted' => '0'])->first();

        $data = "
          <div class='box box-primary' style='font-weight:bold'>
            <div class='box-body box-profile'>

              <h3 class='profile-username text-center'>$apartment->name</h3>

              <p class='text-muted text-center'>$apartment->code</p>

              <table class='table table-striped details-view'>
                <tr>
                  <td>Name</b> </td><td>$apartment->name</td>
                </tr>
                <tr>
                  <td>Code</b> </td><td>$apartment->code</td>
                </tr>
                <tr>
                  <td>Floor</b> </td><td>".$apartment->Floor->name."</td>
                </tr>
                <tr>
                  <td>Building/Facility</b> </td><td>".$apartment->Floor->Facility->name."</td>
                </tr>
                <tr>
                  <td>Vendor</b> </td><td>".$apartment->Floor->Facility->Vendor->name."</td>
                </tr>
                <tr>
                  <td>Comment</b></td><td>$apartment->comment</td>
                </tr>
                <tr>
                  <td>Active</b> </td><td>".($apartment->active == '1' ? 'YES' : 'NO')."</td>
                </tr>
                <tr>
                  <td>Vacant From</td><td>".($apartment->free == '1' ? date('m-d-Y',strtotime($apartment->vacantfrom)) : 'Occupied')."</td>
                </tr>
              </table>

              <a href='#' onclick='getEditForm(".$apartment->id.")' class='btn btn-primary btn-block'><b>Edit</b></a>
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
      if (!Auth::user()->can('Update-Apartment')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

        $apartment = Apartment::where(['id' => $id])->first();   

        if ((new FacilityAccessController)->isSuper() == true) {

          $vendors = Vendor::where(['deleted'=>'0'])->orderBy('created_at', 'DESC')->get();

          $facilities = Facility::where(['active' => '1', 'deleted' => '0'])->orderBy('name', 'ASC')->get();
      
          $floors = Floor::where(['active' => '1', 'deleted' => '0'])
            ->where(['facility_id' => $apartment->Floor->Facility->id])
            ->orderBy('name', 'ASC')->get();


        } else {

          $vendors = DB::table('vendors')
                     ->join('facilities', 'vendors.id', '=', 'facilities.vendor_id')
                     ->whereIn('facilities.id',(new FacilityAccessController)->userFacilities())
                     ->select('vendors.*')
                     ->get();

          $facilities = Facility::where(['active' => '1', 'deleted' => '0'])->whereIn('facilities.id',(new FacilityAccessController)->userFacilities())->orderBy('name', 'ASC')->get();

          $floors = Floor::where(['active' => '1', 'deleted' => '0'])->
            whereIn('floors.facility_id',(new FacilityAccessController)->userFacilities())
            ->orderBy('name', 'ASC')->get();
            
        }

        $vendroOps = "";
        $facilityOps = "";
        $floorOps = "";

        foreach ($vendors as $key => $value) {

            if ($value->id == $apartment->Floor->Facility->vendor_id) {
                $vendroOps .= "<option value='$value->id' selected>$value->name</option>";
            } else {
                $vendroOps .= "<option value='$value->id'>$value->name</option>";
            }
               
        }


        foreach ($facilities as $k => $v) {

            if ($v->id == $apartment->Floor->Facility->id) {
                $facilityOps .= "<option value='$v->id' selected>$v->name</option>";
            } else {
                $facilityOps .= "<option value='$v->id'>$v->name</option>";
            }
               
        }


        foreach ($floors as $ki => $vi) {

            if ($vi->id == $apartment->Floor->id) {
                $floorOps .= "<option value='$vi->id' selected>$vi->name</option>";
            } else {
                $floorOps .= "<option value='$vi->id'>$vi->name</option>";
            }
               
        }

        $vacantfrom = "
          <div class='form-group' hidden>
            <label>Vacant From <code>*</code></label>
            <div class='input-group date'>
              <div class='input-group-addon'>
                <i class='fa fa-calendar'></i>
              </div>
              <input type='text' class='form-control pull-right' id='datepicker1' value='".(new \App\Custom\Custom)->dateToView($apartment->vacantfrom, "m-d-Y")."' name='vacantfrom'>
            </div>
          </div>
        ";

        if ($apartment->free == '1') {
          $vacantfrom = "
            <div class='form-group'>
              <label>Vacant From <code>*</code></label>
              <div class='input-group date'>
                <div class='input-group-addon'>
                  <i class='fa fa-calendar'></i>
                </div>
                <input type='text' class='form-control pull-right' id='datepicker1' value='".(new \App\Custom\Custom)->dateToView($apartment->vacantfrom, "m-d-Y")."' name='vacantfrom'>
              </div>
            </div>
          ";
        }


        $data = "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editApartment' action='".route('apartments.update', $id)."' method='POST'>

            ".csrf_field()."

            <input type='hidden' name='_method' value='PUT'>
            <div class='form-group'>
             <label>Name <code>*</code></label>
             <input type='text' name='name' placeholder='Name' class='form-control' value='$apartment->name' required>
            </div>

            <div class='form-group'>
             <label>Vendor</label>
             <select id='vendorEdit' name='vendor' class='form-control' required>
               <option value=''>SELECT</option>
               ".$vendroOps."
             </select>
            </div>

            <div class='form-group'>
             <label>Building</label>
             <select id='facility_idEdit' name='facility_id' class='form-control' required>
              <option value=''>SELECT</option>
               ".$facilityOps."
             </select>
            </div>

            <div class='form-group'>
             <label>Floor <code>*</code></label>
             <select id='floor_idEdit' name='floor_id' class='form-control' required>
              <option value=''>SELECT</option>
               ".$floorOps."
             </select>
            </div>

            <div class='form-group'>
             <label>Comment</label>
             <textarea id='comment' name='comment' class='form-control' required>$apartment->comment</textarea>
            </div>

            ".$vacantfrom."

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
      if (!Auth::user()->can('Update-Apartment')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }

        $validator = Validator::make($request->all(), [

            'floor_id' => 'required',
            'name' => 'required|max:255',
            'vacantfrom' => 'required',

        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        DB::beginTransaction();

        try {

            Apartment::where(['id' => $id])->update(
                [
                    'name' => $request->name,
                    'vacantfrom' => (new \App\Custom\Custom)->convertDate($request->vacantfrom, "Y-m-d"),
                    'comment' => $request->comment,
                    'floor_id' => $request->floor_id,
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                ]
            );
            

            DB::commit();

            return response()->json(['success'=>'Record updated']);
        
        } catch (\Exception $e) {

          DB::rollback();
          return response()->json(['error'=>array('Could not update apartment')]);

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
    * Get Floors against Facility
    *
    */

    public function getFacilityFloor($id) {
        $floors = Floor::where(['facility_id' => $id, 'active' => '1', 'deleted' => '0'])->orderBy('name','ASC')->get();
        return $floors;
    }

    public function getFacilityApartment($id, $date) {

        $date = (new \App\Custom\Custom)->convertDate($date, "Y-m-d");

        $apartments = DB::select(
            "
                SELECT A.id, A.name FROM apartments A 
                INNER JOIN floors F ON F.id = A.floor_id 
                INNER JOIN facilities FA ON FA.id = F.facility_id 
                WHERE A.active = '1' 
                AND A.deleted = '0' 
                AND A.free = '1'
                AND FA.id IN ('$id') 
                ORDER BY A.name ASC 
            "
        );
        return $apartments;
    }


    public function getApartment($id) {
        $apartments = DB::select(
            "
                SELECT A.id, A.name FROM apartments A 
                INNER JOIN floors F ON F.id = A.floor_id 
                INNER JOIN facilities FA ON FA.id = F.facility_id 
                WHERE A.active = '1' 
                AND A.deleted = '0' 
                AND FA.id IN ('$id') 
                ORDER BY A.name ASC 
            "
        );
        return $apartments;
    }


    public function getAptActivation($id) {
      
      if (!Auth::user()->can('Apartment-Activation')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      $isActive = Apartment::where(['id' => $id])->first();

      if ($isActive->active == 1) {
        $action = "<option value='0'>Offline</option>";
      } else {
        $action = "<option value='1'>Activate</option>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editApartment' action='".route('postAptActivation',$id)."' method='POST'>

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



    public function postAptActivation(Request $request,$id){

      if (!Auth::user()->can('Apartment-Activation')) {
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

        $apt = Apartment::where(['id' => $id])->first();

        if ($apt->free == 1) {
            
          try {

              DB::beginTransaction();

              Apartment::where(['id' => $id])->update(
                  [
                      'active' => $request->active,
                      'updatedbyuser_id' => Auth::user()->id,
                      'updated_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::table('apartmentactions')->insert(
                  [
                      'apartment_id' => $id,
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
          return response()->json(['error'=>array("Apartment is occupied.")]);
        }     

    }

    public function getDeletion($id) {

      if (!Auth::user()->can('Delete-Apartment')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editApartment' action='".route('postApartmentDeletion',$id)."' method='POST'>

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

        if (!Auth::user()->can('Delete-Apartment')) {
          return response()->json(['error' => array('You do not have enough permission(s)')]);
        }
        
        $apartment = Apartment::where(['id' => $id])->first();

        $isOk = count($apartment->Admission->where('deleted','0'));

        if ($isOk < 1) {
            
          try {

              DB::beginTransaction();

              Apartment::where(['id' => $id])->update(
                  [
                      'deleted' => '1',
                      'updatedbyuser_id' => Auth::user()->id,
                      'updated_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::table('apartmentactions')->insert(
                  [
                      'apartment_id' => $id,
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
          return response()->json(['error'=>array("Apartment has admission history")]);
        }     

    }


}
