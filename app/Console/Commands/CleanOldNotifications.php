<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanOldNotifications extends Command
{
    protected $signature = 'notifications:clean {--days=90 : Delete read notifications older than this many days}';
    protected $description = 'Delete read in-app notifications past the retention window';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('read_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Deleted {$deleted} old notification(s).");

        return Command::SUCCESS;
    }
}
