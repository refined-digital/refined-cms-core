<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Str;
use Intervention\Image\ImageManagerStatic as Image;
use RefinedDigital\CMS\Modules\Media\Models\Media;

class RefinedImage {

    protected $file = null;
    private $path = '/storage/uploads/';

    protected $width = null;
    protected $height = null;
    protected $type = null;
    protected $force = null;
    protected $returnType = 'string'; // object | image | string

    protected $directory = '';
    protected $extension = '';
    protected $originalFileName = '';
    protected $attributes = [];


    public function __construct()
    {
        $this->directory = storage_path('app/public/uploads/');
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
            $this->originalFileName = str_replace('.'.$this->extension, '', $file->file);

            // add the alt text into the attributes
            if (isset($this->file->alt) && $this->file->alt) {
                $this->attributes['alt'] = $this->file->alt;
            }
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

    public function save($fileName = false)
    {
      try {
        // only process if we do have a file
        if (isset($this->file->id) && $this->file->type == 'Image') {
            $fileName = $this->buildFileName($fileName);

            // only create if we are forcing, or the file doesn't already exist
            if (!file_exists($this->directory.$fileName) || $this->force) {

                // load the image
                $image = Image::make($this->directory.$this->file->file);

                if ($this->type && $this->width && $this->height) {
                    if ($this->type == 'fit') {
                        $image->fit($this->width, $this->height);
                    } else if ($this->type == 'fill') {
                        $image->fit($this->width, $this->height, function($constrain){
                            $constrain->upsize();
                        });
                    }
                }
                else {
                    if($this->width && $this->height) {
                        $image->resize($this->width, $this->height, function($constrain){
                            $constrain->upsize();
                            $constrain->aspectRatio();
                        });
                    } elseif($this->width && !$this->height) {
                        $image->resize($this->width, null, function($constrain){
                            $constrain->upsize();
                            $constrain->aspectRatio();
                        });
                    } elseif(!$this->width && $this->height) {
                        $image->resize(null, $this->height, function($constrain){
                            $constrain->upsize();
                            $constrain->aspectRatio();
                        });
                    }
                }

                // now save it
                $image->save($this->directory.$fileName);
            }

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
        $this->returnType = 'image';
        return $this->save();
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




    private function buildFileName($fileName = false)
    {
        $name = [];

        // add the filename
        if($fileName) {
            $name[] = $fileName;
        } else {
            $name[] = $this->originalFileName;
        }

        // set the dimensions
        if ($this->width && $this->height) {
            $name[] = $this->width.'x'.$this->height;
        }

        // if only width, add it
        if ($this->width && !$this->height) {
            $name[] = 'w'.$this->width;
        }

        // if only height, add it
        if (!$this->width && $this->height) {
            $name[] = 'h'.$this->height;
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
        $name .= '.'.$this->extension;

        // return the file name
        return $name;
    }

}
