<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Encoders\FileExtensionEncoder;
use Intervention\Image\ImageManager;
use RefinedDigital\CMS\Modules\Media\Models\Media;

class RefinedImage
{
    protected $file = null;

    protected $width = null;

    protected $height = null;

    protected $type = null;

    protected $force = null;

    protected $returnType = 'string'; // object | image | string

    protected $quality = 90;

    protected $isLazy = true;

    protected $useNewFormat = true;

    protected $cacheSecondsHigh = 60 * 24 * 7;

    protected $cacheSecondsLow = 60 * 24;

    protected $extension = '';

    protected $originalExtension = '';

    protected $originalFileName = '';

    protected $originalFile = '';

    protected $directory = '';

    protected $attributes = [];

    protected $fetchPriority = null;

    /** storage path of the file the last createImage() call produced, for reading its real size */
    protected $lastGeneratedPath = null;

    protected $newTypes = ['webp', 'avif'];

    /**
     * Format generated for modern browsers. webp rather than avif because gd's
     * avif encoder is both slower and worse: on a 1640x1045 photo, webp took
     * 0.11s for 231KB against avif's 0.20s for 555KB. Revisit only if the driver
     * moves to imagick, which encodes avif properly.
     */
    protected $newType = 'webp';

    protected $dimensions = [];

    protected $disk = 'local';

    public function __construct()
    {
        $newFormat = config('pages.image.newFormat');
        if (! $newFormat) {
            $this->onlyUseOldFormat();
        }
    }

    public function load($file)
    {
        $this->disk = config('pages.image.disk');

        // go and get the file from the DB
        $file = Media::find($file);

        if (isset($file->id)) {
            $this->file = $file;
        }

        if (is_a($file, Media::class)) {
            $this->directory = $file->id;
            $this->originalFile = $file->getFileWithDirectory();

            $this->extension = $this->file->extension;
            $this->originalExtension = $this->extension;
            $this->originalFileName = str_replace('.'.$this->extension, '', $file->file);

            // photographs uploaded as png produce legacy fallbacks 10-20x
            // heavier than jpeg. when the upload provably has no transparency
            // the legacy derivatives are encoded as jpeg instead; modern
            // browsers get the webp source either way
            if ($this->extension === 'png' && $this->pngIsOpaque()) {
                $this->extension = 'jpg';
            }

            // add the alt text into the attributes
            if (isset($this->file->alt) && $this->file->alt) {
                $this->attributes['alt'] = $this->file->alt;
            }
        }

        return $this;
    }

    public function dimensions(array $dimensions)
    {
        if (is_array($dimensions)) {
            $this->dimensions = $dimensions;
        }

        return $this;
    }

    public function width(int $width)
    {
        if (is_numeric($width)) {
            $this->width = $width;
        }

        return $this;
    }

    public function height(int $height)
    {
        if (is_numeric($height)) {
            $this->height = $height;
        }

        return $this;
    }

    public function fill()
    {
        $this->type = 'fill';

        return $this;
    }

    public function fit()
    {
        $this->type = 'fit';

        return $this;
    }

    public function onlyUseOldFormat()
    {
        $this->useNewFormat = false;

        return $this;
    }

    public function format($format)
    {
        $this->extension = $format;

        return $this;
    }

    /**
     * Set the img fetchpriority hint. Pair with lazy(false) on an lcp image so
     * the browser both requests it immediately and ranks it above other
     * subresources.
     */
    public function fetchPriority($priority = 'high')
    {
        $this->fetchPriority = in_array($priority, ['high', 'low', 'auto']) ? $priority : null;

        return $this;
    }

    public function lazy($lazy = true)
    {
        $this->isLazy = $lazy;

        return $this;
    }

    public function quality($quality = 90)
    {
        $this->quality = (float) $quality;

        return $this;
    }

    public function forceNewFile($force = false)
    {
        if ($force) {
            $this->force = $force;
        }

        return $this;
    }

    public function returnType($type = 'string')
    {
        $this->returnType = $type;

        return $this;
    }

    public function attributes(array $attributes)
    {
        if (is_array($attributes) && count($attributes)) {
            $this->attributes = $attributes;
        }

        return $this;
    }

