<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Schedulerlog;

class Nosignsheetdata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:nosignsheetdata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks attendance data missing for last 3 days';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $facilities = DB::SELECT(
            "
                SELECT
                    *
                FROM
                    facilities
                WHERE
                    facilities.id IN (
                        SELECT
                            facilities.id
                        FROM
                            admissions
                        INNER JOIN apartments ON apartments.id = admissions.apartment_id
                        INNER JOIN floors ON floors.id = apartments.floor_id
                        INNER JOIN facilities ON floors.facility_id = facilities.id
                    )
                AND facilities.id NOT IN (
                    SELECT DISTINCT
                        (facility_id)
                    FROM
                        attendances
                    WHERE
                        active = '1'
                    AND deleted = '0'
                    AND date >= DATE_SUB(NOW(), INTERVAL 4 DAY) 
                )
            "
        );


        Schedulerlog::create(
            [
                'name' => 'nosignsheetdata',
                'description' => count($facilities).' records found',
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
            ]
        );
    }
}
