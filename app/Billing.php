<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $fillable = ['bill_id', 'month', 'facility_id', 'createdbyuser_id', 'updatedbyuser_id', 'deleted', 'rate', 'total_amount'];

    public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
        return $this->belongsTo('App\User', 'updatedbyuser_id');
    }

    public function Facility() {
    	return $this->belongsTo('App\Facility', 'facility_id');
    }

    public function Details() {
        return $this->hasMany('App\BillingDetails', 'billing_id');
    }
}
