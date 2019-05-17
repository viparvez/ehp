<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Admission;
use App\Facility;
use App\Client;
use App\Apartment;
use App\Precondition;
use App\Preconditionchange;
use App\Admissionhistory;
use App\Apartmentallotment;
use Validator;
use Auth;

class AdmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!(Auth::user()->can('View-Admissions'))) {
          return view('pages.550');
        }

        if ((new FacilityAccessController)->isSuper() == true) {

          $admissions = Admission::where(['deleted'=>'0', 'active'=>'1'])->orderBy('created_at', 'DESC')->get();
          $buildings = Facility::where(['deleted'=>'0', 'active'=>'1'])->orderBy('code', 'ASC')->get();


        } else {

          $admissions = Admission::select('admissions.*')
                        ->join('apartments', 'apartments.id', '=', 'admissions.apartment_id')
                        ->join('floors', 'floors.id', '=', 'apartments.floor_id')
                        ->join('facilities', 'facilities.id', '=', 'floors.facility_id')
                        ->where(['admissions.active' => '1', 'admissions.deleted' => '0'])
                        ->whereIn('facilities.id', (new FacilityAccessController)->userFacilities())
                        ->get();

          $buildings = Facility::where(['deleted'=>'0', 'active'=>'1'])->orderBy('name', 'ASC')->whereIn('facilities.id',(new FacilityAccessController)->userFacilities())->get();

        }

        return view('pages.admission', compact('admissions','buildings'));
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

        if(!(Auth::user()->can('Create-Admission'))) {
          return response()->json(['error'=>array('You do not have necessary permission(s)')]);
        }

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|numeric',
            'apartment_id' => 'required|numeric',
            'moveindate' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $moveindate = (new \App\Custom\Custom)->convertDate($request->moveindate, "Y-m-d");

        if($moveindate > date('Y-m-d')) {
          return response()->json(['error'=>array('Move in date should not be equal to or older than '.date('Y-m-d'))]);
        }

        $apartment = Apartment::where(['id' => $request->apartment_id])->first();

        if ($moveindate < $apartment->Floor->Facility->start_date_ehp )  {
          return response()->json(['error'=>array('Move in date should be a date after '.date('Y-m-d',strtotime($apartment->Floor->Facility->start_date_ehp)))]);
        }

        $month = date("F", strtotime((new \App\Custom\Custom)->convertDate($request->moveindate, "Y-m-d")));
        $year = date("Y", strtotime((new \App\Custom\Custom)->convertDate($request->moveindate, "Y-m-d")));

        if (!empty($this->billGenerated($month, $year, $request->apartment_id))) {
          return response()->json(['error'=>array('Cannot Discharge. Reason: Bill for the selected date has alreeady been generated.')]);
        }

        $currentAdmission = Admission::where(['client_id' => $request->client_id, 'active' => '1', 'deleted' => '0'])->whereNull('moveoutdate')->first();

        $checkAptAv = DB::select(
        "
          SELECT
            count(apartment_id) AS result
          FROM
            apartmentallotments
          WHERE
            apartment_id = '$request->apartment_id'
          AND active = '1'
          AND deleted = '0'
          AND (
            occupiedon BETWEEN '$moveindate' AND CURDATE() + INTERVAL 1 DAY
            OR vacatedon BETWEEN '$moveindate' AND CURDATE() + INTERVAL 1 DAY
            OR vacatedon IS NULL
          ) 
        "
        );

        if ($checkAptAv[0]->result > 0) {
          return response()->json(['error'=>array('Apartment was not vacant in any point of time between the date you provided and today.')]);
        }

        $clientAvailable = DB::SELECT(
                            "
                              SELECT * FROM admissions
                              WHERE client_id = '$request->client_id'
                              AND active = '1'
                              AND deleted = '0'
                              AND 
                              (moveindate >= '$moveindate'
                              OR moveoutdate > '$moveindate')
                            "
                          );

        if (count($clientAvailable) > 0) {
          return response()->json(['error'=>array('Client was not available in any point of time between the date you provided and today.')]);
        }


        if (empty($currentAdmission)) {

          try{

              $precondiotionId = Precondition::where(['name'=> 'Admitted'])->first();

              DB::beginTransaction();

              $id = DB::table('admissions')->insertGetId(
                  [
                      'admissionid' => time(),
                      'client_id' => $request->client_id,
                      'apartment_id' => $request->apartment_id,
                      'moveindate' => (new \App\Custom\Custom)->convertDate($request->moveindate, "Y-m-d"),
                      'created_at' => date('Y-m-d h:i:s'),
                      'updated_at' => date('Y-m-d h:i:s'),
                      'createdbyuser_id' => Auth::user()->id,
                      'updatedbyuser_id' => Auth::user()->id,
                  ]
              );

              DB::table('admissionhistories')->insert(
                  [
                      'admission_id' => $id,
                      'action' => 'New Admission',
                      'created_at' => date('Y-m-d h:i:s'),
                      'updated_at' => date('Y-m-d h:i:s'),
                      'createdbyuser_id' => Auth::user()->id,
                      'updatedbyuser_id' => Auth::user()->id,
                  ]
              );


              DB::table('apartmentallotments')->insert(
                  [
                      'admission_id' => $id,
                      'apartment_id' => $request->apartment_id,
                      'occupiedon' => (new \App\Custom\Custom)->convertDate($request->moveindate, "Y-m-d"),
                      'created_at' => date('Y-m-d h:i:s'),
                      'updated_at' => date('Y-m-d h:i:s'),
                      'createdbyuser_id' => Auth::user()->id,
                      'updatedbyuser_id' => Auth::user()->id,
                  ]
              );

              $code = 'ADM'.sprintf('%06d', $id);

              Admission::where(['id' => $id])->update(
                [
                  'admissionid' => $code,
                ]
              );
              
              DB::table('clients')->where(['id' => $request->client_id])
                  ->update(
                  [
                      'precondition_id' => $precondiotionId->id,
                      'updated_at' => date("Y-m-d h:i:s"),
                      'updatedbyuser_id' => Auth::user()->id,
                  ]
              );

              DB::table('apartments')->where(['id' => $request->apartment_id])
                  ->update(
                  [
                      'free' => '0',
                      'updated_at' => date("Y-m-d h:i:s"),
                      'updatedbyuser_id' => Auth::user()->id,
                  ]
              );

              DB::table('preconditionchanges')->insert(
                  [
                      'client_id' => $request->client_id,
                      'precondition_id' => $precondiotionId->id,
                      'comment' => "New admission: $code",
                      'created_at' => date('Y-m-d h:i:s'),
                      'updated_at' => date('Y-m-d h:i:s'),
                      'createdbyuser_id' => Auth::user()->id,
                      'updatedbyuser_id' => Auth::user()->id,
                  ]
              );
              
              DB::commit();

              return response()->json(['success'=>'Admission done.']);

          } catch (\Exception $e) {
              DB::rollback();
              return response()->json(['error'=>array('We have encountered and internal error. Please contact the system administrator')]);
              //return response()->json(['error' => $e->getMessage()]);
          }
        
        } else {
           return response()->json(['error'=>array('This client alreeady has an open admission.')]);
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
        if(!(Auth::user()->can('Create-Admission'))) {
          return "<p style='color:red; text-align:center'>Permission Denied</p>";
        }

        $admission = Admission::where(['id' => $id, 'deleted' => '0', 'active' => '1'])->first();

        if (!empty($admission->discharge_doc)) {
          $discharge_doc = "
            <tr>
              <td>Discharge Document</td><td><a href='$admission->discharge_doc' download>Download</a></td>
            </tr>
          ";
        } else {
          $discharge_doc = "";
        }

        if (!empty($admission->moveindate)) {
          $moveindate = date('m-d-Y', strtotime($admission->moveindate));
        } else {
          $moveindate = $admission->moveindate;
        }

        if (!empty($admission->moveoutdate)) {
          $moveoutdate = date('m-d-Y', strtotime($admission->moveoutdate));
        } else {
          $moveoutdate = $admission->moveoutdate;
        }

        $data = "
          <div class='box box-primary' style='font-weight:bold'>
            <div class='box-body box-profile'>
            <h3 class='profile-username text-center'>Admission ID: ".$admission->admissionid."</h3>
              <table class='table table-striped details-view'>
                  <tr>
                    <td>Client Name</td><td>".$admission->Client->fname." ".$admission->Client->lname."</td>
                  </tr>
                  <tr>
                    <td>Client ID</td><td>".$admission->Client->code."</td>
                  </tr>
                  <tr>
                    <td>Client Phone</td><td>".$admission->Client->phone."</td>
                  </tr>
                  <tr>
                    <td>Building</td><td>".$admission->Apartment->Floor->Facility->name." (Code: ".$admission->Apartment->Floor->Facility->code.")</td>
                  </tr>
                  <tr>
                    <td>Floor</td><td>".$admission->Apartment->Floor->name." (Code: ".$admission->Apartment->Floor->code.")</td>
                  </tr>
                  <tr>
                    <td>Apartment</td><td>".$admission->Apartment->name."(Code: ".$admission->Apartment->code.")</td>
                  </tr>
                  <tr>
                    <td>Movein Date</td><td>$moveindate</td>
                  </tr>
                  <tr>
                    <td>Moveout Date</td><td>".($admission->moveoutdate == null ? 'Currently Admitted' : $moveoutdate)."</td>
                  </tr>
                  <tr>
                    <td>Comments</td><td>".$admission->comments."</td>
                  </tr>
                  $discharge_doc
                </table>
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
        //
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
        //
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



    public function dischargeForm($id)
    {

        if(!(Auth::user()->can('Discharge-Admission'))) {
          return "<p style='color:red; text-align:center'>Permission Denied</p>";
        }

        $admission = Admission::where(['id' => $id])->first();

        if (empty($admission->moveoutdate)) {
            $data  = "

                <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
                    <ul></ul>
                </div>

                <h4><p>Facility: <b>".$admission->Apartment->Floor->Facility->name."</b> | Unit: <b>".$admission->Apartment->name."</b> | Admission date: <b>".date('m-d-Y',strtotime($admission->moveindate))."</b></p></h4>
                
                <form method='POST' action='".route('admissions.putdischarge')."' id='disForm'>
                    ".csrf_field()."
                    <input type='hidden' name='admission_id' value='$id'>
                    
                    <div class='form-group'>
                        <label>Admission ID</label>
                        <input class='form-control' type='text' value='".$admission->admissionid."' readonly> 
                    </div>

                    <div class='form-group'>
                        <label>Client</label>
                        <input class='form-control' type='text' value='".$admission->Client->fname." ".$admission->Client->lname."' readonly> 
                    </div>

                    <div class='form-group'>
                      <label>Move out date:</label>
                      <div class='input-group date moveindate'>
                        <div class='input-group-addon'>
                          <i class='fa fa-calendar'></i>
                        </div>
                        <input type='text' class='form-control pull-right' id='datepicker1' name='moveoutdate' >
                      </div>
                    </div>


                    <div class='form-group'>
                        <label>Discharge Documents</label>
                        <input type='file' name='discharge_doc' > 
                    </div>

                    <div class='form-group'>
                        <label>Comments</label>
                        <textarea name='comments' class='form-control'></textarea>
                    </div>

                    <button class='btn btn-block btn-primary btn-sm' id='submit_discharge' type='submit'>SUBMIT</button>
                    <button class='btn btn-block btn-primary btn-sm' id='loading_discharge' style='display: none' disabled=''>Working...</button>
                </form>
            ";
        } else{
            $data = "<h3 class='text-center'>DISCHARGE/ADMISSION NOT POSSIBLE</h3>";
        }

        return $data;
    }



    public function discharge(Request $request) {

      if(!(Auth::user()->can('Discharge-Admission'))) {
        return response()->json(['error'=>array('You do not have necessary permission(s)')]);
      }

        $validator = Validator::make($request->all(), [
            'admission_id' => 'required',
            'moveoutdate' => 'required',
            #'discharge_doc' => 'mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $admission = Admission::where(['id'=> $request->admission_id, 'active' => '1', 'deleted' => '0'])->first();

        if ($validator->fails()) {
          return response()->json(['error'=>$validator->errors()->all()]);
        }

        $month = date("F", strtotime((new \App\Custom\Custom)->convertDate($request->moveoutdate, "Y-m-d")));
        $year = date("Y", strtotime((new \App\Custom\Custom)->convertDate($request->moveoutdate, "Y-m-d")));

        $moveoutdateFormatted = (new \App\Custom\Custom)->convertDate($request->moveoutdate, "Y-m-d");

        if ($moveoutdateFormatted < $admission->moveindate || $moveoutdateFormatted > date('Y-m-d')) {
          return response()->json(['error'=>array('Move out date should be a date greater than move in date and equal to or smaller than '.date('m-d-Y'))]);
        }

        if (!empty($this->billGenerated($month, $year, $admission->Apartment->Floor->Facility->id))) {
          return response()->json(['error'=>array('Cannot Discharge. Reason: Bill for the selected date has alreeady been generated.')]);
        }

        //$vacantfrom = date('Y-m-d',strtotime($moveoutdateFormatted . "+1 days"));

        $vacantfrom = date('Y-m-d',strtotime($moveoutdateFormatted . "+1 days"));

        $advance_attendance = DB::select(
          "
            SELECT count(*) AS result
            FROM attendances
            WHERE apartment_id = '$admission->apartment_id'
            AND active = '1'
            AND deleted = '0'
            AND date >= '$moveoutdateFormatted'
          "
        );

        if ($advance_attendance[0]->result > 0) {
          return response()->json(['error'=>array('Attendance for this apartment for a date equal to or grater than the moveout date is present. Please select a correct date for discharge.')]);
        }

        $precondiotionId = Precondition::where(['name'=> 'Discharged'])->first();

        if(!empty($admission)){

            try{

                if($request->hasFile('discharge_doc')) {
                  
                   $file = $request->file('discharge_doc');
                   $name = $request->admission_id.'.'.$file->getClientOriginalExtension();
                   $file->move(public_path().'/file/discharge_doc/', $name);
                   $file_url = url('/')."/public/file/discharge_doc/$name";

                } else {
                    $name = null;
                    $file_url = null;
                }

                DB::beginTransaction();

                DB::table('admissions')->where(['id' => $request->admission_id])
                    ->update(
                    [
                        'moveoutdate' => (new \App\Custom\Custom)->convertDate($request->moveoutdate, "Y-m-d"),
                        'updated_at' => date("Y-m-d h:i:s"),
                        'comments' => $request->comments,
                        'discharge_doc' => $file_url,
                        'updatedbyuser_id' => Auth::user()->id,
                    ]
                );


                DB::table('admissionhistories')->insert(
                    [
                        'admission_id' => $request->admission_id,
                        'action' => 'Discharge',
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s'),
                        'updatedbyuser_id' => Auth::user()->id,
                    ]
                );


                DB::table('apartmentallotments')->where(['admission_id' => $request->admission_id, 'apartment_id' => $admission->Apartment->id])
                    ->update(
                    [
                        'vacatedon' => (new \App\Custom\Custom)->convertDate($request->moveoutdate, "Y-m-d"),
                        'updated_at' => date("Y-m-d h:i:s"),
                        'updatedbyuser_id' => Auth::user()->id,
                    ]
                );

                if($admission->Client->Precondition->name == 'Admitted' || $admission->Client->Precondition->name == 'Transferred') {
                  
                  DB::table('clients')->where(['id' => $admission->Client->id])
                      ->update(
                      [
                          'precondition_id' => $precondiotionId->id,
                          'updated_at' => date("Y-m-d h:i:s"),
                          'updatedbyuser_id' => Auth::user()->id,
                      ]
                  );

                  DB::table('preconditionchanges')->insert(
                      [
                          'client_id' => $admission->Client->id,
                          'precondition_id' => $precondiotionId->id,
                          'comment' => 'Discharge',
                          'created_at' => date('Y-m-d h:i:s'),
                          'updated_at' => date('Y-m-d h:i:s'),
                          'createdbyuser_id' => Auth::user()->id,
                          'updatedbyuser_id' => Auth::user()->id,
                      ]
                  );

                } 

                DB::table('apartments')->where(['id' => $admission->apartment_id])
                    ->update(
                    [
                        'free' => '1',
                        'vacantfrom' => $vacantfrom,
                        'updated_at' => date("Y-m-d h:i:s"),
                        'updatedbyuser_id' => Auth::user()->id,
                    ]
                );


                DB::commit();

                $level = DB::transactionLevel();

                switch ($level) {
                    case '0':
                        return response()->json(['success'=>'Discharged']);
                        break;
                    
                    case '1':
                        return response()->json(['success'=>'Client Discharged']);
                        break;

                    case '2':
                        return response()->json(['error'=>'Operation Failed']);
                        break;

                    default:
                        return response()->json(['error'=>'Undefined Error']);
                        break;
                }
 
            } catch (\Exception $e) {
                DB::rollback();
                return $e->getMessage();
            }

        }

    }



    public function transfer(Request $request) {

      if(!(Auth::user()->can('Admission-Transfer'))) {
        return response()->json(['error'=>array('You do not have necessary permission(s)')]);
      }

      $validator = Validator::make($request->all(), [
            'admission_id' => 'required',
            'moveindate' => 'required',
            'apartment_id' => 'required',
        ]);

      $admission = Admission::where(['id'=> $request->admission_id, 'active' => '1', 'deleted' => '0'])->first();

      $precondiotionId = Precondition::where(['name'=> 'Transferred'])->first();

      $month = date("F", strtotime((new \App\Custom\Custom)->convertDate($request->moveindate, "Y-m-d")));
      $year = date("Y", strtotime((new \App\Custom\Custom)->convertDate($request->moveindate, "Y-m-d")));

      if (!empty($this->billGenerated($month, $year, $admission->Apartment->Floor->Facility->id))) {
        return response()->json(['error'=>array('Cannot Transfer. Reason: Bill for the selected date has alreeady been generated for '. $admission->Apartment->Floor->Facility->name)]);
      }

      $apartment = Apartment::where(['id' => $request->apartment_id])->first();

      if (!empty($this->billGenerated($month, $year, $apartment->Floor->Facility->id))) {
        return response()->json(['error'=>array('Cannot Transfer. Reason: Bill for the selected date has alreeady been generated for '. $apartment->Floor->Facility->name)]);
      }

      $transferdate = (new \App\Custom\Custom)->convertDate($request->moveindate, "Y-m-d");

      if ($transferdate < $admission->moveindate || $transferdate > date('Y-m-d')) {
        return response()->json(['error'=>array('Invalid transfer date')]);
      }

      $checkAptAv = DB::select(
        "
          SELECT
            count(apartment_id) AS result
          FROM
            apartmentallotments
          WHERE
            apartment_id = '$request->apartment_id'
          AND active = '1'
          AND deleted = '0'
          AND (
            occupiedon BETWEEN '$transferdate' AND CURDATE() + INTERVAL 1 DAY
            OR vacatedon BETWEEN '$transferdate' AND CURDATE() + INTERVAL 1 DAY
            OR vacatedon IS NULL
          ) 
        "
      );

      if ($checkAptAv[0]->result > 0) {
        return response()->json(['error'=>array('Apartment was not vacant in any point of time between the date you provided and today.')]);
      }

      $currApt = $admission->currentAptDetails($admission->id, $admission->apartment_id);

      if ($transferdate < $currApt[0]->occupiedon) {
        return response()->json(['error'=>array('The requested date is a date before current apartments moveindate.')]);
      }

      DB::beginTransaction();

      try {

        DB::table('admissions')->where(['id' => $request->admission_id])->update(
          [
            'apartment_id' => $request->apartment_id,
            'updated_at' => date("Y-m-d h:i:s"),
            'updatedbyuser_id' => Auth::user()->id
          ] 
        );

        DB::table('clienttransferhistories')->insert(
            [
                'client_id' => $request->client_id,
                'admission_id' => $admission->id,
                'previous_apt_id' => $admission->apartment_id,
                'new_apt_id' => $request->apartment_id,
                'comment' => $request->comment,
                'active' => '1',
                'deleted' => '0',
                'createdbyuser_id' => Auth::user()->id,
                'updatedbyuser_id' => Auth::user()->id,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s')
            ]
        );

        DB::table('admissionhistories')->insert(
            [
                'admission_id' => $admission->id,
                'action' => 'Client Transfer',
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
                'updatedbyuser_id' => Auth::user()->id,
            ]
        );

        DB::table('apartments')->where(['id' => $admission->apartment_id])
            ->update(
            [
                'free' => '1',
                'vacantfrom' => (new \App\Custom\Custom)->convertDate($request->moveindate, "Y-m-d"),
                'updated_at' => date("Y-m-d h:i:s"),
                'updatedbyuser_id' => Auth::user()->id,
            ]
        );

        DB::table('apartments')->where(['id' => $request->apartment_id])
            ->update(
            [
                'free' => '0',
                'updated_at' => date("Y-m-d h:i:s"),
                'updatedbyuser_id' => Auth::user()->id,
            ]
        );


        DB::table('apartmentallotments')->where(['admission_id' => $request->admission_id, 'apartment_id' => $admission->apartment_id])
            ->update(
            [
                'vacatedon' => (new \App\Custom\Custom)->convertDate($request->moveindate, "Y-m-d"),
                'updated_at' => date("Y-m-d h:i:s"),
                'updatedbyuser_id' => Auth::user()->id,
            ]
        );

        DB::table('apartmentallotments')->insert(
            [
                'admission_id' => $request->admission_id,
                'apartment_id' => $request->apartment_id,
                'occupiedon' => (new \App\Custom\Custom)->convertDate($request->moveindate, "Y-m-d"),
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
                'createdbyuser_id' => Auth::user()->id,
                'updatedbyuser_id' => Auth::user()->id,
            ]
        );

        DB::table('clients')->where(['id' => $request->client_id])
            ->update(
            [
                'precondition_id' => $precondiotionId->id,
                'updated_at' => date("Y-m-d h:i:s"),
                'updatedbyuser_id' => Auth::user()->id,
            ]
        );

        DB::table('preconditionchanges')->insert(
            [
                'client_id' => $request->client_id,
                'precondition_id' => $precondiotionId->id,
                'comment' => 'Transfer',
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
                'createdbyuser_id' => Auth::user()->id,
                'updatedbyuser_id' => Auth::user()->id,
            ]
        );

        DB::commit();

        return response()->json(['success'=>'Record updated.']);

      } catch (Exception $e) {
        DB::rollback();
        return response()->json(['error'=>array('Cannot Transfer')]);
      }
    }


    public function billGenerated ($month, $year, $facility_id) {
      return DB::select(
        "
          SELECT
            *
          FROM
            billings
          WHERE
            facility_id = '$facility_id'
          AND `month` = '$month'
          AND `year` = '$year'
          AND deleted = '0'
        "
      );
    }


    public function getDeletion($id) {

      if(!(Auth::user()->can('Delete-Admission'))) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='deleteAdmission' action='".route('postAdmissionDeletion',$id)."' method='POST'>

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

        if(!(Auth::user()->can('Delete-Admission'))) {
          return response()->json(['error'=>array('You do not have necessary permission(s)')]);
        }

        $adm = Admission::where(['id' => $id])->first();

        $isOk = count($adm->History->where('deleted','0'));

        

        $month = date('m', strtotime($adm->moveindate));
        $year = date('Y', strtotime($adm->moveindate));

        if (!empty($this->billGenerated ($month, $year, $adm->Apartment->Floor->Facility->id))) {
          return response()->json(['error'=>array("Cannot DELETE. Bill for this admission has already been generated.")]);
        }

        $precondiotionId = Preconditionchange::where(['client_id'=> $adm->client_id, 'deleted' => '0'])->orderBy('created_at', 'desc')->skip(1)->take(1)->get();

        if (1==1) {
            
          try {

              DB::beginTransaction();

              Admission::where(['id' => $id])->update(
                  [
                      'deleted' => '1',
                      'updatedbyuser_id' => Auth::user()->id,
                      'updated_at' => date('Y-m-d h:i:s'),
                  ]
              );

              Admissionhistory::where(['admission_id' => $adm->id])->update(
                  [
                      'deleted' => '1',
                      'createdbyuser_id' => Auth::user()->id,
                      'created_at' => date('Y-m-d h:i:s'),
                  ]
              );

              Apartmentallotment::where(['admission_id' => $adm->id])->update(
                  [
                      'deleted' => '1',
                      'createdbyuser_id' => Auth::user()->id,
                      'created_at' => date('Y-m-d h:i:s'),
                  ]
              );

              Apartment::where(['id' => $adm->apartment_id])->update(
                  [
                      'free' => '1',
                      'createdbyuser_id' => Auth::user()->id,
                      'created_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::table('clients')->where(['id' => $adm->client_id])
                  ->update(
                  [
                      'precondition_id' => $precondiotionId[0]->precondition_id,
                      'updated_at' => date("Y-m-d h:i:s"),
                      'updatedbyuser_id' => Auth::user()->id,
                  ]
              );
              

              DB::table('preconditionchanges')->insert(
                  [
                      'client_id' => $adm->client_id,
                      'precondition_id' => $precondiotionId[0]->precondition_id,
                      'comment' => "Admission Cancelled",
                      'created_at' => date('Y-m-d h:i:s'),
                      'updated_at' => date('Y-m-d h:i:s'),
                      'createdbyuser_id' => Auth::user()->id,
                      'updatedbyuser_id' => Auth::user()->id,
                  ]
              );

              DB::commit();

              return response()->json(['success'=>'Deleted Successfully!.']);

              
          } catch (\Exception $e) {
              DB::rollback();
              return response()->json(['error'=>array($e->getMessage())]);
          }

        } else {
          return response()->json(['error'=>array("Cannot DELETE")]);
        }     

    }

}
