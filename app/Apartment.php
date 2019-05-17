<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    protected $fillable = ['code', 'name', 'floor_id', 'vacantfrom'];

    public function Floor() {
    	return $this->belongsTo('App\Floor', 'floor_id');
    }

    public function Admission() {
    	return $this->hasMany('App\Admission', 'apartment_id');
    }

    public function Inspectiondetail() {
    	return $this->hasMany('App\Inspectiondetail', 'apartment_id');
    }

    public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
        return $this->belongsTo('App\User', 'updatedbyuser_id');
    }
}
