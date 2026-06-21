<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep=30 : Number of recent backups to retain}';
    protected $description = 'Create a MySQL database backup (mysqldump) and prune old backups';

    public function handle(): int
    {
        $connection = config('database.connections.mysql');
        $dbName = $connection['database'];
        $dbUser = $connection['username'];
        $dbPass = $connection['password'];
        $dbHost = $connection['host'];
        $dbPort = $connection['port'] ?? 3306;

        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$dbName}_{$timestamp}.sql";
        $backupPath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        // mysqldump binary — overridable for environments where it isn't on PATH (e.g. local XAMPP).
        $binary = env('MYSQLDUMP_PATH', 'mysqldump');

        $args = [
            escapeshellarg($binary),
            '--host=' . escapeshellarg($dbHost),
            '--port=' . escapeshellarg((string) $dbPort),
            '--user=' . escapeshellarg($dbUser),
        ];
        if (! empty($dbPass)) {
            $args[] = '--password=' . escapeshellarg($dbPass);
        }
        $args[] = escapeshellarg($dbName);

        $command = implode(' ', $args) . ' > ' . escapeshellarg($backupPath) . ' 2>&1';

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            // mysqldump writes its error into the target file via the redirect.
            $detail = is_file($backupPath) ? trim((string) file_get_contents($backupPath)) : implode("\n", $output);
            if (is_file($backupPath)) {
                @unlink($backupPath);
            }
            $this->error("Backup failed: {$detail}");
            Log::error('Database backup failed', ['return_code' => $returnCode, 'detail' => $detail]);

            return Command::FAILURE;
        }

        $fileSize = filesize($backupPath);
        $sizeFormatted = $this->formatBytes($fileSize);

        $this->info("Backup created: {$filename} ({$sizeFormatted})");

        $this->cleanOldBackups((int) $this->option('keep'));

        ActivityLog::log(
            'database_backed_up',
            "Database backup created: {$filename} ({$sizeFormatted})",
            ['subject_label' => $filename]
        );

        return Command::SUCCESS;
    }

    private function cleanOldBackups(int $keep): void
    {
        $files = glob(storage_path('app/backups') . '/backup_*.sql') ?: [];
        if (count($files) <= $keep) {
            return;
        }

        // Newest first, then delete everything beyond the keep limit.
        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        foreach (array_slice($files, $keep) as $file) {
            @unlink($file);
            $this->info('Deleted old backup: ' . basename($file));
        }
    }

    private function formatBytes(int $bytes): string
    {
        return $bytes > 1048576
            ? round($bytes / 1048576, 2) . ' MB'
            : round($bytes / 1024, 2) . ' KB';
    }
}
