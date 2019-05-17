<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Facilityaction extends Model
{

	protected $fillable = ['facility_id', 'deleted', 'created_at', 'createdbyuser_id', 'action'];

    public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function Facility() {
    	return $this->belongsTo('App\Facility','facility_id');
    }

}
