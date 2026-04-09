<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanBuildPartsCommand extends Command
{
    protected $signature = 'clean:buildparts';

    protected $description = 'Delete all build parts for fresh start';

    public function handle()
    {
        DB::table('build_parts')->truncate();
        $this->info('All build parts deleted!');

        // Also reset auto increment
        DB::statement('ALTER TABLE build_parts AUTO_INCREMENT = 1');
        $this->info('Auto increment reset!');

        return Command::SUCCESS;
    }
}
