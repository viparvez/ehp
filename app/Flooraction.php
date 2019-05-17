<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Flooraction extends Model
{
	protected $fillable = ['floor_id', 'deleted', 'created_at', 'createdbyuser_id', 'action'];

    public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function Floor() {
    	return $this->belongsTo('App\Floor','floor_id');
    }
}
