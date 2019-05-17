<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    protected $fillable = ['code', 'name', 'facility_id'];

    public function Facility() {
    	return $this->belongsTo('App\Facility', 'facility_id');
    }

    public function Apartment() {
    	return $this->hasMany('App\Apartment', 'floor_id');
    }

    public function CreatedBy() {
    	return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
    	return $this->belongsTo('App\User', 'updatedbyuser_id');
    }
}
