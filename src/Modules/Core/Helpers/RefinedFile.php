<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use RefinedDigital\CMS\Modules\Media\Models\Media;

class RefinedFile {

    protected $file;

    public function load($fileId)
    {
        $this->file = Media::find($fileId);
        return $this;
    }

    public function link()
    {
        if ($this->file) {
            return $this->file->link->original;
        }

        return null;
    }

    public function url()
    {
        return $this->link();
    }

    public function path()
    {
        if ($this->file) {
            $url = $this->file->link->original;
            $base = $this->file->link->basePath;
            $path = str_replace($base, '', $url);
            return public_path($path);
        }

        return null;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getData()
    {
        return file_get_contents($this->path());
    }
}
