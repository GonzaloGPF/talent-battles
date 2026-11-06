<?php

namespace App\Console\Commands;

use App\Events\NewDeploy;
use Illuminate\Console\Command;

class NotifyNewDeploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:new_deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches new deployment event to frontend';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        NewDeploy::dispatch();
    }
}
