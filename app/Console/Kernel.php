<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use DB;
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        //DB::table('test')->insert(['name' => 'Import Types', 'date' => date('Y-m-d H:i:s')]);
        $schedule->command('unionOne:today')->everyFiveMinutes();
        $schedule->command('cms:import-couriers-customers')->hourly()->withoutOverlapping();
        //$schedule->command('unionOneCategories:today')->everyFiveMinutes();
        //$schedule->command('unionOneTypes:today')->everyFiveMinutes();
        //$schedule->command('unionOneVendors:today')->everyFiveMinutes();
        //$schedule->command('shipright:today')->everyFiveMinutes();
        //$schedule->command('indiamart2:today')->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
