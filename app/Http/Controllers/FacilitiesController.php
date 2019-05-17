<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Facility;
use App\Vendor;
use Validator;
use App\Floor;
use App\State;
use App\Http\Controllers\FacilityAccessController;
use App\Facilitydocument;
use App\Custom\Custom;
use App\Inspection;

class FacilitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

      if (!Auth::user()->can('View-Facilities')) {
        return view('pages.550');
      }

        $userFacilities =  Auth::user()->Facility->pluck('facility_id')->toArray();

        if ((new FacilityAccessController)->isSuper() == true) {
          $facilities = Facility::where(['deleted'=>'0'])->orderBy('code', 'ASC')->get();
        } else {
          $facilities = Facility::where(['deleted' => '0'])->whereIn('id', $userFacilities)->orderBy('code', 'ASC')->get();
        }

        $vendors = Vendor::where(['deleted'=>'0', 'active'=>'1'])->orderBy('name', 'ASC')->get();
        $states = State::where(['deleted'=>'0', 'active'=>'1'])->get();

        return view('pages.facilities', compact('facilities','vendors','states'));
    }


    public function vendorFacilities($vendor_id)
    {

        $userFacilities =  Auth::user()->Facility->pluck('facility_id')->toArray();

        if ((new FacilityAccessController)->isSuper() == true) {
          $facilities = Facility::where(['deleted'=>'0', 'vendor_id' => $vendor_id, 'active' => '1'])->orderBy('code', 'ASC')->get();
        } else {
          $facilities = Facility::where(['deleted' => '0', 'vendor_id' => $vendor_id, 'active' => '1'])->whereIn('id', $userFacilities)->orderBy('code', 'ASC')->get();
        }

        $vendors = Vendor::where(['deleted'=>'0', 'active'=>'1'])->orderBy('name', 'ASC')->get();
        $states = State::where(['deleted'=>'0', 'active'=>'1'])->get();

        return view('pages.facilities', compact('facilities','vendors','states'));
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

      if (!Auth::user()->can('Create-Facility')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }

        $validator = Validator::make($request->all(), [

            'vendor_id' => 'required',
            'name' => 'required|max:255',
            'code' => 'required|max:255|unique:facilities',
            'hasMedicine' => 'required',
            'hasHandicapAccess' => 'required',
            'isSmokeFree' => 'required',
            'hasElevator' => 'required',
            'city' => 'required',
            'state_id' => 'required',
            'zip' => 'required',
            'type' => 'required',
            'start_date_hasa' => 'required',
            'start_date_ehp' => 'required',
            'ein' => 'required',
            'rate' => 'required|numeric'
        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        try {

          DB::beginTransaction();

            $id = DB::table('facilities')->insertGetId(
                [
                    'vendor_id' => $request->vendor_id,
                    'code' => trim($request->code,' '),
                    'name' => $request->name,
                    'city' => $request->city,
                    'state_id' => $request->state_id,
                    'zip' => $request->zip,
                    'contact_p' => $request->contact_p,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'comment' => $request->comment,
                    'hasMedicine' => $request->hasMedicine,
                    'hasHandicapAccess' => $request->hasHandicapAccess,
                    'isSmokeFree' => $request->isSmokeFree,
                    'hasElevator' => $request->hasElevator,
                    'type' => $request->type,
                    'start_date_hasa' => (new \App\Custom\Custom)->convertDate($request->start_date_hasa, "Y-m-d"),
                    'start_date_ehp' => (new \App\Custom\Custom)->convertDate($request->start_date_ehp, "Y-m-d"),
                    'ein' => $request->ein,
                    'rate' => $request->rate,
                    'mou_signed_from' => (new \App\Custom\Custom)->convertDate($request->mou_signed_from, "Y-m-d"),
                    'mou_signed_to' => (new \App\Custom\Custom)->convertDate($request->mou_signed_to, "Y-m-d"),
                    'no_of_units' => $request->no_of_units,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                ]
            );

            DB::table('facilityactions')->insert(
                [
                    'facility_id' => $id,
                    'action' => 'Created',
                    'deleted' => '0',
                    'createdbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                ]
            );

            if($request->hasFile('contact_paper')) {

              $file = $request->file('contact_paper');
              $name = $id. '-'.time().'-'.$request->code.'.'.$file->getClientOriginalExtension();
              $file->move(public_path().'/file/facilities/', $name);
              $file_url = "file/facilities/$name";

              DB::table('facilitydocuments')->insert(
                [
                  'url' => $file_url,
                  'name' =>$name,
                  'facility_id' => $id,
                  'createdbyuser_id' => Auth::user()->id,
                  'updatedbyuser_id' => Auth::user()->id,
                  'created_at' => date('Y-m-d h:i:s'),
                  'updated_at' => date('Y-m-d h:i:s'),
                ]
              );
              
            } 

            DB::commit();

            return response()->json(['success'=>'Added new records.']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error'=>array($e->getMessage())]);
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

        if (!Auth::user()->can('View-Facility-Details')) {
          return "<p style='color:red; text-align:center'>Permission Denied</p>";
        }

        if ((new FacilityAccessController)->getPemissionById($id) == false) {
          return "<p style='color:red; text-align:center'>You are not allowed to view this facility.</p>";
        }

        $facility = Facility::where(['id' => $id, 'deleted' => '0'])->first();

        $attList = "";

        $attachments = Facilitydocument::where(['facility_id' => $id])->get();

        foreach ($facility->Facilitydocuments as $k => $att) {

            $attList = "<a href='".url('/')."/public/$att->url' download>Download</a>";

        }

        $data = "
          <div class='box box-primary' style='font-weight:bold'>
            <div class='box-body box-profile'>

              <h3 class='profile-username text-center'>$facility->name</h3>

              <p class='text-muted text-center'>$facility->address</p>

              <table class='table table-striped details-view'>
                <tr>
                  <td>Name/Address</td><td>".$facility->name."</td>
                </tr>
                <tr>
                  <td>Code</td><td>$facility->code</td>
                </tr>
                <tr>
                  <td>Has Medicine?</td><td>".($facility->hasMedicine == '1' ? 'YES' : 'NO')."</td>
                </tr>
                <tr>
                  <td>Has Handicap Access?</td><td>".($facility->hasHandicapAccess == '1' ? 'YES' : 'NO')."</td>
                </tr>
                <tr>
                  <td>Is Smoke Free?</td><td>".($facility->isSmokeFree == '1' ? 'YES' : 'NO')."</td>
                </tr>
                <tr>
                  <td>Has Elevator?</td><td>".($facility->hasElevator == '1' ? 'YES' : 'NO')."</td>
                </tr>
                <tr>
                  <td>Type</td><td>$facility->type</td>
                </tr>
                <tr>
                  <td>City</td><td>$facility->city</td>
                </tr>
                <tr>
                  <td>State</td><td>".$facility->State->name."</td>
                </tr>
                <tr>
                  <td>ZIP</td><td>$facility->zip</td>
                </tr>
                <tr>
                  <td>Contact Person</td><td>$facility->contact_p</td>
                </tr>
                <tr>
                  <td>Email</td><td>$facility->email</td>
                </tr>
                <tr>
                  <td>Phone</td><td>$facility->phone</td>
                </tr>
                <tr>
                  <td>Start Date (HASA)</td><td>".(new \App\Custom\Custom)->dateToView($facility->start_date_hasa, "m-d-Y")."</td>
                </tr>
                <tr>
                  <td>Start Date (EHP)</td><td>".(new \App\Custom\Custom)->dateToView($facility->start_date_ehp, "m-d-Y")."</td>
                </tr>
                <tr>
                  <td>Contract Sign Dates</td><td><a href='#'>".(new \App\Custom\Custom)->dateToView($facility->mou_signed_from, "m-d-Y")."</a> - <a href='#'>".(new \App\Custom\Custom)->dateToView($facility->mou_signed_to, "m-d-Y")."</a></td>
                </tr>
                <tr>
                  <td>EIN</td><td>$facility->ein</td>
                </tr>
                <tr>
                  <td>Rate</td><td>$$facility->rate</td>
                </tr>
                <tr>
                  <td>No. of Units as per contract</td><td>$facility->no_of_units</td>
                </tr>
                <tr>
                  <td>Comment</td><td>$facility->comment</td>
                </tr>
                <tr>
                  <td>Document</td><td>$attList</td>
                </tr>
              </table>

              <a href='#' onclick='getEditForm(".$facility->id.")' class='btn btn-primary btn-block'><b>Edit</b></a
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
        if (!Auth::user()->can('Update-Facility')) {
          return "<p style='color:red; text-align:center'>Permission Denied</p>";
        }

        if ((new FacilityAccessController)->getPemissionById($id) == false) {
          return "<p style='color:red; text-align:center'>You are not allowed to edit this facility.</p>";
        }

        $facility = Facility::where(['id' => $id])->first();
        $vendors = Vendor::where(['deleted' => '0', 'active' => '1'])->get();

        $vendroOps = "";

        foreach ($vendors as $key => $value) {

            if ($value->id == $facility->vendor_id) {
                $vendroOps .= "<option value='$value->id' selected>$value->name</option>";
            } else {
                $vendroOps .= "<option value='$value->id'>$value->name</option>";
            }
               
        }

        $states = State::where(['deleted'=>'0', 'active'=>'1'])->get();


        $stateOps = "";

        foreach ($states as $key => $value) {

            if ($value->id == $facility->State->id) {
                $stateOps .= "<option value='".$value->id."' selected>".$value->name."</option>";
            } else {
                $stateOps .= "<option value='".$value->id."'>".$value->name."</option>";
            }

        }

        $data = "
            <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
                <ul></ul>
            </div>

            <form id='editFacility' action='".route('facilities.update',$facility->id)."' method='POST' enctype='multipart/form-data'>

              ".csrf_field()."
              <input type='hidden' name='_method' value='PUT'>

              <div class='col-md-6 col-sm-12 col-xs-12'>

                <div class='form-group'>
                  <label>Vendor <code>*</code></label>
                  <select name='vendor_id' class='form-control'>
                    <option value='' selected=''>Select</option>
                    ".$vendroOps."
                  </select>
                </div>

                <div class='form-group'>
                 <label>Name/Address <code>*</code></label>
                 <input type='text' name='name' placeholder='Name' class='form-control' value='$facility->name'>
                </div>

                <div class='form-group'>
                 <label>Code <code>*</code></label>
                 <input type='text' name='code' id='code' placeholder='Code' class='form-control' value='$facility->code' readonly>
                </div>

                <div class='col-md-6 col-sm-12 col-xs-12'>
                  <div class='form-group'>
                    <label>Has Medicine? <code>*</code></label><br>
                    <input type='radio' name='hasMedicine' id='hasMedicine' value='1' ".($facility->hasMedicine == '1' ? 'checked' : '')."> Yes &nbsp;&nbsp;
                    <input type='radio' name='hasMedicine' id='hasMedicine' value='0' ".($facility->hasMedicine == '0' ? 'checked' : '')."> No 
                  </div>

                  <div class='form-group'>
                    <label>Has Handicap Access? <code>*</code></label><br>
                    <input type='radio' name='hasHandicapAccess' id='hasHandicapAccess' value='1' ".($facility->hasHandicapAccess == '1' ? 'checked' : '')."> Yes &nbsp;&nbsp;
                    <input type='radio' name='hasHandicapAccess' id='hasHandicapAccess' value='0' ".($facility->hasHandicapAccess == '0' ? 'checked' : '')."> No 
                  </div>
                </div>

                <div class='col-md-6 col-sm-12 col-xs-12'>
                  <div class='form-group'>
                    <label>Is Smoke Free? <code>*</code></label><br>
                    <input type='radio' name='isSmokeFree' id='isSmokeFree' value='1' ".($facility->isSmokeFree == '1' ? 'checked' : '')."> Yes &nbsp;&nbsp;
                    <input type='radio' name='isSmokeFree' id='isSmokeFree' value='0' ".($facility->isSmokeFree == '0' ? 'checked' : '')."> No 
                  </div>

                  <div class='form-group'>
                    <label>Has Elevator? <code>*</code></label><br>
                    <input type='radio' name='hasElevator' id='hasElevator' value='1' ".($facility->hasElevator == '1' ? 'checked' : '')."> Yes &nbsp;&nbsp;
                    <input type='radio' name='hasElevator' id='hasElevator' value='0' ".($facility->hasElevator == '0' ? 'checked' : '')."> No 
                  </div>
                </div>

                <div class='form-group'>
                  <label>Family or Single <code>*</code></label><br>
                  <select name='type' class='form-control'>
                    <option value='FAMILY' ".($facility->type == 'FAMILY' ? 'selected' : '').">FAMILY</option>
                    <option value='SINGLE' ".($facility->type == 'SINGLE' ? 'selected' : '').">SINGLE</option>
                  </select>
                </div>

                <div class='form-group'>
                  <label>City <code>*</code></label>
                  <input type='text' class='form-control' name='city' id='city' value='".$facility->city."'>
                </div>

                <div class='form-group'>
                  <label>State <code>*</code></label>
                  <select name='state_id' class='form-control'>
                    <option value='' selected=''>Select</option>
                    ".$stateOps."
                  </select>
                </div>

                <div class='form-group'>
                  <label>ZIP <code>*</code></label>
                  <input type='text' name='zip' class='form-control' placeholder='ZIP' value='".$facility->zip."'>
                </div>

                <div class='form-group'>
                  <label>Contact Person</label>
                  <input type='text' name='contact_p' class='form-control' placeholder='Contact Person' value='".$facility->contact_p."'>
                </div>

                <div class='form-group'>
                  <label>Email</label>
                  <input type='email' name='email' class='form-control' placeholder='Email' value='".$facility->email."'>
                </div>

              </div>


              <div class='col-md-6 col-sm-12 col-xs-12'>

                <div class='form-group'>
                  <label>Phone</label>

                  <div class='input-group'>
                    <div class='input-group-addon'>
                      <i class='fa fa-phone'></i>
                    </div>
                    <input type='text' name='phone' id='phone' class='form-control' data-inputmask=''mask': '(999) 999-9999'' data-mask value='".$facility->phone."'>
                  </div>
                </div>

                <div class='form-group'>
                  <label>Start Date (HASA) <code>*</code></label>
                  <div class='input-group date'>
                    <div class='input-group-addon'>
                      <i class='fa fa-calendar'></i>
                    </div>
                    <input type='text' class='form-control pull-right date' id='start_date_hasa' name='start_date_hasa' value='".(new \App\Custom\Custom)->dateToView($facility->start_date_hasa, "m-d-Y")."'>
                  </div>
                </div>

                <div class='form-group'>
                  <label>Start Date (EHP) <code>*</code></label>
                  <div class='input-group date'>
                    <div class='input-group-addon'>
                      <i class='fa fa-calendar'></i>
                    </div>
                    <input type='text' class='form-control pull-right date' id='start_date_ehp' name='start_date_ehp' value='".(new \App\Custom\Custom)->dateToView($facility->start_date_ehp, "m-d-Y")."'>
                  </div>
                </div>


                <div class='form-group'>
                  <label>Contract Signed From</label>
                  <div class='input-group date'>
                    <div class='input-group-addon'>
                      <i class='fa fa-calendar'></i>
                    </div>
                    <input type='text' class='form-control pull-right date' id='mou_signed_from' name='mou_signed_from' value='".(new \App\Custom\Custom)->dateToView($facility->mou_signed_from, "m-d-Y")."'>
                  </div>
                </div>


                <div class='form-group'>
                  <label>Contract Signed To</label>
                  <div class='input-group date'>
                    <div class='input-group-addon'>
                      <i class='fa fa-calendar'></i>
                    </div>
                    <input type='text' class='form-control pull-right date' id='mou_signed_to' name='mou_signed_to' value='".(new \App\Custom\Custom)->dateToView($facility->mou_signed_to, "m-d-Y")."'>
                  </div>
                </div>


                <div class='form-group'>
                  <label>EIN <code>*</code></label>
                  <input type='text' name='ein' class='form-control' placeholder='EIN' value='$facility->ein'>
                </div>


                <div class='form-group'>
                  <label>Rate <code>*</code></label>
                  <input type='text' name='rate' class='form-control' placeholder='Rate' value='$facility->rate'>
                </div>


                <div class='form-group'>
                  <label>No. of Units as per contract</label>
                  <input type='text' name='no_of_units' class='form-control' placeholder='No of Units' value='$facility->no_of_units'>
                </div>

                <div class='form-group'>
                  <label>Comment</label>
                  <textarea rows='2' class='form-control' name='comment' id='comment'>$facility->comment</textarea>
                </div>

                <div class='form-group'>
                  <label>Contact Papers</label>
                  <input type='file' name='contact_paper' id='contact_paper'>
                </div>

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

      if (!Auth::user()->can('Update-Facility')) {
        return response()->json(['error' => array('You do not have enough permission(s)')]);
      }

        $validator = Validator::make($request->all(), [

            'vendor_id' => 'required',
            'name' => 'required|max:100',
            'code' => 'required|unique:facilities,code,'.$id,
            'hasMedicine' => 'required',
            'hasHandicapAccess' => 'required',
            'isSmokeFree' => 'required',
            'hasElevator' => 'required',
            'city' => 'required',
            'state_id' => 'required',
            'zip' => 'required',
            'type' => 'required',
            'start_date_hasa' => 'required',
            'start_date_ehp' => 'required',
            'ein' => 'required',
            'rate' => 'required|numeric'
        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }


        try {

          DB::beginTransaction();

            Facility::where(['id' => $id])->update(
                [
                    'vendor_id' => $request->vendor_id,
                    'code' => $request->code,
                    'name' => $request->name,
                    'city' => $request->city,
                    'state_id' => $request->state_id,
                    'zip' => $request->zip,
                    'contact_p' => $request->contact_p,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'comment' => $request->comment,
                    'hasMedicine' => $request->hasMedicine,
                    'hasHandicapAccess' => $request->hasHandicapAccess,
                    'isSmokeFree' => $request->isSmokeFree,
                    'hasElevator' => $request->hasElevator,
                    'type' => $request->type,
                    'start_date_hasa' => (new \App\Custom\Custom)->convertDate($request->start_date_hasa, "Y-m-d"),
                    'start_date_ehp' => (new \App\Custom\Custom)->convertDate($request->start_date_ehp, "Y-m-d"),
                    'ein' => $request->ein,
                    'rate' => $request->rate,
                    'mou_signed_from' => (new \App\Custom\Custom)->convertDate($request->mou_signed_from, "Y-m-d"),
                    'mou_signed_to' => (new \App\Custom\Custom)->convertDate($request->mou_signed_to, "Y-m-d"),
                    'no_of_units' => $request->no_of_units,
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d h:i:s'),
                ]
            );

            if($request->hasFile('contact_paper')) {

              $hasEntry = Facilitydocument::where(['facility_id' => $id, 'deleted' => '0'])->get();

              if (count(Facilitydocument::where(['facility_id' => $id, 'deleted' => '0'])->get()) > 0) {
                DB::table('facilitydocuments')->where('facility_id',$id)->delete();
              }

              $file = $request->file('contact_paper');
              $name = $id. '-'.time().'-'.$request->code.'.'.$file->getClientOriginalExtension();
              $file->move(public_path().'/file/facilities/', $name);
              $file_url = "file/facilities/$name";

              DB::table('facilitydocuments')->insert(
                [
                  'url' => $file_url,
                  'name' =>$name,
                  'facility_id' => $id,
                  'createdbyuser_id' => Auth::user()->id,
                  'updatedbyuser_id' => Auth::user()->id,
                  'created_at' => date('Y-m-d h:i:s'),
                  'updated_at' => date('Y-m-d h:i:s'),
                ]
              );

            } 

            DB::commit();

            return response()->json(['success'=>'Record updated.']);


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

    public function getDeletion($id) {

      if (!Auth::user()->can('Delete-Facility')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editFacility' action='".route('postFacilityDeletion',$id)."' method='POST'>

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

        if (!Auth::user()->can('Delete-Facility')) {
          return response()->json(['error' => array('You do not have enough permission(s)')]);
        }

        $facility = Facility::where(['id' => $id])->first();

        $isOk = count($facility->Apartment($id));
        $hasInspection = count(Inspection::where(['facility_id' => $id, 'deleted' => '0', 'active' => '1'])->get());

        if ($isOk < 1 && $hasInspection < 1) {
            
          try {

              DB::beginTransaction();

              Facility::where(['id' => $id])->update(
                  [
                      'deleted' => '1',
                      'updatedbyuser_id' => Auth::user()->id,
                      'updated_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::table('facilityactions')->insert(
                  [
                      'facility_id' => $id,
                      'action' => 'deleted',
                      'deleted' => '0',
                      'createdbyuser_id' => Auth::user()->id,
                      'created_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::commit();

              return response()->json(['success'=>'Deleted.']);

              
          } catch (\Exception $e) {
              DB::rollback();
              return response()->json(['error'=>array('Cannot delete')]);
          }

        } else {
          return response()->json(['error'=>array("Vendor has associated apartment(s) or Inspection")]);
        }     

    }


    public function getFacilityActivation($id) {
      
      if (!Auth::user()->can('Facility-Activation')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      $isActive = Facility::where(['id' => $id])->first();

      if ($isActive->active == 1) {
        $action = "<option value='0'>Offline</option>";
      } else {
        $action = "<option value='1'>Activate</option>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editFacility' action='".route('postFacilityActivation',$id)."' method='POST'>

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


    public function postFacilityActivation(Request $request,$id){

      if (!Auth::user()->can('Facility-Activation')) {
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
          $action = "Non-Referral";
        }
            
        try {

            DB::beginTransaction();

            Facility::where(['id' => $id])->update(
                [
                    'active' => $request->active,
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d h:i:s'),
                ]
            );

            DB::table('facilityactions')->insert(
                [
                    'facility_id' => $id,
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
    
}
