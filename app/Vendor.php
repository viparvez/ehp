<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = ['code', 'name', 'joindate', 'address', 'city', 'state_id', 'zip', 'email', 'phone'];

    public function State() {
    	return $this->belongsTo('App\State', 'state_id');
    }

    public function Facility(){
    	return $this->hasMany('App\Facility', 'vendor_id');
    }

    public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
        return $this->belongsTo('App\User', 'updatedbyuser_id');
    }

    public function Actions() {
        return $this->hasMany('App\Vendoraction','vendor_id');
    }
}
