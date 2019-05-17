<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deficiencydetail extends Model
{
    protected $fillable = ['code','concern_id','category_id','weightage','description'];

    public function Concern() {
    	return $this->belongsTO('App\Deficiencyconcern','concern_id');
    }

    public function Category() {
    	return $this->belongsTo('App\Deficiencycategory','category_id');
    }

    public function Inspectiondetails() {
    	return $this->hasMany('App\Inspectiondetail','details_id');
    }

    public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
        return $this->belongsTo('App\User', 'updatedbyuser_id');
    }
}
