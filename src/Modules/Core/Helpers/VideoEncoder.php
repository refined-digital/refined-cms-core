<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RefinedDigital\CMS\Modules\Media\Models\Media;

class VideoEncoder
{
    /** null until the binary check has run for this instance */
    private ?bool $available = null;

    /**
     * Whether both binaries respond. proc_open rather than shelling out to
     * `which`, which is absent on some minimal images.
     */
    public function isAvailable(): bool
    {
        if ($this->available !== null) {
            return $this->available;
        }

        $this->available = $this->run([$this->binary('ffmpeg'), '-version']) !== null
            && $this->run([$this->binary('ffprobe'), '-version']) !== null;

        return $this->available;
    }

    /**
     * Dimensions and bitrate of the first video stream, or null when the file
     * cannot be read as video at all.
     *
     * @return array{width: int, height: int, bitRate: int}|null
     */
    public function probe(string $absolutePath): ?array
    {
        $output = $this->run([
            $this->binary('ffprobe'), '-v', 'error',
            '-select_streams', 'v:0',
            '-show_entries', 'stream=width,height',
            '-show_entries', 'format=bit_rate',
            '-of', 'json', $absolutePath,
        ]);

        if (! $output) {
            return null;
        }

        $data = json_decode($output, true);
        $stream = $data['streams'][0] ?? null;

        if (! isset($stream['width'], $stream['height'])) {
            return null;
        }

        return [
            'width' => (int) $stream['width'],
            'height' => (int) $stream['height'],
            'bitRate' => (int) ($data['format']['bit_rate'] ?? 0),
        ];
    }

    private function binary(string $name): string
    {
        return config('pages.video.'.$name, $name);
    }

    /**
     * Runs a command with no shell, so filter arguments need no escaping.
     * Returns stdout on success, null on any non-zero exit.
     *
     * stderr is routed to a temp file rather than a pipe. Reading stdout to
     * completion before touching stderr deadlocks if the child fills the
     * stderr pipe buffer while blocked writing to it and we are blocked
     * reading stdout — ffmpeg can write enough to stderr to hit that. A
     * temp-file descriptor makes the deadlock structurally impossible.
     *
     * stdin is closed to /dev/null rather than inherited: ffmpeg reads stdin
     * for interactive y/n prompts (e.g. "overwrite? [y/N]" on a race with -y),
     * and an inherited stdin under php-fpm can leave the child waiting on
     * input that will never arrive.
     */
    private function run(array $command): ?string
    {
        $errorFile = tempnam(sys_get_temp_dir(), 'video-encoder-');

        if ($errorFile === false) {
            return null;
        }

        $process = @proc_open(
            $command,
            [
                0 => ['file', '/dev/null', 'r'],
                1 => ['pipe', 'w'],
                2 => ['file', $errorFile, 'w'],
            ],
            $pipes
        );

        if (! is_resource($process)) {
            unlink($errorFile);

            return null;
        }

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $exitCode = proc_close($process);
        $stderr = file_get_contents($errorFile);
        unlink($errorFile);

        if ($exitCode !== 0) {
            Log::warning('VideoEncoder command failed: '.implode(' ', $command).' - '.trim((string) $stderr));

            return null;
        }

        return $stdout;
    }

    /**
     * Encodes to h264 at the configured quality, scaled down to maxWidth when
     * wider. Audio is dropped: these are muted background reels and the stream
     * is dead weight. faststart puts the moov atom first so playback can begin
     * before the download finishes.
     */
    private function encode(string $source, string $destination): bool
    {
        $maxWidth = (int) config('pages.video.maxWidth', 1920);

        return $this->run([
            $this->binary('ffmpeg'), '-y', '-v', 'error',
            '-i', $source,
            '-c:v', 'libx264',
            '-profile:v', 'high',
            '-crf', (string) config('pages.video.crf', 32),
            '-preset', (string) config('pages.video.preset', 'slow'),
            // -2 keeps the height even, which h264 requires. the quotes are
            // parsed by ffmpeg, not a shell, and protect the comma
            '-vf', "scale='min(".$maxWidth.",iw)':-2",
            '-g', '50',
            '-pix_fmt', 'yuv420p',
            '-an',
            '-movflags', '+faststart',
            $destination,
        ]) !== null;
    }