    /**
     * Real pixel size of a generated file, or null when it cannot be read.
     *
     * @return array{0: int, 1: int}|null
     */
    private function getImageSize(?string $storagePath): ?array
    {
        if (! $storagePath) {
            return null;
        }

        try {
            $size = @getimagesize(Storage::disk($this->disk)->path($storagePath));
        } catch (\Exception $error) {
            return null;
        }

        if (! $size || ! isset($size[0], $size[1]) || ! $size[0] || ! $size[1]) {
            return null;
        }

        return [(int) $size[0], (int) $size[1]];
    }

    public function createImage($width, $height, $fileName = false, $extension = false)
    {
        // cleared up front so a caller cannot read a stale path off an early return
        $this->lastGeneratedPath = null;

        if (! $this->file) {
            return null;
        }

        if ($this->isSVG()) {
            return $this->getOriginalImageUrl();
        }

        $width = (int) $width;
        $height = (int) $height;
        $targetExtension = $extension ?: $this->extension;
        $fileName = $this->buildFileName($fileName, $width, $height, $targetExtension);
        $fileNameAndDirectory = $this->file->getFileWithDirectory($fileName);
        $this->lastGeneratedPath = $fileNameAndDirectory;

        // the generated name collides with the upload's own when no dimensions were
        // asked for and the target format matches it. under force that would
        // re-encode the original in place, so hand back the untouched file instead
        if ($fileNameAndDirectory === $this->originalFile) {
            return $this->getOriginalImageUrl();
        }

        $fileExists = Storage::disk($this->disk)->exists($fileNameAndDirectory);

        // only create if we are forcing, or the file doesn't already exist
        if (! $fileExists || $this->force) {
            $fileContents = $this->getFileContents();

            // load the image
            $manager = new ImageManager(new Driver);
            $image = $manager->decodeBinary($fileContents);

            if ($this->type && $width && $height) {
                if ($this->type == 'fit') {
                    $image->cover(width: $width, height: $height);
                } elseif ($this->type == 'fill') {
                    $image->pad(width: $width, height: $height);
                }
            } else {
                if ($width && $height) {
                    $image->scaleDown(width: $width, height: $height);
                } elseif ($width && ! $height) {
                    $image->scaleDown(width: $width);
                } elseif (! $width && $height) {
                    $image->scaleDown(height: $height);
                }
            }

            // AutoEncoder picks its encoder from the *origin* media type, so it can
            // never convert: a file named .avif came out holding the original jpeg
            // bytes. Encode by the target extension when a format that takes a
            // quality argument is asked for; png/gif/bmp take none, so they stay
            // on AutoEncoder
            $encoder = in_array($targetExtension, [...$this->newTypes, 'jpg', 'jpeg'])
                ? new FileExtensionEncoder($targetExtension, quality: (int) $this->getQuality())
                : new AutoEncoder(quality: $this->getQuality());

            // now save it
            $encodedImage = $image->encode($encoder);
            Storage::disk($this->disk)->put($this->file->getFileWithDirectory($fileName), $encodedImage);
        }

        return Storage::disk($this->disk)->url($fileNameAndDirectory);
    }

