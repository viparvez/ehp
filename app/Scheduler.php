<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scheduler extends Model
{
    protected $fillable = ['task_name', 'runon', 'description'];
}
