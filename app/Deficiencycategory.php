<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Deficiencycategory extends Model
{
   protected $fillable = ['code', 'name'];

   public function Defdetails(){
    	return $this->hasMany('App\Deficiencydetail','category_id');
    }

    public function CreatedBy() {
    	return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
    	return $this->belongsTo('App\User', 'updatedbyuser_id');
    }

    public function Inspection($id) {
        return DB::select(
            "
                SELECT insdet.* FROM inspectiondetails insdet 
                INNER JOIN deficiencydetails defdet ON defdet.id = insdet.details_id
                INNER JOIN deficiencycategories defcat ON defcat.id = defdet.category_id
                INNER JOIN deficiencyconcerns defcon ON defcon.id = defdet.concern_id
                WHERE defcat.id = '$id'
            "
        );
    }
}
