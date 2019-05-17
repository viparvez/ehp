<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Billingdetail extends Model
{
	protected $fillable = ['billing_id', 'apartment_id', 'client_id', 'admission_id', 'total_days', 'moveindate', 'moveoutdate', 'amount'];

    public function Billing() {
        return $this->belongsTo('App\Billing', 'billing_id');
    }

    public function Apartment() {
        return $this->belongsTo('App\Apartment', 'apartment_id');
    }

    public function Client() {
        return $this->belongsTo('App\Client', 'client_id');
    }

    public function Admission() {
        return $this->belongsTo('App\Admission', 'admission_id');
    }
}