    /**
     * First frame, straight to webp. Frame 0 rather than a seek so the poster
     * matches the start of the loop and nothing jumps when playback begins.
     */
    private function extractPoster(string $source, string $destination): bool
    {
        $maxWidth = (int) config('pages.video.maxWidth', 1920);

        return $this->run([
            $this->binary('ffmpeg'), '-y', '-v', 'error',
            '-i', $source,
            '-frames:v', '1',
            '-update', '1',
            '-vf', "scale='min(".$maxWidth.",iw)':-2",
            '-c:v', 'libwebp',
            '-quality', (string) config('pages.video.posterQuality', 80),
            $destination,
        ]) !== null;
    }

    /**
     * Generates the derivatives for an uploaded video. Every failure path leaves
     * the original untouched and usable — a video upload must never fail because
     * encoding did, and this runs inside uploadFile()'s try block, which
     * force-deletes the record on an exception.
     */
    public function process(Media $media, bool $force = false): array
    {
        $result = [
            'status' => 'failed',
            'originalBytes' => 0,
            'encodedBytes' => null,
            'posterBytes' => null,
        ];

        try {
            // config check first: must never probe for the binaries (which
            // spawns ffmpeg -version / ffprobe -version) when encoding is
            // switched off altogether
            if (! config('pages.video.encode', true)) {
                return ['status' => 'unavailable'] + $result;
            }

            if (! $this->isAvailable()) {
                Log::warning('Video encoding skipped, ffmpeg or ffprobe not found on this server.');

                return ['status' => 'unavailable'] + $result;
            }

            $disk = Storage::disk(config('pages.video.disk', config('pages.image.disk')));
            $original = $media->getFileWithDirectory();

            if (! $disk->exists($original)) {
                return ['status' => 'missing'] + $result;
            }

            $result['originalBytes'] = $disk->size($original);

            $sourcePath = $disk->path($original);
            $details = $this->probe($sourcePath);

            if (! $details) {
                return ['status' => 'missing'] + $result;
            }

            $result['posterBytes'] = $this->makePoster($disk, $sourcePath, $original, $force);

            $encoded = $this->makeEncoded($disk, $sourcePath, $original, $details, $force);

            if ($encoded === false) {
                // ffmpeg exited non-zero or produced a rejected output. distinct
                // from a deliberate skip so the reprocess command can surface a
                // broken encode instead of reporting it as "already lean"
                $result['status'] = 'failed';
            } elseif ($encoded === null) {
                $result['status'] = 'skipped';
            } else {
                $result['encodedBytes'] = $encoded;
                $result['status'] = 'encoded';
            }

            return $result;
        } catch (\Throwable $error) {
            Log::warning('Video processing failed: '.$error->getMessage());

            return $result;
        }
    }

