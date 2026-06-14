<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;

class CheckTicketSla extends Command
{
    protected $signature = 'tickets:check-sla';
    protected $description = 'Flag open tickets whose SLA deadline has passed';

    public function handle(): int
    {
        $count = Ticket::whereNotIn('status', ['resolved', 'closed'])
            ->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<', now())
            ->where('is_overdue_sla', false)
            ->update(['is_overdue_sla' => true]);

        $this->info("Marked {$count} ticket(s) as SLA overdue.");

        return Command::SUCCESS;
    }
}
