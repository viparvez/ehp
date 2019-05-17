<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $fillable = ['code','date','facility_id','total_inspected_area','cap_due_date'];

    public function Facility() {
    	return $this->belongsTo('App\Facility', 'facility_id');
    }

    public function Inspectiondetail() {
    	return $this->hasMany('App\Inspectiondetail', 'inspection_id');
    }

    public function Followed() {
        return $this->belongsTo('App\Inspection','followedins_id');
    }

    public function Followedby() {
        return $this->belongsTo('App\Inspection','followedbyins_id');
    }

    public function CreatedBy() {
    	return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
    	return $this->belongsTo('App\User', 'updatedbyuser_id');
    }
}
