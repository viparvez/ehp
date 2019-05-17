<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clientaction extends Model
{
    public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }
}
