<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\State;
use App\Vendor;
use App\Facility;
use App\Floor;
use App\Apartment;
use App\Vendoraction;
use Validator;
use Auth;
use App\User;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
      if(!(Auth::user()->can('View-Vendors'))) {
        return view('pages.550');
      }

      $vendors = Vendor::where(['deleted'=>'0'])->orderBy('created_at', 'DESC')->get();
      $states = State::where(['deleted'=>'0', 'active'=>'1'])->get();
      return view('pages.vendors', compact('states','vendors'));
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
        if(!(Auth::user()->can('Create-Vendor'))) {
           return response()->json(['error'=>array('You do not have enough permission(s)')]);
        }

        $validator = Validator::make($request->all(), [

            'name' => 'required|max:255',
            'city' => 'required',
            'address' => 'required',
            'state_id' => 'required',
            'zip' => 'required',
            'email' => 'required|email',
            'phone' => 'required'

        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        DB::beginTransaction();

        try {

            $id = DB::table('vendors')->insertGetId(
                [
                    'code' => time(),
                    'name' => $request->name,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state_id' => $request->state_id,
                    'contact_person' => $request->contact_person,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'fax' => $request->fax,
                    'zip' => $request->zip,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                ]
            );

            DB::table('vendoractions')->insert(
                [
                    'vendor_id' => $id,
                    'action' => 'Created',
                    'deleted' => '0',
                    'createdbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                ]
            );

            $code = 'VEN'.sprintf('%06d', $id);

            Vendor::where(['id' => $id])->update(
              [
                'code' => $code,
              ]
            );

            DB::commit();

            return response()->json(['success'=>'Added new records.']);
   
        } catch (\Exception $e) {

          DB::rollback();
          return response()->json(['error'=>'Could not add new record.']);
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
        if(!(Auth::user()->can('View-Vendor-Details'))) {
          return "<p style='color:red; text-align:center'>Permission Denied</p>";
        }

        $vendor = Vendor::where(['id' => $id, 'deleted' => '0'])->first();

        $data = "
          <div class='box box-primary' style='font-weight:bold'>
            <div class='box-body box-profile'>

              <h3 class='profile-username text-center'>$vendor->name</h3>

              <p class='text-muted text-center'>$vendor->address</p>

              <table class='table table-striped details-view'>
                <tr>
                  <td>State</td><td>".$vendor->State->name."</td>
                </tr>
                <tr>
                  <td>Code</td><td>$vendor->code</td>
                </tr>
                <tr>
                  <td>City</td><td>$vendor->city</td>
                </tr>
                <tr>
                  <td>ZIP</td><td>$vendor->zip</td>
                </tr>
                <tr>
                  <td>Address</td><td>$vendor->address</td>
                </tr>
                <tr>
                  <td>Contact Person</td><td>$vendor->contact_person</td>
                </tr>
                <tr>
                  <td>Email</td><td>$vendor->email</td>
                </tr>
                <tr>
                  <td>Phone</td><td>$vendor->phone</td>
                </tr>
                <tr>
                  <td>FAX</td><td>$vendor->fax</td>
                </tr>
              </table>

              <a href='#' onclick='getEditForm(".$vendor->id.")' class='btn btn-primary btn-block'><b>Edit</b></a>
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

        if(!(Auth::user()->can('Update-Vendor'))) {
          return "<p style='color:red; text-align:center'>Permission Denied</p>";
        }

        $vendor = Vendor::where(['deleted'=>'0', 'active'=>'1', 'id' => $id])->first();
        $states = State::where(['deleted'=>'0', 'active'=>'1'])->get();


        $stateOps = "";

        foreach ($states as $key => $value) {

            if ($value->id == $vendor->State->id) {
                $stateOps .= "<option value='".$value->id."' selected>".$value->name."</option>";
            } else {
                $stateOps .= "<option value='".$value->id."'>".$value->name."</option>";
            }

        }


        
        $data = "
            <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
                <ul></ul>
            </div>

            <form id='editVendor' action='".route('vendors.update',$vendor->id)."' method='POST'>

              ".csrf_field()."

              <input type='hidden' name='_method' value='PUT'>

              <div class='form-group'>
               <label>Name <code>*</code></label>
               <input type='text' name='name' placeholder='Name' class='form-control' value='".$vendor->name."' required>
              </div>

              <div class='form-group'>
                <label>Address <code>*</code></label>
                <textarea rows='2' class='form-control' name='address'>".$vendor->address."</textarea>
              </div>

              <div class='form-group'>
                <label>City <code>*</code></label>
                <input type='text' name='city' class='form-control' placeholder='City' value='".$vendor->city."' required>
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
                <input type='text' name='zip' class='form-control' placeholder='ZIP' value='".$vendor->zip."' required>
              </div>

              <div class='form-group'>
                <label>Contact Person</label>
                <input type='text' name='contact_person' class='form-control' placeholder='Contact Person' value='".$vendor->contact_person."' required>
              </div>

              <div class='form-group'>
                <label>Email <code>*</code></label>
                <input type='text' name='email' class='form-control' placeholder='Email' value='".$vendor->email."' required>
              </div>

              <div class='form-group'>
                <label>Phone <code>*</code></label>
                <input type='text' name='phone' class='form-control' placeholder='phone' value='".$vendor->phone."' required>
              </div>

              <div class='form-group'>
                <label>FAX</label>

                <div class='input-group'>
                  <div class='input-group-addon'>
                    <i class='fa fa-fax'></i>
                  </div>
                  <input type='text' name='fax' id='fax' class='form-control' placeholder='FAX' value='".$vendor->fax."' required>
                </div>
              </div>

              <button class='btn btn-block btn-success btn-sm' id='editData' type='submit'>SAVE</button>
              <button class='btn btn-block btn-success btn-sm' id='loadingEdit' style='display: none' disabled=''>Working...</button>

            </form>
        ";

        sleep(1);

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

        if(!(Auth::user()->can('Update-Vendor'))) {
          return response()->json(['error'=>array('You do not have enough permission.')]);
        }

        $validator = Validator::make($request->all(), [

            'name' => 'required|max:255',
            'city' => 'required',
            'address' => 'required',
            'state_id' => 'required',
            'zip' => 'required',
            'email' => 'required|email',
            'phone' => 'required',

        ]);


        if ($validator->fails()) {

            return response()->json(['error'=>$validator->errors()->all()]);
        }

        try {

            Vendor::where(['id' => $id])->update(
                [
                    'name' => $request->name,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state_id' => $request->state_id,
                    'contact_person' => $request->contact_person,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'fax' => $request->fax,
                    'zip' => $request->zip,
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d'),
                ]
            );

            return response()->json(['success'=>'Updated Successfully!.']);

            
        } catch (\Exception $e) {
            return $e.getMessage();
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


    public function getVendorActivation($id) {
      
      if (!Auth::user()->can('Vendor-Activation')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      $isActive = Vendor::where(['id' => $id])->first();

      if ($isActive->active == 1) {
        $action = "<option value='0'>Deactivate</option>";
      } else {
        $action = "<option value='1'>Activate</option>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editVendor' action='".route('postVendorActivation',$id)."' method='POST'>

            ".csrf_field()."

            <input type='hidden' name='_method' value='PUT'>

            <div class='form-group'>
              <label>Status <code>*</code></label>
              <select name='active' class='form-control'>
                $action
              </select>
            </div>


            <button class='btn btn-block btn-success btn-sm' id='editData' type='submit'>SAVE</button>
            <button class='btn btn-block btn-success btn-sm' id='loadingEdit' style='display: none' disabled=''>Working...</button>

          </form>
      ";

    }



    public function getDeletion($id) {

      if (!Auth::user()->can('Delete-Vendor')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editVendor' action='".route('postVendorDeletion',$id)."' method='POST'>

            ".csrf_field()."

            <input type='hidden' name='_method' value='PUT'>

            <h3 style='text-align: center'>You will not be able to revert changes.
              <br>Are you sure to submit?
            </h3>


            <button class='btn btn-block btn-danger btn-sm' id='editData' type='submit'>YES</button>
            <button class='btn btn-block btn-success btn-sm' id='loadingEdit' style='display: none' disabled=''>Working...</button>

          </form>
      ";

    }


    public function postDeletion(Request $request,$id){

      if (!Auth::user()->can('Delete-Vendor')) {
        return response()->json(['error'=>array("You do not have enough permission(s)")]);
      }

        $vendor = Vendor::where(['id' => $id])->first();

        $isOk = count($vendor->Facility->where('deleted','0'));

        if ($isOk < 1) {
            
          try {

              DB::beginTransaction();

              Vendor::where(['id' => $id])->update(
                  [
                      'deleted' => '1',
                      'updatedbyuser_id' => Auth::user()->id,
                      'updated_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::table('vendoractions')->insert(
                  [
                      'vendor_id' => $id,
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
          return response()->json(['error'=>array("Vendor has $isOk facilities")]);
        }     

    }


    public function postVendorActivation(Request $request,$id){

      if (!Auth::user()->can('Vendor-Activation')) {
        return response()->json(['error'=>array("You do not have enough permission(s)")]);
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

        $vendor = Vendor::where(['id' => $id])->first();

        $isOk = count($vendor->Facility->where('active','1'));

        if ($isOk < 1) {
            
          try {

              DB::beginTransaction();

              Vendor::where(['id' => $id])->update(
                  [
                      'active' => $request->active,
                      'updatedbyuser_id' => Auth::user()->id,
                      'updated_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::table('vendoractions')->insert(
                  [
                      'vendor_id' => $id,
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
          return response()->json(['error'=>array("Vendor has $isOk active facilities")]);
        }     

    }


}
