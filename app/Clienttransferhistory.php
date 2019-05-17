<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clienttransferhistory extends Model
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

    public function Client() {
    	return $this->belongsTo('App\Client', 'client_id');
    }

    public function prev_apt() {
    	return $this->belongsTo('App\Apartment', 'previous_apt_id');
    }

    public function new_apt() {
    	return $this->belongsTo('App\Apartment', 'new_apt_id');
    }
}
