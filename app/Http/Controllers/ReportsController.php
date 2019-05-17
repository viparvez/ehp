<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exports\UsersExport;
use App\Exports\FacilityDetails;
use App\Exports\CurrentAdmissions;
use App\Exports\ClientDetails;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Admission;
use Auth;

class ReportsController extends Controller
{
    
    
    public function index() {
        if (!Auth::user()->can('View-Reports')) {
          return view('pages.550');
        }
        return view('pages.report');
    }

    public function export(Request $request) 
    {
        if ($request->name == 'Location Chain') {

            return $this->location_chain();

        } elseif ($request->name == 'Current Admissions') {

            return $this->current_admission();

        }elseif ($request->name == 'Client Details') {

            return $this->client_details();

        }
    }

    public function location_chain() 
    {
        return Excel::download(new FacilityDetails, 'location_chain.xlsx');
    }

    public function current_admission() 
    {
        return Excel::download(new CurrentAdmissions, 'current_admission.xlsx');
    }

    public function client_details(){
    	return Excel::download(new ClientDetails, 'client_details.xlsx');
    }
}
