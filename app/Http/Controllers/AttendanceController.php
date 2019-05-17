<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Attendance;
use App\Facility;
use App\Apartment;
use Validator;
use Auth;
use Redirect;
use App\Attendancesheet;
use App\Vendor;
use App\State;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Auth::user()->can('Show-Attendance')) {
          return view('pages.550');
        }

        $facilities = Facility::where(['deleted' => '0'])->get();
        return view('pages.attendances',compact('facilities'));
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
        if (!Auth::user()->can('Create-Attendance')) {
          return response()->json(['error' => array('You do not have enough permission(s)')]);
        }

        DB::beginTransaction();

        try {

            if($request->hasFile('document')) {
              
               $file = $request->file('document');
               $name = $request->date.'-attendance-data.'.time().'.'.$file->getClientOriginalExtension();
               $file->move(public_path().'/file/attendance', $name);
               $refUrl = "/public/file/attendance/".$name;

               DB::table('attendancesheets')->insert([
                   'date' => $request->date,
                   'file' => $refUrl,
                   'facility_id' => $request->facility_id,
               ]);

            } 

            foreach ($request->attendance as $key => $value) {

                DB::table('attendances')->insert([
                    'facility_id' => $request->facility_id,
                    'apartment_id' => $key,
                    'attend' => $value,
                    'date' => $request->date,
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                ]);

            }

            DB::table('attendancecomments')->insert([
                'date' => $request->date,
                'comment' => $request->comment,  
                'facility_id' => $request->facility_id,                
            ]);


            DB::commit();

            return response()->json(['success'=>'Added new records.']);

        } catch (Exception $e) {
            DB::rollback();
            return $e.getMessage();
        }
        
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($date)
    {
        $date_mod = (new \App\Custom\Custom)->convertDate($date, "Y-m-d");

        $data =  DB::select(
            "
                SELECT
                    facility_name,
                    occ_fac_code,
                    CONCAT(
                        city,
                        ', ',
                        state,
                        ', ',
                        zip
                    ) AS facility_address,
                    total_occupied,
                    total_signs,
                    CASE
                WHEN total_signs = '0' THEN
                    0
                ELSE
                    ROUND(
                        (total_signs / total_occupied) * 100,
                        2
                    )
                END AS sign_percentage
                FROM
                    (
                        SELECT
                            FOO2.occ_fac_code,
                            FOO2.facility_name,
                            FOO2.zip,
                            FOO2.city,
                            FOO2.state,
                            (
                                SELECT
                                    COUNT(apartment_id)
                                FROM
                                    apartmentallotments
                                INNER JOIN apartments ON apartments.id = apartmentallotments.apartment_id
                                INNER JOIN floors ON floors.id = apartments.floor_id
                                INNER JOIN facilities ON facilities.id = floors.facility_id
                                WHERE
                                    facilities.id = FOO2.occ_fac_id
                                AND apartmentallotments.active = '1'
                                AND apartmentallotments.deleted = '0'
                                AND (
                                    (
                                        apartmentallotments.occupiedon <= '$date_mod'
                                        AND apartmentallotments.vacatedon >= '$date_mod'
                                    )
                                    OR (
                                        apartmentallotments.occupiedon <= '$date_mod'
                                        AND apartmentallotments.vacatedon IS NULL
                                    )
                                )
                            ) AS total_occupied,
                            CASE
                        WHEN FOO1.total_signs IS NULL THEN
                            0
                        ELSE
                            FOO1.total_signs
                        END AS total_signs
                        FROM
                            (
                                SELECT
                                    facilities.id,
                                    (
                                        SELECT
                                            COUNT(apartment_id)
                                        FROM
                                            apartmentallotments
                                        WHERE
                                            date = '$date_mod'
                                        AND facility_id = facilities.id
                                        AND active = '1'
                                        AND deleted = '0'
                                    ) AS total_occupied,
                                    (
                                        SELECT
                                            COUNT(apartment_id)
                                        FROM
                                            attendances
                                        WHERE
                                            date = '$date_mod'
                                        AND facility_id = facilities.id
                                        AND active = '1'
                                        AND deleted = '0'
                                        AND attend = '1'
                                    ) AS total_signs
                                FROM
                                    attendances
                                INNER JOIN facilities ON facilities.id = attendances.facility_id
                                INNER JOIN states ON facilities.state_id = states.id
                                WHERE
                                    attendances.date = '$date_mod'
                                AND attendances.active = '1'
                                AND attendances.deleted = '0'
                                GROUP BY
                                    facilities.id, attendances.date, attendances.facility_id
                            ) FOO1
                        RIGHT JOIN (
                            SELECT DISTINCT
                                facilities.id AS occ_fac_id,
                                facilities.`code` AS occ_fac_code,
                                facilities.`name` AS facility_name,
                                facilities.zip,
                                facilities.city,
                                states.`name` AS state
                            FROM
                                apartmentallotments
                            INNER JOIN apartments ON apartments.id = apartmentallotments.apartment_id
                            INNER JOIN floors ON floors.id = apartments.floor_id
                            INNER JOIN facilities ON facilities.id = floors.facility_id
                            INNER JOIN states ON facilities.state_id = states.id
                            WHERE
                                apartmentallotments.deleted = '0'
                            AND apartmentallotments.active = '1'
                            AND (
                                (
                                    apartmentallotments.occupiedon <= '$date_mod'
                                    AND apartmentallotments.vacatedon >= '$date_mod'
                                )
                                OR (
                                    apartmentallotments.occupiedon <= '$date_mod'
                                    AND apartmentallotments.vacatedon IS NULL
                                )
                            )
                        ) FOO2 ON FOO1.id = FOO2.occ_fac_id
                    ) ATTENDANCEBYDATE
            "
        );

        return view('pages.attendances_show', compact('data', '$date'));
    }


    public function postByDate(Request $request){
        return redirect()->route('attendances.showByDate', ['date' => $request->date]);
    }

    public function showByDate($date)
    {
        $date_mod = (new \App\Custom\Custom)->convertDate($date, "Y-m-d");

        $data =  DB::select(
            "
                SELECT
                    facility_name,
                    occ_fac_code,
                    CONCAT(
                        city,
                        ', ',
                        state,
                        ', ',
                        zip
                    ) AS facility_address,
                    total_occupied,
                    total_signs,
                    CASE
                WHEN total_signs = '0' THEN
                    0
                ELSE
                    ROUND(
                        (total_signs / total_occupied) * 100,
                        2
                    )
                END AS sign_percentage
                FROM
                    (
                        SELECT
                            FOO2.occ_fac_code,
                            FOO2.facility_name,
                            FOO2.zip,
                            FOO2.city,
                            FOO2.state,
                            (
                                SELECT
                                    COUNT(apartment_id)
                                FROM
                                    apartmentallotments
                                INNER JOIN apartments ON apartments.id = apartmentallotments.apartment_id
                                INNER JOIN floors ON floors.id = apartments.floor_id
                                INNER JOIN facilities ON facilities.id = floors.facility_id
                                WHERE
                                    facilities.id = FOO2.occ_fac_id
                                AND apartmentallotments.active = '1'
                                AND apartmentallotments.deleted = '0'
                                AND (
                                    (
                                        apartmentallotments.occupiedon <= '$date_mod'
                                        AND apartmentallotments.vacatedon >= '$date_mod'
                                    )
                                    OR (
                                        apartmentallotments.occupiedon <= '$date_mod'
                                        AND apartmentallotments.vacatedon IS NULL
                                    )
                                )
                            ) AS total_occupied,
                            CASE
                        WHEN FOO1.total_signs IS NULL THEN
                            0
                        ELSE
                            FOO1.total_signs
                        END AS total_signs
                        FROM
                            (
                                SELECT
                                    facilities.id,
                                    (
                                        SELECT
                                            COUNT(apartment_id)
                                        FROM
                                            apartmentallotments
                                        WHERE
                                            date = '$date_mod'
                                        AND facility_id = facilities.id
                                        AND active = '1'
                                        AND deleted = '0'
                                    ) AS total_occupied,
                                    (
                                        SELECT
                                            COUNT(apartment_id)
                                        FROM
                                            attendances
                                        WHERE
                                            date = '$date_mod'
                                        AND facility_id = facilities.id
                                        AND active = '1'
                                        AND deleted = '0'
                                        AND attend = '1'
                                    ) AS total_signs
                                FROM
                                    attendances
                                INNER JOIN facilities ON facilities.id = attendances.facility_id
                                INNER JOIN states ON facilities.state_id = states.id
                                WHERE
                                    attendances.date = '$date_mod'
                                AND attendances.active = '1'
                                AND attendances.deleted = '0'
                                GROUP BY
                                    facilities.id, attendances.date, attendances.facility_id
                            ) FOO1
                        RIGHT JOIN (
                            SELECT DISTINCT
                                facilities.id AS occ_fac_id,
                                facilities.`code` AS occ_fac_code,
                                facilities.`name` AS facility_name,
                                facilities.zip,
                                facilities.city,
                                states.`name` AS state
                            FROM
                                apartmentallotments
                            INNER JOIN apartments ON apartments.id = apartmentallotments.apartment_id
                            INNER JOIN floors ON floors.id = apartments.floor_id
                            INNER JOIN facilities ON facilities.id = floors.facility_id
                            INNER JOIN states ON facilities.state_id = states.id
                            WHERE
                                apartmentallotments.deleted = '0'
                            AND apartmentallotments.active = '1'
                            AND (
                                (
                                    apartmentallotments.occupiedon <= '$date_mod'
                                    AND apartmentallotments.vacatedon >= '$date_mod'
                                )
                                OR (
                                    apartmentallotments.occupiedon <= '$date_mod'
                                    AND apartmentallotments.vacatedon IS NULL
                                )
                            )
                        ) FOO2 ON FOO1.id = FOO2.occ_fac_id
                    ) ATTENDANCEBYDATE
            "
        );

        return view('pages.attendances_show', compact('data', 'date'));
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
        if (!Auth::user()->can('Create-Attendance')) {
          return response()->json(['error' => array('You do not have enough permission(s)')]);
        }
        
        DB::beginTransaction();

        try {

            Attendance::where(['facility_id' => $id, 'date' => $request->date])->delete();

            if($request->hasFile('document')) {
              
               $file = $request->file('document');
               $name = $request->date.'-attendance-data.'.time().$file->getClientOriginalExtension();
               $file->move(public_path().'/file/attendance', $name);
               $refUrl = "/public/file/attendance/".$name;

               Attendancesheet::where(['date' => $request->date, 'facility_id' => $id])->delete();

               DB::table('attendancesheets')->insert([
                   'date' => $request->date,
                   'file' => $refUrl,
                   'facility_id' => $id,
               ]);

            } 

            foreach ($request->attendance as $key => $value) {

                DB::table('attendances')->insert([
                    'facility_id' => $request->facility_id,
                    'apartment_id' => $key,
                    'attend' => $value,
                    'date' => $request->date,
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                ]);

            }


            DB::select("DELETE FROM attendancecomments WHERE date = '$request->date' AND facility_id = '$id'");
            DB::table('attendancecomments')->insert([
                'date' => $request->date,
                'comment' => $request->comment,     
                'facility_id' => $id,             
            ]);


            DB::commit();

            return response()->json(['success'=>'Added new records.']);

        } catch (Exception $e) {
            DB::rollback();
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


    public function processForm(Request $request){

        $date = (new \App\Custom\Custom)->convertDate($request->date, "Y-m-d");

        return Redirect::route('attendances.showForm',array('facility' => $request->facility_id,'date' => $date));

    }


    public function showForm($facility_id, $date){

        $facilities = Facility::where(['active' => '1', 'deleted' => '0'])->get();

        $apartments = DB::select(
            "
                SELECT
                    apartments.*, facilities.`name` AS 'facility_name',
                    facilities.`code` AS 'facility_code',
                    facilities.`id` AS 'facility_id',
                    admissions.moveindate,
                    admissions.moveoutdate
                FROM
                    apartments
                INNER JOIN floors ON floors.id = apartments.floor_id
                INNER JOIN facilities ON facilities.id = floors.facility_id
                INNER JOIN admissions ON apartments.id = admissions.apartment_id
                WHERE
                facilities.id = '$facility_id'
                AND facilities.active = '1'
                AND facilities.deleted = '0'
                AND floors.active = '1'
                AND floors.deleted = '0'
                AND apartments.active = '1'
                AND apartments.deleted = '0'
                AND admissions.active = '1'
                AND admissions.deleted = '0'
                AND '$date' BETWEEN admissions.moveindate
                AND COALESCE (
                    admissions.moveoutdate,
                    NOW()
                )
            "
        );

        $apartments = $this->fetch_unique_apartments($apartments);
        //return $apartments;
        $check = Attendance::where(['facility_id' => $facility_id, 'date' => $date])->get();

        $commentHas = DB::SELECT("SELECT * FROM attendancecomments WHERE `date` = '$date' AND facility_id = '$facility_id'");

        if (count($check) > 0) {

            $occupied = array();
            foreach($check as $c) {
              if($c->attend == '1') {
                array_push($occupied, $c->apartment_id);
              }
            }


            $fileHas = DB::select(
                        "
                            SELECT
                                *
                            FROM
                                attendancesheets
                            WHERE
                                `date` = '$date'
                            AND facility_id = '$facility_id'
                        "
                    );

            return view('pages.attendances_update', compact('facilities','apartments','date','occupied', 'check', 'fileHas', 'commentHas'));
        }

        $fileHas = DB::select(
                        "
                            SELECT
                                *
                            FROM
                                attendancesheets
                            WHERE
                                `date` = '$date'
                            AND facility_id = '$facility_id'
                        "
                    );

        return view('pages.attendances', compact('facilities','apartments','date','fileHas', 'commentHas'));
    }


    function fetch_unique_apartments($input)
    {
        if (count($input) < 1) {
            return $input;
        }

        $output = [];

        array_push($output, array(
            'id' => $input[0]->id,
            'code' => $input[0]->code,
            'name' => $input[0]->name,
            'facility_name' => $input[0]->facility_name,
            'facility_code' => $input[0]->facility_code,
            'facility_id' => $input[0]->facility_id
        ));

        foreach ($input as $key => $value) {
            if (!in_array($value->code, array_column($output, 'code'))) {

                array_push($output, array(
                    'id' => $value->id,
                    'code' => $value->code,
                    'name' => $value->name,
                    'facility_name' => $value->facility_name,
                    'facility_code' => $value->facility_code,
                    'facility_id' => $value->facility_id
                ));

            }
        }

        return $output;

    }

}
