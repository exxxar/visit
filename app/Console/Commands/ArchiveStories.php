<?php

namespace App\Console\Commands;

use App\Enums\StoryStatus;
use App\Models\Story;
use Illuminate\Console\Command;

class ArchiveStories extends Command
{
    protected $signature = 'stories:archive';
    protected $description = 'Переносит просроченные истории в архив';

    public function handle(): int
    {
        $count = Story::where('status', StoryStatus::Approved)
            ->where('expires_at', '<', now())
            ->get()
            ->each(fn ($story) => $story->archive())
            ->count();

        $this->info("Перенесено в архив: {$count} историй");

        return self::SUCCESS;
    }
}
