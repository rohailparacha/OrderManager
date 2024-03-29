<?php

namespace App\Console;
use Config;
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

        //$schedule->call('App\Http\Controllers\productsController@repricing')->timezone('Asia/Tehran')->dailyAt('02:30');

        $schedule->command('queue:restart')->everyFiveMinutes();
        
    //    $schedule->call(function () {DB::table('categories')->insert(['name'=>'cronCheck']);  })->everyMinute();
        
        $schedule->command('queue:listen --timeout=0')->everyMinute()->withoutOverlapping();
    
        $schedule->command('queue:work --daemon --timeout=0')->everyMinute()->withoutOverlapping();
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
