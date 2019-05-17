<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;
use App\Facility;
use App\Floor;
use App\Apartment;
use App\Vendor;

class FacilityDetails implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DB::table('apartments')
        		->join('floors', 'floors.id', '=', 'apartments.floor_id')
        		->join('facilities', 'facilities.id', '=', 'floors.facility_id')
        		->join('vendors', 'vendors.id', '=', 'facilities.vendor_id')
        		->where([
        			'vendors.deleted' => '0',
        			'facilities.deleted' => '0',
        			'floors.deleted' => '0',
        			'apartments.deleted' => '0'
        		])
        		->select(
        			'vendors.code as vendor_code', 
        			'vendors.name as vendor_name', 
        			'facilities.code as facility_code',
        			'facilities.name as facility_name',
        			'facilities.type as facility_type',
        			'floors.name as floors_name',
        			'apartments.name as apartments_name'
        		)
        		->get();
    }

    public function headings(): array
    {
        return ['Vendor Code', 'Vendor Name', 'Facility Code', 'Facility Name', 'Facility Type', 'Floor', 'Apartment'];
    }
}