    public function save($fileName = false)
    {
        if ($this->useNewFormat) {
            $this->format($this->newType);
        }

        if ($this->isSVG()) {
            return $this->getOriginalImageUrl();
        }

        try {
            // only process if we do have a file
            if (isset($this->file->id) && $this->file->type == 'Image') {
                $this->createImage($this->width, $this->height, $fileName);
                $fileName = $this->buildFileName($fileName, $this->width, $this->height);
                $fileNameAndDirectory = $this->file->getFileWithDirectory($fileName);

                $fileContents = Storage::disk($this->disk)->path($fileNameAndDirectory);

                // return the image
                $src = Storage::disk($this->disk)->url($fileNameAndDirectory);

                switch ($this->returnType) {
                    case 'image':
                    case 'img':
                        $img = '<img src="'.asset($src).'"';
                        if (count($this->attributes)) {
                            $attrs = '';
                            foreach ($this->attributes as $key => $value) {
                                $attrs .= ' '.$key.'="'.$value.'"';
                            }
                            $img .= $attrs;
                        }
                        $size = $this->getImageSize($fileNameAndDirectory);
                        if ($size) {
                            $img .= ' width="'.$size[0].'" height="'.$size[1].'"';
                        }
                        if ($this->isLazy) {
                            $img .= ' loading="lazy"';
                        }
                        if ($this->fetchPriority) {
                            $img .= ' fetchpriority="'.$this->fetchPriority.'"';
                        }
                        $img .= '/>';
                        break;
                    case 'object':
                        $dimensions = getimagesize($fileContents);

                        $img = new \stdClass;
                        $img->alt = $this->file->alt;
                        $img->width = $dimensions[0] ?? null;
                        $img->height = $dimensions[1] ?? null;
                        $img->attributes = $this->attributes;
                        $img->src = $src;
                        break;
                    case 'string':
                    default:
                        $img = $src;
                        break;
                }

                return $img;

            }

        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    public function get()
    {
        try {
            if ($this->isSVG()) {
                return $this->getFileContents();
            } else {
                $this->returnType = 'image';

                return $this->save();
            }
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    public function string()
    {
        if ($this->isSVG()) {
            return $this->getOriginalImageUrl();
        }

        $this->returnType = 'string';

        return $this->save();
    }

    public function object()
    {
        $this->returnType = 'object';

        return $this->save();
    }

    public function pictureHtml()
    {
        if (! $this->file) {
            return 'Failed to create image';
        }

        if ($this->isSVG()) {
            return $this->getFileContents();
        }

        try {
            // attributes describe the img, not the picture wrapper. alt was
            // landing on picture here, where it is not a valid attribute
            $html = '<picture>';

            $basePath = null;

            if (! count($this->dimensions)) {
                $image = $this->createImage($this->width, $this->height);
                $baseImage = $image;
                $basePath = $this->lastGeneratedPath;
                $html .= $this->buildModernSourceHtml($this->width, $this->height);
                $html .= $this->buildSourceHtml($image);
            } else {
                $width = 0;
                foreach ($this->dimensions as $dims) {
                    $image = $this->createImage($dims['width'], $dims['height']);
                    // captured before the modern source overwrites it
                    $generatedPath = $this->lastGeneratedPath;
                    $html .= $this->buildModernSourceHtml($dims['width'], $dims['height'], $dims['media'] ?? false);
                    $html .= $this->buildSourceHtml($image, $dims['media'] ?? false);
                    if ($dims['width'] > $width) {
                        $width = $dims['width'];
                        $baseImage = $image;
                        // captured here rather than after the loop: the widest
                        // dimension is not necessarily the last one generated
                        $basePath = $generatedPath;
                    }
                }
            }

            $attributes = [
                'src' => asset($baseImage),
            ];

            // read off the generated file rather than the requested size: without
            // fit() or fill() the resize scales down and the result is smaller
            // than asked for. the pair gives the browser an aspect ratio to
            // reserve space with, so the page does not shift as images arrive
            $size = $this->getImageSize($basePath);

            if ($size) {
                $attributes['width'] = $size[0];
                $attributes['height'] = $size[1];
            }

            if ($this->file->alt) {
                $attributes['alt'] = $this->file->alt;
            }

            if ($this->isLazy) {
                $attributes['loading'] = 'lazy';
            }

            if ($this->fetchPriority) {
                $attributes['fetchpriority'] = $this->fetchPriority;
            }

            // merged last so an explicit attributes() call wins. it replaces the
            // whole array, including the alt that load() puts there, which is why
            // alt is set from the file above rather than relied on here
            $attributes = array_merge($attributes, $this->attributes);

            $html .= PHP_EOL."\t".'<img '.core()->arrayToAttr($attributes).'/>';
            $html .= PHP_EOL.'</picture>';

            return $html;

        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    /**
     * A modern-format <source> emitted ahead of the legacy one, so a browser that
     * supports it never downloads the png/jpeg. Empty string when the original is
     * already a modern format, or when new formats are turned off.
     */
    private function buildModernSourceHtml($width, $height, $media = false)
    {
        if (! $this->useNewFormat || in_array($this->originalExtension, $this->newTypes)) {
            return '';
        }

        return $this->buildSourceHtml($this->createImage($width, $height, false, $this->newType), $media);
    }

    private function buildSourceHtml($image, $media = false)
    {
        $html = PHP_EOL."\t<source ";
        $attrs = [
            'srcset' => asset($image),
        ];

        // read off the generated file rather than mime_content_type: the type only
        // matters when it differs from the img fallback, and the extension is
        // already authoritative now that the encoder honours it
        $extension = strtolower(pathinfo(parse_url($image, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));

        if ($extension && $extension !== $this->originalExtension) {
            $attrs['type'] = 'image/'.($extension === 'jpg' ? 'jpeg' : $extension);
        }

        if (is_numeric($media) && $media > 200) {
            $attrs['media'] = '(min-width: '.$media.'px)';
        }

        $html .= core()->arrayToAttr($attrs);
        $html .= '></source>';

        return $html;
    }

    private function buildFileName($fileName = false, $width = false, $height = false, $extension = '')
    {
        $name = [];

        if (! $width) {
            $width = $this->width;
        }
        if (! $height) {
            $height = $this->height;
        }

        // add the filename
        if ($fileName) {
            $name[] = $fileName;
        } else {
            $name[] = $this->originalFileName;
        }

        // set the dimensions
        if ($width && $height) {
            $name[] = $width.'x'.$height;
        }

        // if only width, add it
        if ($width && ! $height) {
            $name[] = 'w'.$width;
        }

        // if only height, add it
        if (! $width && $height) {
            $name[] = 'h'.$height;
        }

        // only add the type if it is one of the valid types
        if ($this->type == 'fit') {
            $name[] = 'fit';
        }

        // only add the type if it is one of the valid types
        if ($this->type == 'fill') {
            $name[] = 'fill';
        }

        // create the file name
        $name = Str::slug(implode(' ', $name));

        // add the extension
        $ext = $extension ?: $this->extension;
        $name .= '.'.$ext;

        // return the file name
        return $name;
    }

    private function getQuality()
    {
        $quality = $this->quality;

        if (config('pages.image.quality')) {
            $quality = (float) config('pages.image.quality');
        }

        if ($quality > 100) {
            $quality = 100;
        }

        return $quality;
    }

    private function isSVG()
    {
        return $this->originalExtension === 'svg';
    }

    /**
     * Whether the loaded upload is a png with no transparency, read from the
     * file header rather than a decode. Cached because this runs on every
     * load() of the file.
     */
    private function pngIsOpaque(): bool
    {
        return \Cache::remember($this->getCacheKey('png-opaque-'.$this->directory), $this->cacheSecondsHigh, function () {
            $disk = Storage::disk($this->disk);

            if (! $disk->exists($this->originalFile)) {
                return false;
            }

            $stream = $disk->readStream($this->originalFile);

            if (! $stream) {
                return false;
            }

            // 64kb comfortably covers everything before the pixel data, which
            // is where a tRNS transparency chunk must appear
            $bytes = fread($stream, 65536) ?: '';
            fclose($stream);

            return $bytes !== '' && ! static::pngBytesHaveTransparency($bytes);
        });
    }

    /**
     * Whether png header bytes declare transparency: an alpha channel in the
     * IHDR colour type, or a tRNS chunk on palette/truecolour images. Anything
     * unparseable claims transparency so the caller leaves the file alone.
     *
     * ponytail: an rgba png whose pixels are all opaque still reports
     * transparent — detecting that needs a pixel scan, add one if it matters
     */
    public static function pngBytesHaveTransparency(string $bytes): bool
    {
        if (! str_starts_with($bytes, "\x89PNG\r\n\x1a\n") || strlen($bytes) < 26) {
            return true;
        }

        $colourType = ord($bytes[25]);

        // 4 = greyscale+alpha, 6 = truecolour+alpha
        if ($colourType === 4 || $colourType === 6) {
            return true;
        }

        // a tRNS chunk is only valid before the pixel data
        $idat = strpos($bytes, 'IDAT');
        $header = $idat === false ? $bytes : substr($bytes, 0, $idat);

        return str_contains($header, 'tRNS');
    }

    private function getFileContents()
    {
        return Storage::disk($this->disk)->get($this->originalFile);
    }

    private function getOriginalImageUrl()
    {
        return Storage::disk($this->disk)->url($this->originalFile);
    }

    private function getCacheKey($name = '')
    {
        if (! $name) {
            $name = $this->originalFileName;
        }

        return Str::slug($this->disk.'-'.$name);
    }
}
