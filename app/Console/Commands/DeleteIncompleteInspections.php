<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Inspection;
use App\Schedulerlog;

class DeleteIncompleteInspections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:deleteincompleteinspection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Incomplete Inspections Older Than 2 Days';

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

        $date = date('Y-m-d'); 
        $date = date("Y-m-d", strtotime($date ." -2 day"));

        $result = Inspection::where(['active' => '1', 'deleted' => '0', 'status' => 'INCOMPLETE'])
                    ->where('created_at', '<=', $date)
                    ->count();

        Schedulerlog::create(
            [
                'name' => 'deleteincompleteinspection',
                'description' => $result.' records found',
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
            ]
        );
        
    }
}