    /** @return int|null bytes of the poster, or null when none was produced */
    private function makePoster($disk, string $sourcePath, string $original, bool $force): ?int
    {
        if (! config('pages.video.poster', true)) {
            return null;
        }

        $poster = static::posterName($original);

        if ($disk->exists($poster) && ! $force) {
            return $disk->size($poster);
        }

        // extract beside the final path, not onto it, for the same reason as
        // makeEncoded(): a force run must not truncate a working poster in
        // place, and a mid-extract kill must not leave a broken file sitting
        // at the path the never-regenerate guard treats as finished, forever.
        // the name is deterministic (not tempnam()) so a crash-orphaned temp
        // is overwritten by the next run instead of accumulating. the real
        // extension stays trailing so ffmpeg's muxer auto-detection still
        // works — see makeEncoded(), where "...-web.mp4.tmp" broke that and
        // "...-web.tmp.mp4" fixed it. suffixed() never inserts ".tmp." before
        // the extension, so this can never collide with posterName()'s output
        $destinationPath = $disk->path($poster);
        $temporary = pathinfo($destinationPath, PATHINFO_DIRNAME).'/'
            .pathinfo($destinationPath, PATHINFO_FILENAME)
            .'.tmp.'.pathinfo($destinationPath, PATHINFO_EXTENSION);

        if (! $this->extractPoster($sourcePath, $temporary)) {
            @unlink($temporary);

            return null;
        }

        // a zero-byte or missing poster must never be promoted onto the live path
        if (! @filesize($temporary)) {
            @unlink($temporary);

            return null;
        }

        rename($temporary, $destinationPath);

        return $disk->size($poster);
    }

    /**
     * @return int|false|null bytes of the encoded file on success, null when
     *                        skipped because the source is already lean, or
     *                        false when the encode failed
     */
    private function makeEncoded($disk, string $sourcePath, string $original, array $details, bool $force): int|false|null
    {
        $encoded = static::encodedName($original);

        // never regenerate unless asked to. RefinedImage's buildNewSourceHtml()
        // forced a rebuild on every page load and that was expensive for an
        // image; for video it would be ruinous. only the reprocess command
        // passes force
        if ($disk->exists($encoded) && ! $force) {
            return $disk->size($encoded);
        }

        // already lean enough that re-encoding would only lose a generation.
        // honoured even under force, so reprocessing is safe to re-run
        if ($details['bitRate'] > 0
            && $details['bitRate'] <= (int) config('pages.video.skipUnder', 1500000)
            && $details['width'] <= (int) config('pages.video.maxWidth', 1920)) {
            return null;
        }

        set_time_limit(0);

        // encode beside the final path, not onto it. ffmpeg's -y would
        // otherwise truncate a live, working derivative in place, and a
        // mid-encode kill (php-fpm's request_terminate_timeout ignores
        // set_time_limit) would leave that truncated file sitting at the
        // path the never-regenerate guard treats as finished, forever.
        // the extension must stay last: ffmpeg without -f picks its muxer
        // from the output filename's suffix, so "...-web.mp4.tmp" fails to
        // open with "Unable to choose an output format"
        $destinationPath = $disk->path($encoded);
        $temporary = pathinfo($destinationPath, PATHINFO_DIRNAME).'/'
            .pathinfo($destinationPath, PATHINFO_FILENAME)
            .'.tmp.'.pathinfo($destinationPath, PATHINFO_EXTENSION);

        if (! $this->encode($sourcePath, $temporary)) {
            @unlink($temporary);

            return false;
        }

        $temporarySize = @filesize($temporary);

        // crf encoding an already-small file can grow it; a missing or
        // zero-byte output must never be promoted onto the live path
        if (! $temporarySize || $temporarySize >= $disk->size($original)) {
            @unlink($temporary);

            return false;
        }

        rename($temporary, $disk->path($encoded));

        return $disk->size($encoded);
    }

    /**
     * Name of the encoded derivative for an upload, beside the original.
     */
    public static function encodedName(string $file): string
    {
        return static::suffixed($file, '-web', 'mp4');
    }

    /**
     * Name of the poster derivative for an upload, beside the original.
     */
    public static function posterName(string $file): string
    {
        return static::suffixed($file, '-poster', 'webp');
    }

    /**
     * Appends a suffix and swaps the extension, preserving any directory prefix
     * and any dots inside the name itself.
     */
    private static function suffixed(string $file, string $suffix, string $extension): string
    {
        $name = pathinfo($file, PATHINFO_FILENAME).$suffix.'.'.$extension;
        $directory = pathinfo($file, PATHINFO_DIRNAME);

        return $directory === '.' || $directory === '' ? $name : $directory.'/'.$name;
    }
}
