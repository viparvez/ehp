<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Precondition extends Model
{
    protected $fillable = ['code', 'name'];

    public function Client(){
        return $this->hasMany('App\Client', 'precondition_id');
    }

    public function Preconditionchange() {
    	return $this->belongsTo('App\Preconditionchange','precondition_id');
    }

    public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
        return $this->belongsTo('App\User', 'updatedbyuser_id');
    }
}
