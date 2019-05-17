<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class FacilityAccessController extends Controller
{
    
	public function getPemissionById($facility_id) {
		
		$userRoles = Auth::user()->roles->pluck('name')->toArray();
        $userFacilities =  Auth::user()->Facility->pluck('facility_id')->toArray();

        if ($this->isSuper() == false) {
          if (!in_array($facility_id, $userFacilities)) {
            return false;
          }
        } 

        return true;

	}


	public function isSuper() {

		$userRoles = Auth::user()->roles->pluck('name')->toArray();

		if (in_array('Admin', $userRoles) || in_array('Superuser', $userRoles)) {
          return true;
        }

        return false;

	}


	public function userFacilities(){
		return Auth::user()->Facility->pluck('facility_id')->toArray();
	}

}
