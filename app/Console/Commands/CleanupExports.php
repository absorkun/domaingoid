<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupExports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-exports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $files = Storage::disk('public')->files('exports');

        foreach ($files as $file) {

            if (!str_contains($file, 'ns-ip-export-')) {
                continue;
            }

            Storage::disk('public')->delete($file);
        }
    }
}
