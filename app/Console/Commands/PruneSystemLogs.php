<?php

namespace App\Console\Commands;

use App\Models\SystemLog;
use Illuminate\Console\Command;

class PruneSystemLogs extends Command
{
    protected $signature = 'logs:prune {--days=45 : Keep logs newer than this many days}';

    protected $description = 'Delete old system logs based on retention days';

    public function handle(): int
    {
        $days = max((int) $this->option('days'), 1);
        $cutoff = now()->subDays($days);

        $deleted = SystemLog::where('created_at', '<', $cutoff)->delete();
        $this->info("Pruned {$deleted} log rows older than {$days} days.");

        return self::SUCCESS;
    }
}

