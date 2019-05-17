<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    public function Vendor() {
    	$this->hasMany('App\Vendor', 'state_id');
    }
}
