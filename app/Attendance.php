<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
	protected $fillable = ['facility_id','apartment_id','date', 'comment', 'createdbyuser_id', 'updatedbyuser_id'];

	public function Facility() {
		return $this->belongsTo('App\Facility', 'facility_id');
	}

	public function Apartment() {
		return $this->belongsTo('App\Apartment', 'apartment_id');
	}

    public function CreatedBy() {
    	return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
    	return $this->belongsTo('App\User', 'updatedbyuser_id');
    }

}
