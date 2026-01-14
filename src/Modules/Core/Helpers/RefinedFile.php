<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Support\Facades\Storage;
use RefinedDigital\CMS\Modules\Media\Models\Media;

class RefinedFile {

    protected $file;
    protected $disk = 'local';
    protected $originalFile = null;

    public function load($fileId)
    {
        $this->disk = config('pages.image.disk');

        $this->file = Media::find($fileId);

        $this->directory = $this->file->id;

        $this->originalFile = $this->getFileWithDirectory($this->file->file);

        return $this;
    }

    public function link()
    {
        if ($this->file) {
            return Storage::disk($this->disk)->url($this->originalFile);
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
            return Storage::disk($this->disk)->path($this->originalFile);
        }

        return null;
    }

    public function storagePath()
    {
        if ($this->file) {
            return Storage::disk($this->disk)->path($this->originalFile);
        }

        return null;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getData()
    {
        return Storage::disk($this->disk)->get($this->originalFile);
    }

    private function getFileWithDirectory(string $file)
    {
        return $this->directory . DIRECTORY_SEPARATOR . $file;
    }
}
