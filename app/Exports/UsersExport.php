<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;

class UsersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DB::table('users')
        		->select(['name', 'email', 'designation', 'phone'])
        		->where(['deleted' => '0', 'active' => '1'])
        		->get();
    }

    public function headings(): array
    {
        return ['name', 'email', 'designation', 'phone'];
    }
}
