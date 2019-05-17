<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedulerlog extends Model
{
    protected $table = 'schedulerlogs';

    protected $fillable = ['name', 'description'];
}
