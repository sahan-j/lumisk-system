<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class CleanActivityLog extends Command
{
    protected $signature = 'activity:clean {--days=90 : Delete logs older than this many days}';
    protected $description = 'Delete activity log entries older than the retention window';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = ActivityLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} activity log entr(y/ies) older than {$days} days.");

        return Command::SUCCESS;
    }
}
