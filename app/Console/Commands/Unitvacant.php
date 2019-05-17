<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Apartment;
use App\Schedulerlog;
use App\Http\Controllers\EmailController;

class Unitvacant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:unitvacant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search units that are vacant for more than 15 days';

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
        $date = date('Y-m-d'); // date you want to upgade
        $date = date("Y-m-d", strtotime($date ." -15 day"));

        $vanact = Apartment::where(['free' => '1', 'active' => '1', 'deleted' => '0'])
                    ->where('vacantfrom', '<=', $date)
                    ->get();

        Schedulerlog::create(
            [
                'name' => 'unitvacant',
                'description' => count($vanact).' records found',
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
            ]
        );


        $body="
            <html>
            <head>
            <meta http-equiv=Content-Type content='text/html; charset=windows-1252'>
            </head>

            <body>
            <h4>Hello Sirajum Monir,</h4>

            <p>This is a system Generated Email. Please ignore if you have got the message in your inbox.</p>
            <p align=center style='text-align:center'><span
            style='font-size:10.0pt;line-height:107%'>This is a system generated email.
            Please do not reply.<o:p></o:p></span></p>

            </div>
            </body>
            </html>

        ";

        (new EmailController)->sendmail(array('viparvez@gmail.com'),$body,null, 'Test Email From EHP'); 

    }
}
