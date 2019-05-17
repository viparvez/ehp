<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Facilitydocument extends Model
{
    public function Facility() {
    	return $this->belongsTo('App\Facility', 'facility_id');
    }
}
