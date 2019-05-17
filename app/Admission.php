<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Admission extends Model
{
    protected $fillable = ['admissionid', 'client_id', 'apartment_id', 'moveindate', 'moveoutdate'];

    public function Client() {
    	return $this->belongsTo('App\Client', 'client_id');
    }

    public function Apartment() {
    	return $this->belongsTo('App\Apartment', 'apartment_id');
    }

    public function Apartmentallotment() {
    	return $this->hasMany('App\Apartmentallotment', 'apartment_id');
    }

    public function History() {
        return $this->hasMany('App\Admissionhistory', 'admission_id');
    }

    public function currentAptDetails($admission_id, $apartment_id) {
        return DB::select(
            "
                SELECT * FROM apartmentallotments 
                WHERE admission_id = '$admission_id'
                AND apartment_id = '$apartment_id'
            "
        );
    }

    public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
        return $this->belongsTo('App\User', 'updatedbyuser_id');
    }
}
