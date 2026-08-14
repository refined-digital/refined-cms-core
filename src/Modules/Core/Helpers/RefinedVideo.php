<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Support\Facades\Storage;
use RefinedDigital\CMS\Modules\Media\Models\Media;

class RefinedVideo
{
    protected $file = null;

    protected $class = 'banner__video';

    protected $disk = 'public';

    public function load($file)
    {
        $this->disk = config('pages.video.disk', config('pages.image.disk'));

        $file = Media::find($file);

        if (is_a($file, Media::class)) {
            $this->file = $file;
        }

        return $this;
    }

    public function class(string $class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * The encoded derivative when one exists, otherwise the untouched upload, so
     * a server without ffmpeg still serves video.
     */
    public function url(): ?string
    {
        if (! $this->file) {
            return null;
        }

        $original = $this->file->getFileWithDirectory();
        $encoded = VideoEncoder::encodedName($original);
        $disk = Storage::disk($this->disk);

        return asset($disk->url($disk->exists($encoded) ? $encoded : $original));
    }

    public function poster(): ?string
    {
        if (! $this->file) {
            return null;
        }

        $poster = VideoEncoder::posterName($this->file->getFileWithDirectory());
        $disk = Storage::disk($this->disk);

        return $disk->exists($poster) ? asset($disk->url($poster)) : null;
    }

    public function banner(): string
    {
        $src = $this->url();

        return $src ? static::videoHtml($src, $this->poster(), $this->class) : '';
    }

    /**
     * The background reel element. Static and argument-driven so it can be
     * exercised without booting the framework; callers resolve the urls.
     */
    public static function videoHtml(string $src, ?string $poster, string $class): string
    {
        $attributes = [
            'class="'.htmlspecialchars($class, ENT_QUOTES, 'UTF-8').'"',
            'autoplay',
            'muted',
            'loop',
            'playsinline',
            'webkit-playsinline',
            'x-webkit-airplay="deny"',
            'preload="metadata"',
            'disablePictureInPicture',
        ];

        // omitted rather than emitted empty: an empty poster makes some browsers
        // request the current page and use the response as the image
        if ($poster) {
            $attributes[] = 'poster="'.htmlspecialchars($poster, ENT_QUOTES, 'UTF-8').'"';
        }

        return '<video '.implode(' ', $attributes).'>'
            .PHP_EOL."\t".'<source src="'.htmlspecialchars($src, ENT_QUOTES, 'UTF-8').'" type="video/mp4">'
            .PHP_EOL.'</video>';
    }
}
