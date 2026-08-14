<?php

namespace RefinedDigital\CMS\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use RefinedDigital\CMS\Modules\Core\Helpers\VideoEncoder;
use RefinedDigital\CMS\Modules\Media\Models\Media;

class ReprocessVideos extends Command
{
    protected $signature = 'refinedCMS:reprocess-videos
                            {id? : A single media id. Omit to process every video}';

    protected $description = 'Re-encode uploaded videos and rebuild their posters';

    public function handle(VideoEncoder $encoder): int
    {
        if (! $encoder->isAvailable()) {
            $this->error('ffmpeg and ffprobe were not found. Install ffmpeg on this server first.');

            return self::FAILURE;
        }

        $videos = $this->videos();

        if (! $videos->count()) {
            $this->warn('No videos found.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Reprocessing '.$videos->count().' video'.($videos->count() === 1 ? '' : 's'));
        $this->newLine();

        $originalTotal = 0;
        $encodedTotal = 0;

        foreach ($videos as $video) {
            // printed before the encode, not after: a long encode with no output
            // looks like the command has hung
            $this->line('  <fg=cyan>#'.$video->id.'</>  '.$video->file);

            $result = $encoder->process($video, true);

            $originalTotal += $result['originalBytes'];
            // a skipped video still ships its original, so it weighs its own size
            $encodedTotal += $result['encodedBytes'] ?? $result['originalBytes'];

            $this->line('       '.$this->describe($result));
            $this->newLine();
        }

        $saved = max($originalTotal - $encodedTotal, 0);

        $this->info(sprintf(
            '%d video%s processed, %s -> %s, saved %s (%s)',
            $videos->count(),
            $videos->count() === 1 ? '' : 's',
            $this->bytes($originalTotal),
            $this->bytes($encodedTotal),
            $this->bytes($saved),
            $this->saving($originalTotal, $encodedTotal)
        ));

        return self::SUCCESS;
    }

    /**
     * Filters on the mime column, not the type accessor. `type` and `extension`
     * are accessors on Media, not columns, so a where() on either throws
     * "Unknown column" at the database.
     */
    private function videos(): Collection
    {
        $query = Media::query()->where('mime', 'like', 'video/%');

        if ($id = $this->argument('id')) {
            $query->where('id', $id);
        }

        return $query->orderBy('id')->get();
    }

    /**
     * @param  array{status: string, originalBytes: int, encodedBytes: ?int, posterBytes: ?int}  $result
     */
    private function describe(array $result): string
    {
        return match ($result['status']) {
            'encoded' => sprintf(
                '<fg=green>%s -> %s</>  (%s)%s',
                $this->bytes($result['originalBytes']),
                $this->bytes($result['encodedBytes']),
                $this->saving($result['originalBytes'], $result['encodedBytes']),
                $result['posterBytes'] ? '   poster '.$this->bytes($result['posterBytes']) : ''
            ),
            'skipped' => '<fg=yellow>skipped, already within crf and maxWidth targets</>',
            'missing' => '<fg=red>skipped, file missing or unreadable</>',
            'unavailable' => '<fg=red>skipped, ffmpeg unavailable or encoding disabled</>',
            default => '<fg=red>failed, see the log</>',
        };
    }

    private function bytes(int $bytes): string
    {
        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1).' MB'
            : number_format($bytes / 1024).' KB';
    }

    private function saving(int $from, int $to): string
    {
        return $from > 0 ? '-'.round((1 - $to / $from) * 100).'%' : 'n/a';
    }
}
