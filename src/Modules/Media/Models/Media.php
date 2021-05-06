<?php

namespace RefinedDigital\CMS\Modules\Media\Models;

use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use RefinedDigital\CMS\Modules\Media\Traits\SortableMediaTrait;
use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;
use File;

class Media extends CoreModel implements Sortable {
    use SortableMediaTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'active',
        'position',
        'media_category_id',
        'name',
        'file',
        'alt',
        'description',
        'mime'
    ];

    protected $appends = [
        'link',
        'extension',
        'type',
        'size',
    ];

    protected $casts = [
      'id' => 'integer',
      'active' => 'integer',
      'position' => 'integer',
      'media_category_id' => 'integer',
    ];

    protected $videoTypes = [
        'mp4'
    ];

    protected $with = [
        'altTexts'
    ];

    protected $table = 'media';

    public function getLinkAttribute() {
        $publicDir = $this->getPublicDir($this->id);
        $thumb     = $this->file;

        if ($this->type == 'Image') {
            // generate the thumbnail
            image()->load($this)->width(500)->save();
        }

        $link           = new \stdClass();
        $link->thumb    = asset($publicDir . $thumb);
        $link->original = asset($publicDir . $this->file);
        $link->basePath = pages()->getBaseHref();

        return $link;
    }

    public function getTypeAttribute() {
        $extension = $this->extension;
        $type      = is_numeric(strpos($this->mime, 'image/')) ? 'Image' : 'File';
        if (in_array($extension, $this->videoTypes)) {
            $type = 'Video';
        }

        return $type;
    }

    public function getSizeAttribute() {
        $path = $this->getBasePath();
        if (!file_exists($path)) {
          return null;
        }

        return help()->formatBytes(File::size($path));
    }

    public function getExtensionAttribute() {
        return $this->getFileExtension();
    }

    private function getFileExtension() {
        $path = $this->getBasePath();

        return pathinfo($path, PATHINFO_EXTENSION);
    }

    private function getPublicDir() {
        return 'storage/uploads/' . $this->id . '/';
    }

    private function getBasePath() {
        return storage_path('app/public/uploads/' . $this->id . '/' . $this->file);
    }

    public function altTexts()
    {
        return $this->hasMany('RefinedDigital\CMS\Modules\Media\Models\MediaAltText');
    }
}
