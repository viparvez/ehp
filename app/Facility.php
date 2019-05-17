<?php

namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = ['vendor_id', 'code', 'name', 'hasMedicine', 'hasHandicapAccess', 'isSmokeFree', 'hasElevator', 'city', 'comment', 'state_id', 'zip', 'contact_p', 'email', 'phone'];

    public function Vendor(){
    	return $this->belongsTo('App\Vendor', 'vendor_id');
    }

    public function Floor() {
    	return $this->hasMany('App\Floor', 'facility_id');
    }


    public function ApartmentVacant($facility_id) {
        return count(
                DB::select("
                SELECT * FROM apartments 
                INNER JOIN floors ON floors.id = apartments.floor_id
                INNER JOIN facilities ON facilities.id = floors.facility_id
                WHERE floors.deleted = '0'
                AND apartments.deleted = '0'
                AND facilities.deleted = '0'
                AND facilities.id= '$facility_id'
                AND apartments.free = '1'
                ")
            );

        /*return (string) $this->hasManyThrough(
            'App\Apartment',
            'App\Floor',
            'facility_id',
            'floor_id',
            'id'
        );*/
    }

    public function Apartment($facility_id) {
        return DB::select(
                    "
                        SELECT * FROM apartments 
                        INNER JOIN floors ON floors.id = apartments.floor_id
                        INNER JOIN facilities ON facilities.id = floors.facility_id
                        WHERE floors.deleted = '0'
                        AND apartments.deleted = '0'
                        AND facilities.deleted = '0'
                        AND facilities.id= '$facility_id'
                    "
                );
    }

    public function Inspection() {
    	return $this->hasMany('App\Inspection', 'facility_id');
    }

    public function CreatedBy() {
        return $this->belongsTo('App\User', 'createdbyuser_id');
    }

    public function UpdatedBy() {
        return $this->belongsTo('App\User', 'updatedbyuser_id');
    }

    public function State() {
        return $this->belongsTo('App\State', 'state_id');
    }

    public function Facilitydocuments() {
        return $this->hasMany('App\Facilitydocument', 'facility_id');
    }

}
