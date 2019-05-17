<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Client;
use App\Clientaction;
use App\Precondition;
use App\Admissionhistory;
use App\Facility;
use App\Admission;
use App\Clienttransferhistory;
use Validator;
use Auth;
use App\Http\Controllers\AdmissionController;
use App\Preconditionchange;
use App\Rules\ValidSsn;
use App\Rules\ValidateDOB;
use App\Rules\UniqueMedica;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!(Auth::user()->can('View-Clients'))) {
          return view('pages.550');
        }

        $clients = Client::where(['deleted'=>'0'])->orderBy('created_at', 'DESC')->get();
        return view('pages.clients', compact('clients'));
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

      if(!(Auth::user()->can('Create-Client'))) {
        return response()->json(['error'=>array('You do not have enough permission(s)')]);
      }

        $validator = Validator::make($request->all(), [
            'fname' => 'required|max:255',
            'lname' => 'required|max:255',
            'ssn'   => ['required', 'max:11', 'min:9',new ValidSsn(null)],
            'medicaid' => ['nullable',new UniqueMedica(null)],
            'dob'   => [new ValidateDOB],
            'email' => 'nullable|email|unique:clients',
            'phone' => 'nullable|unique:clients',
            'img_url' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $precondiotionId = Precondition::where(['name'=> 'Referral'])->first();

        DB::beginTransaction();

        try {

            if($request->hasFile('img_url')) {
              
               $file = $request->file('img_url');
               $name = $request->ssn.'.'.$file->getClientOriginalExtension();
               $file->move(public_path().'/images/client', $name);

            } else {
                $name = 'avatar-male.png';
            }

            if($request->hasFile('ref')) {
              
               $ref = $request->file('ref');
               $refname = $request->ssn.'-referral-letter.'.$ref->getClientOriginalExtension();
               $ref->move(public_path().'/images/client', $refname);
               $refUrl = "/public/images/client/".$refname;

            } else {
                $refUrl = null;
            }

            $precondition = Precondition::where([
                            'name' => $request->precondition, 
                            'active' => '1', 
                            'deleted' => '0'
                          ])->first();

            $id = DB::table('clients')->insertGetId(
                [
                    'code' => time(),
                    'fname' => $request->fname,
                    'lname' => $request->lname,
                    'ssn'   => (new CryptoController)->my_simple_crypt($request->ssn,'e'),
                    'medicaid' => (new CryptoController)->my_simple_crypt($request->medicaid,'e'),
                    'dob'   => (new \App\Custom\Custom)->convertDate($request->dob, "Y-m-d"),
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'precondition_id' => $precondition->id,
                    'comment' => $request->comment,
                    'ref_letter' => $refUrl,
                    'img_url' => "/public/images/client/".$name,
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                ]
            );

            DB::table('clientactions')->insert(
                [
                    'client_id' => $id,
                    'action' => 'created',
                    'deleted' => '0',
                    'createdbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                ]
            );

            $code = 'CLS'.sprintf('%06d', $id);

            Client::where(['id' => $id])->update(
              [
                'code' => $code,
              ]
            );

            DB::table('preconditionchanges')->insert(
                [
                    'client_id' => $id,
                    'precondition_id' => $precondiotionId->id,
                    'comment' => "New Client",
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                ]
            );

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

      if(!(Auth::user()->can('View-Client-Details'))) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

        $client = Client::where(['id' => $id, 'deleted' => '0'])->first();

        if ($client->Precondition->name == 'Admitted') {
          $preconditions = Precondition::where(['deleted' => '0', 'active' => '1'])->whereNotIn('name',[$client->Precondition->name, 'Discharged', 'Admitted', 'Transferred', 'Referral', 'No Show'])->get();
        }
		elseif ($client->Precondition->name == 'Transferred') {
          $preconditions = Precondition::where(['deleted' => '0', 'active' => '1'])->whereNotIn('name',[$client->Precondition->name, 'Discharged', 'Admitted', 'Transferred', 'Referral', 'No Show'])->get();
        }
		elseif ($client->Precondition->name == 'Discharged') {
          $preconditions = Precondition::where(['deleted' => '0', 'active' => '1'])->whereNotIn('name',[$client->Precondition->name, 'Discharged', 'Admitted', 'Transferred', 'Missing', 'Arrested', 'Incarcerated', 'Detox', 'Rehab', 'Substance Abuse Program', 'Hospital', 'Deceased'])->get();
        }
		elseif ($client->Precondition->name == 'Referral') {
          $preconditions = Precondition::where(['deleted' => '0', 'active' => '1'])->whereNotIn('name',[$client->Precondition->name, 'Discharged', 'Admitted', 'Transferred', 'Referral', 'Missing', 'Arrested', 'Incarcerated', 'Detox', 'Rehab', 'Substance Abuse Program', 'Hospital', 'Deceased'])->get();
        }
		else {
          $preconditions = Precondition::where(['deleted' => '0', 'active' => '1'])->whereNotIn('name',[$client->Precondition->name, 'Discharged', 'Admitted', 'Transferred'])->get();
        }

        $statusOps = '';

        foreach ($preconditions as $k => $v) {
          $statusOps .= "<option value='$v->id'>$v->name</option>";
        }


        $buildings = Facility::where(['deleted'=>'0', 'active'=>'1'])->orderBy('code', 'ASC')->get();

        $buildingOps = "";

        foreach ($buildings as $key => $value) {
          $buildingOps .= "<option value='$value->id'>$value->code - $value->name</option>";
        }

        /*if (!empty($client->dob)) {
          $dob = date('m-d-Y', strtotime($client->dob));
        } else {
          $dob = $client->dob;
        }*/

        if ($client->currentAdmission($id) == false) {
          $admission_form = "

          <p> You can also admit this client from <b> Home->Client Management->Admissions<b></p>

          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='admissions' action='".route('admissions.store')."' method='POST'>

            ".csrf_field()."

            <input type='hidden' name='client_id' value='$id'>

            <div class='form-group'>
              <label>Move in date:</label>
              <div class='input-group date moveindate'>
                <div class='input-group-addon'>
                  <i class='fa fa-calendar'></i>
                </div>
                <input type='text' class='form-control pull-right' id='datepicker2' name='moveindate' readonly=''>
              </div>
            </div>


            <div class='form-group'>
             <label>Building</label>
             <select name='building' id='building' class='form-control'>
                <option value=''>SELECT</option>
                $buildingOps
             </select>
            </div>

            <div class='form-group'>
             <label>Apartment</label>
             <select name='apartment_id' id='apartment_id' class='form-control'>
               <option value=''>SELECT</option>
             </select>
            </div>

            <button class='btn btn-block btn-primary btn-sm' id='submitAdmission' type='submit' onclick='admission(event)'>SUBMIT</button>
            <button class='btn btn-block btn-primary btn-sm' id='loading' style='display: none' disabled=''>Working...</button>

          </form>";
        } else {
          $admission = Admission::where(['client_id' => $id,])->orderBy('created_at', 'DESC')->first();
          $admission_form = (new AdmissionController)->dischargeForm($admission->id);
        }


        $admission = Admission::where(['client_id' => $client->id, 'active' => '1', 'deleted' => '0'])->where('moveoutdate', '=', null)->first();

        $transfer = "";

        if (!empty($admission)) {

          $transfer = "

                <div class='alert alert-danger print-error-msg' id='error_messages_transfer' style='display:none'>
                    <ul></ul>
                </div>

                <form id='clientTransfer' action='".route('admissions.transfer')."' method='POST'>

                  ".csrf_field()."

                  <p>Admitted in Facility: <b>".$admission->Apartment->Floor->Facility->name."</b> | Unit: <b>".$admission->Apartment->name."</b></p>

                  <input type='hidden' name='client_id' value='$id'>

                  <input type='hidden' name='admission_id' value='$admission->id'>

                  <div class='form-group'>
                    <label>Transfer date:</label>
                    <div class='input-group date moveindate'>
                      <div class='input-group-addon'>
                        <i class='fa fa-calendar'></i>
                      </div>
                      <input type='text' class='form-control pull-right' id='datepicker2' name='moveindate' readonly=''>
                    </div>
                  </div>

                  <div class='form-group'>
                   <label>Building</label>
                   <select name='building' id='building' class='form-control'>
                      <option value=''>SELECT</option>
                      <option value='".$admission->Apartment->Floor->Facility->id."'>".$admission->Apartment->Floor->Facility->code." - ".$admission->Apartment->Floor->Facility->name." </option>
                   </select>
                  </div>

                  <div class='form-group'>
                   <label>Apartment</label>
                   <select name='apartment_id' id='apartment_id' class='form-control'>
                     <option value=''>SELECT</option>
                   </select>
                  </div>

                  <div class='form-group'>
                   <label>Comment</label>
                   <textarea class='form-control' name='comment' id='comment_transfer'></textarea>
                  </div>

                  <button class='btn btn-block btn-primary btn-sm' id='submitTransfer' type='submit'>SUBMIT</button>
                  <button class='btn btn-block btn-primary btn-sm' id='loading' style='display: none' disabled=''>Working...</button>

                </form>

          ";
        } 

        
        if ($client->ref_letter != null) {
          $reflet = "<a  href='".url('/')."/$client->ref_letter' download>Download</a>";
        } else {
          $reflet = "";
        }

        $data = "
          
            <div class='nav-tabs-custom'>
              <ul class='nav nav-tabs'>
                <li class='active'><a href='#profile' data-toggle='tab'>Profile</a></li>
                <li><a href='#updatestatus' data-toggle='tab'>Update Status</a></li>
                <li><a href='#admission' data-toggle='tab'>Admission/Discharge</a></li>
                ".(!empty($admission) == true ? "<li><a href='#transfer' data-toggle='tab'>Transfer</a></li>":'')."
              </ul>
              <div class='tab-content'>
                
                <div class='active tab-pane' id='profile'>

                    <div class='box box-primary' style='font-weight:bold'>
                        <div class='box-body box-profile'>

                          <img class='profile-user-img img-responsive img-responsive' src='".url('/')."$client->img_url' alt='Picture'>

                          <h3 class='profile-username text-center'>$client->fname $client->lname</h3>

                          <p class='text-muted text-center'>Client ID: $client->code</p>

                          <table class='table table-striped details-view'>
                            <tr>
                              <td>First Name</td><td>$client->fname</td>
                            </tr>
                            <tr>
                              <td>Last Name</td><td>$client->lname</td>
                            </tr>
                            <tr>
                              <td>Client ID</td><td>$client->code</td>
                            </tr>

                            <tr>
                              <td>Social Security Number</td><td>".(new CryptoController)->my_simple_crypt($client->ssn,'d')."</td>
                            </tr>

                            <tr>
                              <td>Medication ID No</td><td>".(new CryptoController)->my_simple_crypt($client->medicaid,'d')."</td>
                            </tr>

                            <tr>
                              <td>Date of Birth</td><td>".(new \App\Custom\Custom)->dateToView($client->dob, "m-d-Y")."</td>
                            </tr>

                            <tr>
                              <td>Email</td><td>$client->email</td>
                            </tr>

                            <tr>
                              <td>Phone</td><td>$client->phone</td>
                            </tr>

                            <tr>
                              <td>Status</td><td>".$client->Precondition->name."</td>
                            </tr>

                            <tr>
                              <td>Comment</td><td>$client->comment</td>
                            </tr>

                            <tr>
                              <td>Referral Letter</td><td>$reflet</td>
                            </tr>

                            <tr>
                              <td>Active</td><td>".($client->active == '1' ? 'YES' : 'NO')."</td>
                            </tr>

                          </table>

                          <a href='#' onclick='getEditForm(".$client->id.")' class='btn btn-primary btn-block'><b>Edit</b></a>

                        </div>
                      </div>
                    </div>


                <div class='tab-pane' id='updatestatus'>

                    <div class='alert alert-danger print-error-msg' style='display:none'>
                      <ul></ul>
                    </div>

                    <form id='status' action='".route('clients.updateStatus',$client->id)."' method='POST'>

                        ".csrf_field()."

                        <input type='hidden' name='_method' value='PUT'>
                        
                        <input type='hidden' name='client_id' value='$client->id'>

                        <div class='form-group'>
                         <label>Status</label>
                         <select name='precondition_id' class='form-control'>
                            <option value=''>SELECT</option>
                            $statusOps
                         </select>
                        </div>

                        <div class='form-group'>
                         <label>Comment</label>
                         <textarea name='comment' class='form-control' cols='3'></textarea>
                        </div>

                        <button class='btn btn-block btn-primary btn-sm' id='statupdate' type='submit'>UPDATE</button>
                        <button class='btn btn-block btn-primary btn-sm' id='loading2' style='display: none' disabled=''>Working...</button>

                    </form>
                </div>



                <div class='tab-pane' id='admission'>
                  $admission_form
                </div>

                <div class='tab-pane' id='transfer'>
                  $transfer
                </div>

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

      if (!Auth::user()->can('Update-Client')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }
        
        $client = Client::where(['id' => $id])->first();

        $data = "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editClient' action='".route('clients.update',$id)."' method='POST' enctype='multipart/form-data'>

            ".csrf_field()."
              
            <input type='hidden' name='_method' value='PUT'>

            <div class='form-group'>
             <label>First Name <code>*</code></label>
             <input type='text' name='fname' placeholder='First Name' class='form-control' value='$client->fname' required>
            </div>

            <div class='form-group'>
             <label>Last Name <code>*</code></label>
             <input type='text' name='lname' placeholder='Last Name' class='form-control' value='$client->lname' required>
            </div>

            <div class='form-group'>
             <label>SSN <code>*</code></label>
             <input type='text' id='ssn' maxlength='9' name='ssn' placeholder='SSN' class='form-control' value='".(new CryptoController)->my_simple_crypt($client->ssn,'d')."' required>
            </div>

            <div class='form-group'>
             <label>Medication ID No</label>
             <input type='text' name='medicaid' placeholder='Medication ID' class='form-control' value='".(new CryptoController)->my_simple_crypt($client->medicaid,'d')."' required>
            </div>

            <div class='form-group'>
              <label>Date of Birth</label>
              <div class='input-group date'>
                <div class='input-group-addon'>
                  <i class='fa fa-calendar'></i>
                </div>
                <input type='text' class='form-control pull-right' id='datepicker1' value='".(new \App\Custom\Custom)->dateToView($client->dob, "m-d-Y")."' name='dob'>
              </div>
            </div>

            <div class='form-group'>
             <label>Email</label>
             <input type='email' name='email' placeholder='Email' class='form-control' value='$client->email' required>
            </div>

            <div class='form-group'>
              <label>Phone:</label>

              <div class='input-group'>
                <div class='input-group-addon'>
                  <i class='fa fa-phone'></i>
                </div>
                <input type='text' name='phone' id='phone' class='form-control' data-inputmask=''mask': '(999) 999-9999'' data-mask value='$client->phone' required>
              </div>
            </div>

            <div class='form-group'>
              <label>Comment</label>
              <textarea cols='3' name='comment' class='form-control'>$client->comment</textarea>
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

      if (!Auth::user()->can('Update-Client')) {
        return response()->json(['error'=>array('You do not have enough permission(s)')]);
      }

        $validator = Validator::make($request->all(), [
            'fname' => 'required|max:255',
            'lname' => 'required|max:255',
            'ssn'   => ['required', 'max:11', 'min:9', new ValidSsn($id)],
            'dob'   => [new ValidateDOB],
            'medicaid' => ['nullable',new UniqueMedica($id)],
            'email' => 'nullable|email|unique:clients,email,'.$id,
            'phone' => 'nullable|unique:clients,phone,'.$id
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        try {

            Client::where(['id' => $id])->update(
                [
                    'fname' => $request->fname,
                    'lname' => $request->lname,
                    'ssn'   => (new CryptoController)->my_simple_crypt($request->ssn,'e'),
                    'medicaid' => (new CryptoController)->my_simple_crypt($request->medicaid,'e'),
                    'dob'   => (new \App\Custom\Custom)->convertDate($request->dob, "Y-m-d"),
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'comment' => $request->comment,
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d h:i:s'),
                ]
            );
            DB::commit();

            return response()->json(['success'=>'Record updated.']);
        
        } catch (\Exception $e) {

          DB::rollback();
          //return response()->json(['error'=>array('Could not add Client')]);
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


    /**
     * Update Client Status
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateClientStatus(Request $request, $id)
    {

      if (!Auth::user()->can('Update-Client-Status')) {
        return response()->json(['error'=>array('You do not have enough permission(s)')]);
      }

        $validator = Validator::make($request->all(), [
            'precondition_id' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        
        try{

            DB::beginTransaction();

            DB::table('clients')
            ->where('id', $id)
            ->update([
                        'precondition_id' => $request->precondition_id,
                        'updated_at' => date('Y-m-d h:i:s'),
                        'updatedbyuser_id' => Auth::user()->id,
                    ]);


            DB::table('preconditionchanges')
            ->insert([
                        'client_id' => $request->client_id,
                        'precondition_id' => $request->precondition_id, 
                        'comment' => $request->comment,
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s'),
                        'createdbyuser_id' => Auth::user()->id,
                        'updatedbyuser_id' => Auth::user()->id,
                    ]);

            DB::commit();

            return response()->json(['success'=>'New Client Added.']);

        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }


    public function searchClient(Request $request){
        
        $client = DB::select("
                SELECT
                  *
                FROM
                  (
                    SELECT
                      C.id,
                      C.code,
                      C.fname,
                      C.lname,
                      CONCAT(C.fname,' ',C.lname) AS full_name,
                      C.email,
                      C.phone,
                      C.ssn
                    FROM
                      clients C
                    INNER JOIN preconditions P ON P.id = C.precondition_id
                    AND P. NAME IN (
                      'Referral',
                      'Transferred',
                      'Discharged'
                    )
                    WHERE
                      C.active = '1'
                    AND C.deleted = '0'
                  ) FOO
                WHERE
                  FOO.code LIKE '%$request->input%'
                OR FOO.fname LIKE '%$request->input%'
                OR FOO.lname LIKE '%$request->input%'
                OR FOO.full_name LIKE '%$request->input%'
                OR FOO.email LIKE '%$request->input%'
                OR FOO.ssn LIKE '%$request->input%'
            ");

        if (!empty($client)) {

            $clientData = array();

            foreach ($client as $k => $value) {
              $clientData[$k]['id'] = $value->id;
              $clientData[$k]['code'] = $value->code;
              $clientData[$k]['fname'] = $value->fname;
              $clientData[$k]['lname'] = $value->lname;
              $clientData[$k]['phone'] = $value->phone;
              $clientData[$k]['ssn'] = (new CryptoController)->my_simple_crypt($value->ssn,'d');
            }

            return json_encode($clientData);

        }

        return json_encode([
            'error' => 'No data found',
        ]);
    }



    public function getDeletion($id) {

      if (!Auth::user()->can('Delete-Client')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editClient' action='".route('postClientnDeletion',$id)."' method='POST'>

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

      if (!Auth::user()->can('Delete-Client')) {
        return response()->json(['error'=>array('You do not have enough permission(s)')]);
      }

        $client = Client::where(['id' => $id])->first();

        $isOk = count($client->Admission->where('deleted','0'));

        if ($isOk < 1) {
            
          try {

              DB::beginTransaction();

              Client::where(['id' => $id])->update(
                  [
                      'deleted' => '1',
                      'updatedbyuser_id' => Auth::user()->id,
                      'updated_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::table('clientactions')->insert(
                  [
                      'client_id' => $id,
                      'action' => 'deleted',
                      'deleted' => '0',
                      'createdbyuser_id' => Auth::user()->id,
                      'created_at' => date('Y-m-d h:i:s'),
                  ]
              );

              Preconditionchange::where(['client_id' => $id])->update(
                  [
                      'deleted' => '1',
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
          return response()->json(['error'=>array("Client cannot be deleted")]);
        }     

    }


    public function getClientHistory($id) {

      if (!Auth::user()->can('View-Client-History')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }
      
      $admissionhistories = Admissionhistory::join('admissions', 'admissionhistories.admission_id', '=', 'admissions.id')
                    ->join('users', 'users.id', '=', 'admissionhistories.updatedbyuser_id')
                    ->select(
                            'admissions.admissionid', 
                            'admissionhistories.action', 
                            'admissionhistories.created_at', 
                            'admissionhistories.updated_at',
                            'users.name'
                          )
                    ->where([
                        'admissions.client_id' => $id,
                        'admissions.active' => '1',
                        'admissions.deleted' => '0',
                        'admissionhistories.active' => '1',
                        'admissionhistories.deleted' => '0'
                      ])
                    ->paginate(10);

      $clienttransferhistories = Clienttransferhistory::where(['active' => '1', 'deleted' => '0', 'client_id' => $id])->paginate(10);

      $preconditionchanges = Preconditionchange::where(['deleted' => '0', 'active' => '1', 'client_id' => $id])->paginate(10);

      $data = view('pages.histories.client.index', compact('admissionhistories','clienttransferhistories', 'preconditionchanges'));

      return $data;
    }



    public function getClientAdmHistory($id) {

      $admissionhistories = Admissionhistory::join('admissions', 'admissionhistories.admission_id', '=', 'admissions.id')
                  ->where([
                        'admissions.client_id' => $id,
                        'admissions.active' => '1',
                        'admissions.deleted' => '0',
                        'admissionhistories.active' => '1',
                        'admissionhistories.deleted' => '0'
                      ])
                    ->paginate(10);

      $data = view('pages.histories.client.admission', compact('admissionhistories'));

      return $data;
    }



    public function getClientXferHistory($id){

      $clienttransferhistories = Clienttransferhistory::where(['active' => '1', 'deleted' => '0', 'client_id' => $id])->paginate(10);

      $data = view('pages.histories.client.transfer', compact('clienttransferhistories'));

      return $data;

    }


    public function getClientPrecHistory($id) {

      $preconditionchanges = Preconditionchange::where(['deleted' => '0', 'active' => '1', 'client_id' => $id])->paginate(10);

      $data = view('pages.histories.client.precondition', compact('preconditionchanges'));

      return $data;

    }

}
