<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Client extends Model
{
    protected $fillable = [
    	'code',
    	'fname',
    	'lname',
    	'ssn',
    	'medicaid',
    	'dob',
    	'email',
    	'phone',
    	'comment',
    	'img_url',
        'precondition_id'
    ];

    public function Precondition(){
        return $this->belongsTo('App\Precondition', 'precondition_id');
    }

    public function Preconditionchange() {
        return $this->hasMany('App\Preconditionchange','client_id');
    }

    public function Admission() {
        return $this->hasMany('App\Admission', 'client_id')->where(['active' => '1', 'deleted' => '0']);
    }

    public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
        return $this->belongsTo('App\User', 'updatedbyuser_id');
    }

    public function getAttendanceAlert($id) {

        return $id;

    }


    public function currentAdmission($clientId) {

        $currentAdmission = DB::select(
            "
                SELECT * FROM admissions
                WHERE client_id = '$clientId'
                AND admissions.deleted = '0'
                AND moveoutdate IS NULL
            "
        );

        if (empty($currentAdmission)) {
            return false;
        }

        return true;

    }

}
