<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use RefinedDigital\CMS\Modules\Media\Models\Media;
use Str;

class RefinedImage {

    protected $file = null;
    private $path = '/storage/uploads/';

    protected $width = null;
    protected $height = null;
    protected $type = null;
    protected $force = null;
    protected $returnType = 'string'; // object | image | string
    protected $quality = 90;
    protected $isLazy = true;
    protected $useNewFormat = true;

    protected $directory = '';
    protected $extension = '';
    protected $originalExtension = '';
    protected $originalFileName = '';
    protected $attributes = [];
    protected $newTypes = ['webp', 'avif'];

    protected $dimensions = [];


    public function __construct()
    {
        $this->directory = storage_path('app/public/uploads/');
        if (help()->isMultiTenancy()) {
            $this->directory = storage_path('uploads/');
        }

        $newFormat = config('pages.image.newFormat');
        if (!$newFormat) {
            $this->onlyUseOldFormat();
        }
    }

    public function load($file)
    {
        if (is_numeric($file)) {

            // go and get the file from the DB
            $file = Media::find($file);
            if (isset($file->id)) {
                $this->file = $file;
            }

        } else if (class_basename($file) == 'Media') {
            $this->file = $file;
        }

        if (class_basename($file) == 'Media') {
            $this->directory .= $file->id.'/';
            $this->path .= $file->id.'/';
            $this->extension = pathinfo($this->directory.$file->file, PATHINFO_EXTENSION);
            $this->originalExtension = pathinfo($this->directory.$file->file, PATHINFO_EXTENSION);
            $this->originalFileName = str_replace('.'.$this->extension, '', $file->file);

            // add the alt text into the attributes
            if (isset($this->file->alt) && $this->file->alt) {
                $this->attributes['alt'] = $this->file->alt;
            }
        }


        return $this;
    }

    public function dimensions(array $dimensions) {
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
        if (is_array($attributes) && sizeof($attributes)) {
            $this->attributes = $attributes;
        }

        return $this;
    }

    public function createImage($width, $height, $fileName = false, $extension = false)
    {
        if (!$this->file) {
            return null;
        }

        $width = (int) $width;
        $height = (int) $height;
        $fileName = $this->buildFileName($fileName, $width, $height, $extension);

        // only create if we are forcing, or the file doesn't already exist
        if(!file_exists($this->directory.$fileName) || $this->force) {
            // load the image
            $manager = new ImageManager(new Driver());
            $image = $manager->read($this->directory.$this->file->file);

            if($this->type && $width && $height) {
                if($this->type == 'fit') {
                    $image->cover(width: $width, height: $height);
                } elseif($this->type == 'fill') {
                    $image->pad(width: $width, height: $height);
                }
            } else {
                if($width && $height) {
                    $image->scaleDown(width: $width, height: $height);
                } elseif($width && !$height) {
                    $image->scaleDown(width: $width);
                } elseif(!$width && $height) {
                    $image->scaleDown(height: $height);
                }
            }

            // now save it
            $ext = $extension ?? $this->extension;
            $image->save($this->directory.$fileName, $this->getQuality(), $ext);
        }

        return $this->path.$fileName;
    }

    public function save($fileName = false)
    {
        if ($this->useNewFormat) {
            $this->format('avif');
        }

      try {
        // only process if we do have a file
        if (isset($this->file->id) && $this->file->type == 'Image') {
            $this->createImage($this->width, $this->height, $fileName);
            $fileName = $this->buildFileName($fileName, $this->width, $this->height);

            // return the image
            $dimensions = getimagesize($this->directory.$fileName);

            switch($this->returnType) {
                case 'image':
                case 'img':
                    $img = '<img src="'.asset($this->path.$fileName).'"';
                        if (sizeof($this->attributes)) {
                            $attrs = '';
                            foreach ($this->attributes as $key => $value) {
                                $attrs = ' '.$key.'="'.$value.'"';
                            }
                            $img .= $attrs;
                        }
                        if ($this->isLazy) {
                            $img .= ' loading="lazy"';
                        }
                    $img .= '/>';
                    break;
                case 'object':
                    $img = new \stdClass();
                    $img->alt = $this->file->alt;
                    $img->width = isset($dimensions[0]) ? $dimensions[0] : null;
                    $img->height = isset($dimensions[1]) ? $dimensions[1] : null;
                    $img->attributes = $this->attributes;
                    $img->src = $this->path.$fileName;
                    break;
                case 'string':
                default:
                    $img = $this->path.$fileName;
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
            $filePath = $this->directory.$this->file->file;
            if (strpos($this->file->file, '.svg') && file_exists($filePath)) {
                return file_get_contents($filePath);
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
        if (!$this->file) {
            return 'Failed to create image';
        }

        try {
            $html = '<picture>';
            if (!sizeof($this->dimensions)) {
                $image = $this->createImage($this->width, $this->height);
                $baseImage = $image;
                $html .= $this->buildSourceHtml($image);
                $html .= $this->buildNewSourceHtml();
            } else {
                $width = 0;
                foreach ($this->dimensions as $dims) {
                    $image = $this->createImage($dims['width'], $dims['height']);
                    $html .= $this->buildSourceHtml($image, $dims['media'] ?? false);
                    if (in_array($this->extension, $this->newTypes)) {
                        $html .= $this->buildNewSourceHtml();
                    }
                    if ($dims['width'] > $width) {
                        $width = $dims['width'];
                        $baseImage = $image;
                    }
                }
            }

            $html .= PHP_EOL."\t".'<img src="'.asset($baseImage).'"'.($this->isLazy ? ' loading="lazy"' : '').'/>';
            $html .= PHP_EOL.'</picture>';

            return $html;

        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

    private function buildNewSourceHtml()
    {
        $this->force = true;
        $image = $this->createImage($this->width, $this->height, false, $this->originalExtension);
        $this->force = false;
        return $this->buildSourceHtml($image);
    }

    private function buildSourceHtml($image, $media = false)
    {
        $html = PHP_EOL."\t<source ";
        $attrs = [
            'srcset' => asset($image),
        ];

        if ($this->extension && $this->originalExtension && $this->extension !== $this->originalExtension) {
            $localPath = storage_path(str_replace('/storage', 'app/public', $image));
            $attrs['type'] = mime_content_type($localPath);
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

        if (!$width) {
            $width = $this->width;
        }
        if (!$height) {
            $height = $this->height;
        }

        // add the filename
        if($fileName) {
            $name[] = $fileName;
        } else {
            $name[] = $this->originalFileName;
        }

        // set the dimensions
        if ($width && $height) {
            $name[] = $width.'x'.$height;
        }

        // if only width, add it
        if ($width && !$height) {
            $name[] = 'w'.$width;
        }

        // if only height, add it
        if (!$width && $height) {
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

}
