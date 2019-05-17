<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Facility;
use App\Billing;
use Validator;
use App\Billingdetail;
use Auth;

class BillingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Auth::user()->can('View-Bill')) {
          return view('pages.550');
        }

        $facilities = Facility::where(['deleted'=>'0', 'active'=>'1'])->orderBy('code', 'ASC')->get();
        $bills = Billing::where(['deleted'=>'0'])->orderBy('invoice_code', 'DESC')->get();
        return view('pages.billings', compact('facilities','bills'));
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
        if (!Auth::user()->can('Create-Bill')) {
          return response()->json(['error' => array('You do not have enough permission(s)')]);
        }

        $validator = Validator::make($request->all(), [
            'facility_id' => 'required',
            'month' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        if ($request->year > date('Y')) {
            return response()->json(['error'=>
                array(
                    'Cannot generate bill upfront'
                )
            ]);
        } elseif($request->month >= date('m') && $request->year == date('Y')) {
            return response()->json(['error'=>
                array(
                    'Cannot generate bill upfront'
                )
            ]);
        }

        $check = Billing::where(
            [
                'deleted' => '0', 
                'month' => date('F', mktime(0, 0, 0, $request->month, 10)), 
                'year' => date('Y'),
                'facility_id' => $request->facility_id
            ]
        )->first();

        if (!empty($check)) {
            return response()->json(['error'=>
                array(
                    'Cannot regenerate bill. Please check invoice number: '.$check->invoice_code
                )
            ]);
        } 


        $first_day = $this->FirstDayOfMonth($request->month)." 00:00:00";
        $last_day = $this->lastDayOfMonth($request->month)." 23:59:59";
        
        DB::statement("SET @Months = $request->month");
        DB::statement("SET @NXT_MONTH = @Months + 1");
        DB::statement("SET @Years = $request->year");
        DB::statement("SET @FIRST_DATE = DATE_FORMAT(DATE(concat(@Years, '-', @Months, '-01')), '%Y-%m-%d')");
        DB::statement("SET @F_DATE_NXT_MONTH = DATE_FORMAT(DATE(concat(@Years, '-', @NXT_MONTH, '-01')),'%Y-%m-%d');");
        DB::statement("SET @LAST_DATE = LAST_DAY(DATE(concat(@Years, '-', @Months, '-01')))");
        
        $bill = DB::select(
            "
                SELECT
                    *, total_days * rent AS billed_amt
                FROM(
                    SELECT
                        apartmentallotments.id,
                        apartments.`code` AS room,
                        apartments.id AS apartment_id,
                        facilities.rate AS rent,
                        admissions.id AS admission_id,
                        admissions.admissionid as admissionnum,
                        CONCAT(
                            clients.fname,
                            ' ',
                            clients.lname
                        ) AS client,
                        clients.id AS clientid, 
                        clients.ssn,
                        apartmentallotments.occupiedon AS moveindate,
                        apartmentallotments.vacatedon AS moveoutdate,
                        CASE 
                                WHEN ISNULL(apartmentallotments.vacatedon)
                                THEN 
                                        CASE
                                            WHEN apartmentallotments.occupiedon BETWEEN @FIRST_DATE AND @LAST_DATE
                                            THEN DATEDIFF(@LAST_DATE,apartmentallotments.occupiedon)
                                            WHEN apartmentallotments.occupiedon < @FIRST_DATE
                                            THEN DATEDIFF(@F_DATE_NXT_MONTH,@FIRST_DATE)
                                            ELSE 'FUTURE MONTH'
                                        END
                                ELSE 
                                        CASE
                                                WHEN apartmentallotments.occupiedon BETWEEN @FIRST_DATE AND @LAST_DATE
                                                THEN
                                                        CASE
                                                            WHEN apartmentallotments.vacatedon >= @LAST_DATE
                                                            THEN DATEDIFF(@LAST_DATE,apartmentallotments.occupiedon)
                                                            WHEN apartmentallotments.vacatedon < @LAST_DATE
                                                            THEN DATEDIFF(apartmentallotments.vacatedon,apartmentallotments.occupiedon)
                                                            ELSE 'INVALID_1'
                                                        END
                                                WHEN apartmentallotments.occupiedon < @FIRST_DATE 
                                                THEN
                                                        CASE
                                                            WHEN apartmentallotments.vacatedon < @FIRST_DATE
                                                            THEN 'OLDER_ADMISSION'
                                                            WHEN apartmentallotments.vacatedon BETWEEN @FIRST_DATE AND @LAST_DATE
                                                            THEN DATEDIFF(apartmentallotments.vacatedon,@FIRST_DATE)
                                                            WHEN apartmentallotments.vacatedon >= @LAST_DATE
                                                            THEN DATEDIFF(@F_DATE_NXT_MONTH,@FIRST_DATE)
                                                            ELSE 'INVALID_2'
                                                        END
                                                ELSE 'FUTURE'
                                        END
                        END AS total_days
                    FROM
                        apartmentallotments
                    INNER JOIN apartments ON apartments.id = apartmentallotments.apartment_id
                    INNER JOIN admissions ON admissions.id = apartmentallotments.admission_id
                    INNER JOIN floors ON floors.id = apartments.floor_id
                    INNER JOIN facilities ON facilities.id = floors.facility_id
                    INNER JOIN clients ON clients.id = admissions.client_id
                    WHERE facilities.id = '$request->facility_id'
                    AND apartmentallotments.deleted = '0'
                ) AS FOO
            "
        );
        

        $total_amount = 0;

        foreach ($bill as $key => $value) {
            $total_amount += $value->billed_amt;
        }

        if (empty($bill)) {
           return response()->json(['error'=>array('No data found to be billed.')]);
        } elseif ($total_amount == 0) {
            return response()->json(['error'=>array('No data found to be billed.')]);
        }

        DB::beginTransaction();

        try {

            $id = DB::table('billings')->insertGetId(
                [
                    'invoice_code' => time(),
                    'month' => date('F', mktime(0, 0, 0, $request->month, 10)),
                    'year' => date('Y'),
                    'total_amount' => $total_amount,
                    'facility_id' => $request->facility_id,
                    'rate' => $bill[0]->rent,
                    'deleted' => '0',
                    'createdbyuser_id' => Auth::user()->id,
                    'updatedbyuser_id' => Auth::user()->id,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s')
                ]
            );  

            foreach ($bill as $key => $value) {

                if ($value->billed_amt != 0) {
                    Billingdetail::create(
                        [
                            'billing_id' => $id,
                            'apartment_id' => $value->apartment_id,
                            'client_id' => $value->clientid,
                            'admission_id' => $value->admission_id,
                            'total_days' => $value->total_days,
                            'moveindate' => $value->moveindate,
                            'moveoutdate' => $value->moveoutdate,
                            'amount' => $value->billed_amt,
                            'created_at' => date('Y-m-d h:i:s'),
                            'updated_at' => date('Y-m-d h:i:s')
                        ]
                    );
                }
                
            }

            $code = 'INV-'.sprintf('%06d', $id);

            Billing::where(['id' => $id])->update(
              [
                'invoice_code' => $code,
              ]
            );

            DB::commit();

        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['error'=>array('Could not generate bill')]);
        }

         return response()->json(['success'=>array('Success.')]);

    }

    private function FirstDayOfMonth($month) {
        $date_string = "$month 01 ".date('Y');
        $first_day = date('Y-m-01', strtotime($date_string));
        return $first_day;
    }

    private function lastDayOfMonth($month) {
        $date_string = "$month 01 ".date('Y');
        $last_day = date('Y-m-t', strtotime($date_string));
        return $last_day;
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Auth::user()->can('View-Bill-Details')) {
          return "<p style='color:red; text-align:center'>Permission Denied</p>";
        }

        $bill_details = Billingdetail::where(['billing_id' => $id])->get();
        return view('pages.invoicedetails',compact('bill_details'));
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
        if (!Auth::user()->can('Delete-Bill')) {
          return response()->json(['error' => array('You do not have enough permission(s)')]);
        }
        
        try {

            DB::beginTransaction();

            Billing::where(['id' => $id])->update(
                [
                    'deleted' => '1',
                    'updatedbyuser_id' => Auth::user()->id,
                    'updated_at' => date('Y-m-d h:i:s'),
                ]
            );

            DB::commit();

            return response()->json(['success'=>'Deleted!']);

            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error'=>array('Could not delete')]);
        }

    }

    public function getBillDeletion($id) {
      
      if (!Auth::user()->can('Delete-Bill')) {
        return "<p style='color:red; text-align:center'>Permission Denied</p>";
      }
      
      $isActive = Billing::where(['id' => $id])->first();

      return "
          <div class='alert alert-danger print-error-msg' id='error_messages' style='display:none'>
              <ul></ul>
          </div>

          <form id='editRole' action='".route('billings.destroy',$id)."' method='POST'>

            ".csrf_field()."

            <input type='hidden' name='_method' value='DELETE'>

            <div class='form-group'>
              <h2 style='color:red; text-align:center'>This will delete the bill from the system. Are you sure?</h2>
            </div>


            <button class='btn btn-block btn-success btn-sm' id='submitEdit' type='submit'>YES</button>
            <button class='btn btn-block btn-success btn-sm' id='loadingEdit' style='display: none' disabled=''>Working...</button>

          </form>
      ";

    }

}
