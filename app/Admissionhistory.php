<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Admissionhistory extends Model
{
    public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
        return $this->belongsTo('App\User', 'updatedbyuser_id');
    }

    public function Admission() {
    	return $this->belongsTo('App\Admission', 'admission_id');
    }
}
