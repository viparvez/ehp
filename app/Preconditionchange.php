<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Preconditionchange extends Model
{
    protected $fillable = ['client_id',' precondition_id', 'comment'];

    public function Precondition() {
    	return $this->belongsTo('App\Precondition','precondition_id');
    }

    public function Client() {
    	return $this->belongsTo('App\Client', 'client_id');
    }

    public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
        return $this->belongsTo('App\User', 'updatedbyuser_id');
    }
}
