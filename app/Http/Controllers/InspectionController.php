<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Deficiencyconcern;
use App\Deficiencydetail;
use App\Deficiencycategory;
use App\Inspection;
use App\Inspectiondetail;
use App\Apartment;
use App\Vendor;
use App\Facility;
use Validator;
use Auth;

class InspectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Auth::user()->can('View-Inspections')) {
          return view('pages.550');
        }

        $inspections = Inspection::where(['active' => '1', 'deleted' => '0'])->orderBy('created_at', 'DESC')->get();
        $code_helper = Inspection::orderBy('id', 'DESC')->first();

        $facilities = Facility::where(['active' => '1', 'deleted' => '0'])->orderBy('name', 'ASC')->get();
        return view('pages.inspectionlist', compact('inspections','facilities','code_helper'));
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
        if (!Auth::user()->can('Create-Inspection')) {
          return response()->json(['error' => array('You do not have enough permission(s)')]);
        }

        $validator = Validator::make($request->all(), [

            'date' => 'required',
            'inspection_code' => 'required',
            'facility_id' => 'required',
            'total_inspeted_area' => 'required|numeric',
            'cap_due_date' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $date = (new \App\Custom\Custom)->convertDate($request->date, "Y-m-d");
        $cap_due_date = (new \App\Custom\Custom)->convertDate($request->cap_due_date, "Y-m-d");

        if ($cap_due_date <= $date) {
            return response()->json(['error'=> array(
                'CAP due date should be a date after .'.$date
            )]);
        }

        if (is_numeric($request->followup_code)) {
            $followup_code = $request->followup_code;

            $followupInspection = Inspection::where(['id' => $followup_code])->first();

            if ($followupInspection->facility_id != $request->facility_id) {
                return response()->json(['error'=> array(
                    'Follow up inspection does not belong to the same facility'
                )]);
            }

        } else {
            $followup_code = null;
        }

        /*
            Validation second step
        */

        if (!empty($request->concern_id) && !empty($request->content) && !empty($request->category_id)) {

            if ($request->concern_id[0] == null) {
                return response()->json(['error'=> array(
                    'Please fill all required fields'
                )]);
            }

            foreach ($request->category_id as $ck => $cv) {
                if(empty($cv)){
                    return response()->json(['error'=> array(
                        'No empty category field is allowed!'
                    )]);
                } 
            }

            if (count($request->concern_id,0) != count($request->content,0)) {
                return response()->json(['error'=> array(
                    'Deficiency details field is required for each entry!'
                )]);

            } else {
                foreach ($request->content as $ck => $cv) {
                    if(empty($cv)){
                        return response()->json(['error'=> array(
                            'No empty content field is allowed!'
                        )]);
                    } 
                }
            }
        } else {
            return response()->json(['error'=> array(
                'Please add inspection content'
            )]);
        }


        $status = '';

        if ($request->save == true) {
            $status = 'COMPLETED';
        } else {
            $status = 'INCOMPLETE';
        }

        DB::beginTransaction();

        try {

            $id = DB::table('inspections')->insertGetId(
                [
                    'date' => $date,
                    'code' => $request->inspection_code,
                    'facility_id' => $request->facility_id,
                    'total_inspected_area' => $request->total_inspeted_area,
                    'cap_due_date' => $cap_due_date,
                    'followedins_id' => $followup_code,
                    'status' => $status,
                    'comments' => $request->comments,
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                ]
            );

            if (!empty($request->followup_code)) {
                Inspection::where(['id' => $request->followup_code])->update(
                    [
                        'followedbyins_id' => $id,
                    ]
                );
            }

            $counter = count($request->concern_id,0);

            for ($i = 0; $i < $counter; $i++) {

                $detail = Deficiencydetail::where(['id' => $request->weightage[$i]])->first();

                DB::table('inspectiondetails')->insert([
                    'inspection_id' => $id,
                    'apartment_id' => $request->apartment_id[$i],
                    'content' => $request->content[$i],
                    'concern_id' => $detail->Concern->id,
                    'details_id' => $request->weightage[$i],
                    'comments' => $request->comment[$i],
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s')
                ]);
            
            }

            DB::table('inspectionactions')->insert(
                [
                    'inspection_id' => $id,
                    'action' => 'created',
                    'deleted' => '0',
                    'createdbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
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
        if (!Auth::user()->can('Update-Inspection')) {
          return "<p style='color:red; text-align:center'>Permission Denied</p>";
        }

        $inspection = Inspection::where(['id' => $id])->first();
        $inspection_details = Inspectiondetail::where(['inspection_id' => $id, 'active' => '1', 'deleted' => '0'])->get();
        $facilities = Facility::where(['active' => '1', 'deleted' => '0'])->orderBy('name', 'ASC')->get();
        $apartments = DB::select(
            "
                SELECT
                    apartments.*
                FROM
                    apartments
                INNER JOIN floors ON floors.id = apartments.floor_id
                WHERE
                    floors.facility_id = '$inspection->facility_id'
            "
        );

        $categories = Deficiencycategory::where(['active' => '1', 'deleted' => '0'])->orderBy('name', 'ASC')->get();

        $view = view('pages.savedinspection', compact('inspection','facilities','inspection_details','apartments','categories'));

        return $view;
    }


    public function newForm() {
        
        if (!Auth::user()->can('Create-Inspection')) {
          return "<p style='color:red; text-align:center'>Permission Denied</p>";
        }

        $code_helper = Inspection::orderBy('id', 'DESC')->first();
        $facilities = Facility::where(['active' => '1', 'deleted' => '0'])->orderBy('name', 'ASC')->get();

        if ((new FacilityAccessController)->isSuper() == true) {

          $facilities = Facility::where(['active' => '1', 'deleted' => '0'])->orderBy('name', 'ASC')->get();

        } else {

          $facilities = Facility::where(['active' => '1', 'deleted' => '0'])->whereIn('id', (new FacilityAccessController)->userFacilities())->orderBy('name', 'ASC')->get();

        }

        $view = view('pages.inspectionform', compact('facilities','code_helper'));

        return $view;

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
        if (!Auth::user()->can('Update-Inspection')) {
          return response()->json(['error' => array('You do not have enough permission(s)')]);
        }

        $validator = Validator::make($request->all(), [

            'date' => 'required',
            'inspection_code' => 'required',
            'facility_id' => 'required',
            'total_inspeted_area' => 'required|numeric',
            'cap_due_date' => 'required',
        ]);

        $date = (new \App\Custom\Custom)->convertDate($request->date, "Y-m-d");
        $cap_due_date = (new \App\Custom\Custom)->convertDate($request->cap_due_date, "Y-m-d");

        if ($cap_due_date <= $date) {
            return response()->json(['error'=> array(
                'CAP due date should be a date after .'.$date
            )]);
        }

        
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        if (is_numeric($request->followup_code)) {
            $followup_code = $request->followup_code;
        } else {
            $followup_code = null;
        }

        
           /* Validation second step*/

        $date = (new \App\Custom\Custom)->convertDate($request->date, "Y-m-d");
        $cap_due_date = (new \App\Custom\Custom)->convertDate($request->cap_due_date, "Y-m-d");
        

        if (!empty($request->concern_id) && !empty($request->content) && !empty($request->category_id)) {

            if ($request->concern_id[0] == null) {
                return response()->json(['error'=> array(
                    'Please fill all required fields'
                )]);
            }

            foreach ($request->category_id as $ck => $cv) {
                if(empty($cv)){
                    return response()->json(['error'=> array(
                        'No empty category field is allowed!'
                    )]);
                } 
            }

            if (count($request->concern_id,0) != count($request->content,0)) {
                return response()->json(['error'=> array(
                    'Deficiency details field is required for each entry!'
                )]);

            } else {
                foreach ($request->content as $ck => $cv) {
                    if(empty($cv)){
                        return response()->json(['error'=> array(
                            'No empty content field is allowed!'
                        )]);
                    } 
                }
            }
        } else {
            return response()->json(['error'=> array(
                'Please add inspection content'
            )]);
        }

        /*
            Check followup code is not newer than the current inspection code
        */

        $inspection = Inspection::where(['id' => $id])->first();

        
        $followNewCheck = Inspection::where(['id' => $request->followup_code])->first();

        if (!empty($followNewCheck)) {
            if ($inspection->created_at < $followNewCheck->created_at) {
                return response()->json(['error'=> array(
                    'The inspection to be followed is not valid. It has to be an older inspection than: '. $inspection->code
                )]);
            }
        }

        $status = '';

        if ($request->save == true) {
            $status = 'COMPLETED';
        } else {
            $status = 'INCOMPLETE';
        }

        DB::beginTransaction();

        try {

            if (!empty($inspection->followedins_id)) {
                Inspection::where(['id' => $inspection->followedins_id])->update(
                    [
                        'followedbyins_id' => $inspection->id,
                    ]
                );

                Inspection::where(['id' => $id])->update(
                    [
                        'date' => $date,
                        'code' => $request->inspection_code,
                        'facility_id' => $request->facility_id,
                        'total_inspected_area' => $request->total_inspeted_area,
                        'cap_due_date' => $cap_due_date,
                        'status' => $status,
                        'comments' => $request->comments,
                        'updatedbyuser_id' => Auth::user()->id,
                        'updated_at' => date('Y-m-d h:i:s'),
                    ]
                );
            } else {
                Inspection::where(['id' => $id])->update(
                    [
                        'date' => $date,
                        'code' => $request->inspection_code,
                        'facility_id' => $request->facility_id,
                        'total_inspected_area' => $request->total_inspeted_area,
                        'cap_due_date' => $cap_due_date,
                        'followedins_id' => $followup_code,
                        'status' => $status,
                        'comments' => $request->comments,
                        'updatedbyuser_id' => Auth::user()->id,
                        'updated_at' => date('Y-m-d h:i:s'),
                    ]
                );
            }
            

            if ($followup_code != null) {
                Inspection::where(['id' => $followup_code])->update(
                    [
                        'followedbyins_id' => $id,
                    ]
                );
            }

            Inspectiondetail::where(['inspection_id' => $id])->delete();

            $counter = count($request->concern_id,0);

            for ($i = 0; $i < $counter; $i++) {

                $detail = Deficiencydetail::where(['id' => $request->weightage[$i]])->first();

                DB::table('inspectiondetails')->insert([
                    'inspection_id' => $id,
                    'apartment_id' => $request->apartment_id[$i],
                    'content' => $request->content[$i],
                    'concern_id' => $detail->Concern->id,
                    'details_id' => $request->weightage[$i],
                    'comments' => $request->comment[$i],
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s')
                ]);
            }

            DB::commit();

            return response()->json(['success'=>'Records added.']);


        } catch (\Exception $e) {
            DB::rollback();
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


    public function formfield($id,$arg) {
        
        $categories = Deficiencycategory::where(['active' => '1', 'deleted' => '0'])->orderBy('name', 'ASC')->get();

        $catOps = "";

        foreach ($categories as $key => $value) {
            $catOps .= "<option value='".$value->id."'>".$value->name."</option>";
        }

        if ($arg == 'false'){
            $html = "
                <tr id='tr$id'><td><input type='text' class='form-control' name='content[]'><input type='hidden' name='apartment_id[]' value=''></td><td><select onchange='getDef(this.value,$id)' name='category_id[]' class='form-control'><option value=''>SELECT</option>$catOps</select></td><td><select id='details$id' name='concern_id[]' class='form-control' onchange='getCon(this.value,$id)'>   </select></td><td><h4 class='text-center' id='concern$id'></h4></td><td><input class='form-control' type='hidden' id='weightage$id' name='weightage[]' readonly><input type='text'  class='form-control' name='comment[]' id='comment$id'></td><td><button class='btn btn-sm btn-danger delete'>DELETE</button></td> </tr>
            ";
        } else{

            $apartment = Apartment::where(['id' => $arg])->first();

            $html = "
                <tr id='tr$id'><td><input type='text' class='form-control' name='content[]' value='".$apartment->name."' readonly><input type='hidden' name='apartment_id[]' value='".$apartment->id."'></td><td><select onchange='getDef(this.value,$id)' name='category_id[]' class='form-control'><option value=''>SELECT</option>$catOps</select></td><td><select id='details$id' name='concern_id[]' class='form-control' onchange='getCon(this.value,$id)'>   </select></td><td><h4 class='text-center' id='concern$id'></h4></td><td><input class='form-control' type='hidden' id='weightage$id' name='weightage[]' readonly><input type='text'  class='form-control' name='comment[]' id='comment$id'></td><td><button class='btn btn-sm btn-danger delete'>DELETE</button></td> </tr>
            ";
        }

        
        
        return json_encode($html);

    }


    public function defdetails($id) {    
        $defdetails = Deficiencydetail::where(['category_id' => $id])->orderBy('description', 'ASC')->get();
        return $defdetails;
    }


    public function getconcern($id) {
        $defdetails = Deficiencydetail::where(['id' => $id])->first();

        $data = [];

        $data['id'] = $defdetails->id;
        $data['concern'] = $defdetails->Concern->name;
        $data['concern_id'] = $defdetails->Concern->id;
        $data['weightage'] = $defdetails->weightage;

        return json_encode($data);
    }


    public function getDetails($id) {

        if (!Auth::user()->can('View-Inspection-Details')) {
          return "<p style='color:red; text-align:center'>Permission Denied</p>";
        }
        
        $inspection = Inspection::where(['id' => $id])->first();

        $groupByConcern = DB::select(
            "
                SELECT
                    deficiencyconcerns.`name` AS concern,
                    COUNT(deficiencyconcerns.`name`) AS concern_count,
                    SUM(
                        deficiencydetails.weightage
                    ) AS total_weightage
                FROM
                    inspectiondetails
                INNER JOIN deficiencydetails ON inspectiondetails.details_id = deficiencydetails.id
                INNER JOIN deficiencyconcerns ON deficiencyconcerns.id = inspectiondetails.concern_id
                WHERE
                    inspectiondetails.inspection_id = '$id'
                GROUP BY
                    deficiencyconcerns.`name`
            "
        );
        return view('pages.inspectiondetails', compact('inspection', 'groupByConcern'));
    }

    public function printLayout($id) {
        $inspection = Inspection::where(['id' => $id])->first();
        return view('pages.inspectionprint', compact('inspection'));
    }




    public function addFollowUpIns(Request $request) {
        
        $inspection = Inspection::where([
                        'code' => $request->code, 
                        'status' => 'COMPLETED',
                        'createdbyuser_id' => Auth::user()->id
                    ])->first();
        
        if (!empty($inspection)) {

            $checkDupes = Inspection::where(['followedins_id' => $inspection->id, 'deleted' => '0', 'active' => '1'])->first();

            if (!empty($checkDupes)) {
                return response()->json
                        (
                            [
                                'error'=> array(
                                    'This inspection has already been fllowed by: '.$checkDupes->code,
                                )
                            ]
                        );
            } else {
                return response()->json(['success'=> array(
                    'code' => $inspection->code,
                    'id' => $inspection->id,
                )]);
            }

        } else {
            return response()->json(['error'=> array(
                            'Invalid Code Yea',
                        )]);
        }



    }


    public function getDeletion($id) {

     if (!Auth::user()->can('Delete-Inspection')) {
       return "<p style='color:red; text-align:center'>Permission Denied</p>";
     }

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='inspections' action='".route('postInspectionDeletion',$id)."' method='POST'>

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

        if (!Auth::user()->can('Delete-Inspection')) {
          return response()->json(['error' => array('You do not have enough permission(s)')]);
        }

        $inspection = Inspection::where(['id' => $id])->first();

        if (empty($inspection->followedbyins_id)) {
            
          try {

              DB::beginTransaction();

              Inspection::where(['id' => $id])->update(
                  [
                      'deleted' => '1',
                      'updatedbyuser_id' => Auth::user()->id,
                      'updated_at' => date('Y-m-d h:i:s'),
                  ]
              );

              DB::table('inspectionactions')->insert(
                  [
                      'inspection_id' => $id,
                      'action' => 'deleted',
                      'deleted' => '0',
                      'createdbyuser_id' => Auth::user()->id,
                      'created_at' => date('Y-m-d h:i:s'),
                  ]
              );

              Inspectiondetail::where(['inspection_id' => $id])->update(
                [
                    'deleted' => '1',
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d h:i:s'),
                ]
              );

              Inspection::where(['followedbyins_id' => $id])->update(
                [
                    'followedbyins_id' => null,
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d h:i:s'),
                ]
              );

              DB::commit();

              return response()->json(['success'=>'Deleted Successfully!.']);

              
          } catch (\Exception $e) {
              DB::rollback();
              return response()->json(['error'=>array('Deletion not applied!')]);
          }

        } else {
          return response()->json(['error'=>array("This inspection cannot be deleted")]);
        }     

    }


}
