<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;


class ClientDetails implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */


    public function collection()
    {
        return DB::table('clients')
        		->join('preconditions', 'preconditions.id', '=', 'clients.precondition_id')
        		->where(['clients.active' => '1', 'clients.deleted' => '0'])
        		->select(
        			'clients.code', 
        			'clients.fname as First_Name', 
        			'clients.lname as Last_Name',
        			DB::raw('null as SSN'),
        			DB::raw('null as medicaid'),
        			'clients.dob as Date_of_Birth',
        			'clients.email',
        			'clients.phone',
        			'preconditions.name as Precondition'
        		)->get();
    }


    public function headings(): array
    {
        return ['Code', 'First Name', 'Last Name','SSN', 'Medica ID', 'Date of Birth', 'Email', 'Phone', 'Precondition'];
    }

}
