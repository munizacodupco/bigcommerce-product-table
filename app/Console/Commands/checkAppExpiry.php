<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Store;

use Carbon\Carbon;

class checkAppExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store_app:check_expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the expiry of the app everyday.';

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
     * @return int
     */
    public function handle()
    {
        _log('Running cron', 'CronLog');
        $date  = Carbon::now()->format('Y-m-d H:i:s');
        $stores = Store::where( 'expires_at', '<=', $date )->get();
      
        if( $stores ) {
            $stores->each(function ($store ) {
                $store->update_access( 0 );
                $store->save();
            });
        }
        echo "runnning...";
    }
}
