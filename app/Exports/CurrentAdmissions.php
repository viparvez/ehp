<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;
use App\Admission;
use Maatwebsite\Excel\Concerns\FromCollection;

class CurrentAdmissions implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DB::table('admissions')
        		->join('clients', 'clients.id', '=', 'admissions.client_id')
        		->join('apartments', 'apartments.id', '=', 'admissions.apartment_id')
        		->join('preconditions', 'preconditions.id', '=', 'clients.precondition_id')
        		->join('floors', 'floors.id', '=', 'apartments.floor_id')
        		->join('facilities', 'facilities.id', '=', 'floors.facility_id')
        		->where([
        			'admissions.deleted' => '0',
        			'admissions.active' => '1',
        			'admissions.moveoutdate' => null
        		])
        		->select(
        			'admissions.admissionid',
        			'clients.code as client_code',
        			'clients.fname',
        			'clients.lname',
        			'preconditions.name as precondition',
        			'facilities.code as facility_code',
        			'facilities.name as facility_name',
        			'apartments.name as apartment',
        			'admissions.moveindate'
        		)
        		->get();
    }

    public function headings(): array
    {
        return ['Admission ID', 'Client Code', 'Client First Name','Clients Last Name', 'Client Status', 'Facility Code', 'Facility Name', 'Apartment', 'Moveindate'];
    }

}
