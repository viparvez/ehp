<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vendoraction extends Model
{

	protected $fillable = ['vendor_id', 'deleted', 'created_at', 'createdbyuser_id', 'action'];

    public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function Vendor() {
    	return $this->belongsTo('App\Vendor','vendor_id');
    }
}
