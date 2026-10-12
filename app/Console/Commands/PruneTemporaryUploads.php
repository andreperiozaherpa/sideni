<?php

namespace App\Console\Commands;

use App\Models\TemporaryUpload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneTemporaryUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:prune {--hours=24 : Age in hours before a temporary upload is discarded}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete temporary uploads that were never attached to a report';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoff = now()->subHours((int) $this->option('hours'));
        $pruned = 0;

        TemporaryUpload::query()
            ->where('created_at', '<', $cutoff)
            ->chunkById(100, function ($uploads) use (&$pruned): void {
                foreach ($uploads as $upload) {
                    Storage::disk($upload->disk)->delete($upload->path);
                    $upload->delete();
                    $pruned++;
                }
            });

        $this->info("Pruned {$pruned} temporary upload(s).");

        return self::SUCCESS;
    }
}
