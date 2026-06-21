<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function download(string $filename): BinaryFileResponse
    {
        // Only allow well-formed backup filenames — never arbitrary paths.
        abort_unless((bool) preg_match('/^backup_[\w-]+\.sql$/', $filename), 403);

        $path = storage_path('app/backups/' . $filename);
        abort_unless(is_file($path), 404);

        return response()->download($path);
    }
}
