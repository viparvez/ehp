<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Apartmentallotment extends Model
{
    protected $fillable = ['admission_id','moveindate','moveoutdate'];

    public function Admission() {
    	return $this->belongsTo('App\Admission', 'admission_id');
    }

    public function CreatedBy() {
    	return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
    	return $this->belongsTo('App\User', 'updatedbyuser_id');
    }
}
