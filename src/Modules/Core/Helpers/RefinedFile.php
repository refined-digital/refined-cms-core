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

    public function link() {
        if ($this->file) {
            return $this->file->link->original;
        }

        return '';
    }
}
