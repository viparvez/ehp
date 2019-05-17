<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Apartmentaction extends Model
{
	protected $fillable = ['apartment_id', 'deleted', 'created_at', 'createdbyuser_id', 'action'];

    public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function Apartment() {
    	return $this->belongsTo('App\Apartment','apartment_id');
    }
}
