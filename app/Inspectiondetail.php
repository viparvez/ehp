<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inspectiondetail extends Model
{
    public function Inspection() {
    	return $this->belongsTo('App\Inspection', 'inspection_id');
    }

    public function Deficiencydetail() {
    	return $this->belongsTo('App\Deficiencydetail','details_id');
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
