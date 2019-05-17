<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Scheduler;
use App\Http\Controllers\EmailController;

class ScheduledTaskController extends Controller
{
    public function dropIncompleteInspections() {
    	try {

    		DB::connection()->enableQueryLog();

	    	$query = DB::select(
	    		"
	    			UPDATE inspections
	    			SET deleted = '1',
	    			 updatedbyuser_id = (
	    				SELECT
	    					id
	    				FROM
	    					users
	    				WHERE
	    					name = 'Scheduler'
	    			),
	    			updated_at = NOW()
	    			WHERE
	    				STATUS = 'INCOMPLETE'
	    			AND created_at < CURDATE() - INTERVAL 3 DAY
	    		"
	    	);	

	    	$log = json_encode(DB::getQueryLog());
    		
    		DB::table('schedulers')->insert([
    			'task_name' => 'Drop incomplete inspections older than 72 hours.',
    			'runon' => date('Y-m-d h:i:s'),
    			'description' => $log
    		]);

    	} catch (Exception $e) {

    		$error = $e->getMessage();

    		DB::table('schedulers')->insert([
    			'task_name' => 'Drop incomplete inspections older than 72 hours.',
    			'runon' => date('Y-m-d h:i:s'),
    			'description' => $error
    		]);
    	}
    }


    public function capAlert() {
    	try {

    		$subject = 'Inspection CAP alert.';

    		DB::connection()->enableQueryLog();

	    	$query = DB::select(
	    		/*"
	    			SELECT
	    				inspections.`code`,
	    				inspections.cap_due_date,
	    				users.`name`,
	    				users.email
	    			FROM
	    				inspections
	    			INNER JOIN users ON users.id = inspections.updatedbyuser_id
	    			WHERE
	    				cap_due_date = DATE_ADD(curdate(), INTERVAL 1 DAY)
	    			AND `status` = 'COMPLETED'
	    			AND followedbyins_id IS NULL
	    		"
*/	    
                "
                    SELECT
                        inspections.`code`,
                        inspections.cap_due_date,
                        users.`name`,
                        users.email
                    FROM
                        inspections
                    INNER JOIN users ON users.id = inspections.updatedbyuser_id
                    WHERE
                        inspections.id = '10'
                "
        	);	

	    	$log = json_encode(DB::getQueryLog());
    		
            if (!empty($query)) {
                foreach ($query as $key => $value) {
                    $email = array($value);
                    $body="
                        <html>
                        <head>
                        <meta http-equiv=Content-Type content='text/html; charset=windows-1252'>
                        </head>

                        <body>
                        <h4>Hello ".$value->name.",</h4>

                        <p>You have <b style='mso-bidi-font-weight:
                        normal'><span style='color:#0070C0'>".$value->code."</span></b><span style='color:
                        #0070C0'> </span>pending for follow up. CAP due date expiring on <b
                        style='mso-bidi-font-weight:normal'>".$value->cap_due_date."</b>. Please take initiative.<o:p></o:p></span></p>
                        <p align=center style='text-align:center'><span
                        style='font-size:10.0pt;line-height:107%'>This is a system generated email.
                        Please do not reply.<o:p></o:p></span></p>

                        </div>
                        </body>
                        </html>

                    ";

                    (new EmailController)->sendmail(array($value->email),$body,null, $subject); 

                }
            }
    		

    		DB::table('schedulers')->insert([
    			'task_name' => 'CAP email notification',
    			'runon' => date('Y-m-d h:i:s'),
    			'description' => $log
    		]);

    	} catch (Exception $e) {

    		$error = $e->getMessage();

    		DB::table('schedulers')->insert([
    			'task_name' => 'CAP email notification',
    			'runon' => date('Y-m-d h:i:s'),
    			'description' => $error
    		]);
    	}  	
    }

}
