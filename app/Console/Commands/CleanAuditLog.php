<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class CleanAuditLog extends Command
{
    protected $signature = 'audit:clean {--days=90 : Delete audit logs older than this many days}';
    protected $description = 'Delete audit log entries older than the retention window';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = AuditLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} audit log entr(y/ies) older than {$days} days.");

        return Command::SUCCESS;
    }
}
