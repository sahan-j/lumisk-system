<?php

namespace App\Livewire\Admin\Backups;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Database Backups')]
class BackupsIndex extends Component
{
    public bool $creating = false;

    public function createBackup(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('settings.edit'), 403);

        $this->creating = true;

        $exitCode = Artisan::call('db:backup');

        if ($exitCode === 0) {
            $this->dispatch('toast', type: 'success', message: 'Backup created successfully.');
        } else {
            $this->dispatch('toast', type: 'error', message: 'Backup failed. Check the logs for details.');
        }

        $this->creating = false;
    }

    public function deleteBackup(string $filename): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('settings.edit'), 403);

        if (! preg_match('/^backup_[\w-]+\.sql$/', $filename)) {
            $this->dispatch('toast', type: 'error', message: 'Invalid backup file.');

            return;
        }

        $path = storage_path('app/backups/' . $filename);
        if (is_file($path)) {
            @unlink($path);
            $this->dispatch('toast', type: 'success', message: 'Backup deleted.');
        }
    }

    public function render()
    {
        $files = glob(storage_path('app/backups') . '/backup_*.sql') ?: [];
        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        $backups = collect($files)->map(function ($path) {
            $size = filesize($path);

            return [
                'filename' => basename($path),
                'size_formatted' => $size > 1048576
                    ? round($size / 1048576, 2) . ' MB'
                    : round($size / 1024, 2) . ' KB',
                'created_at' => Carbon::createFromTimestamp(filemtime($path)),
            ];
        });

        return view('livewire.admin.backups.backups-index', [
            'backups' => $backups,
        ]);
    }
}
