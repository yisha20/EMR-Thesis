<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClinicHealthCheck extends Command
{
    protected $signature = 'clinic:health-check';
    protected $description = 'Check the clinic EMR runtime requirements and writable paths';

    public function handle()
    {
        $checks = [
            ['PHP GD extension', extension_loaded('gd'), 'PHP GD is required for Medical Certificate PDF image rendering. Enable the GD PHP extension and restart the web server.'],
            ['PHP mbstring extension', extension_loaded('mbstring'), 'Enable the PHP mbstring extension.'],
            ['PHP DOM extension', extension_loaded('dom'), 'Enable the PHP DOM/XML extension.'],
            ['PHP XML extension', extension_loaded('xml'), 'Enable the PHP XML extension.'],
            ['PHP fileinfo extension', extension_loaded('fileinfo'), 'Enable the PHP fileinfo extension.'],
            ['Dompdf library', class_exists(\Dompdf\Dompdf::class), 'Install the Composer PDF dependencies.'],
            ['Clinic logo readable', is_readable(public_path('img/msu-iit-logo.png')), 'Ensure public/img/msu-iit-logo.png exists and is readable.'],
            ['Storage writable', is_writable(storage_path()), 'Grant the web-service account write access to storage.'],
            ['Bootstrap cache writable', is_writable(base_path('bootstrap/cache')), 'Grant the web-service account write access to bootstrap/cache.'],
            ['Temporary directory writable', is_writable(sys_get_temp_dir()), 'Configure a writable PHP temporary directory.'],
        ];

        $failed = false;
        foreach ($checks as [$name, $passes, $help]) {
            if ($passes) {
                $this->info('[PASS] '.$name);
                continue;
            }

            $failed = true;
            $this->error('[FAIL] '.$name);
            $this->line('       '.$help);
        }

        $this->line('PHP: '.PHP_VERSION);
        $this->line('php.ini: '.(php_ini_loaded_file() ?: 'not loaded'));
        $this->line('Temporary directory: '.sys_get_temp_dir());

        return $failed ? 1 : 0;
    }
}
