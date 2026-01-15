<?php

namespace RefinedDigital\CMS\Modules\Media\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use RefinedDigital\CMS\Modules\Media\Traits\SortableMediaTrait;
use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;
use File;

class Media extends CoreModel implements Sortable {
    use SortableMediaTrait, SoftDeletes;

    protected $cacheSecondsHigh = 60 * 24 * 7;
    protected $cacheSecondsLow = 60 * 24;

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
        'mime',
        'external_id',
        'external_url'
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

    protected $table = 'media';

    public function getLinkAttribute() {
        $link           = new \stdClass();
        $link->thumb    = $this->type === 'Image'
            ? image()->load($this->id)->width(500)->string()
            : null
        ;
        $link->original = $this->getFileUrl();
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
        $exists = $this->exists();

        if (!$this->exists()) {
            return null;
        }

        try {
            return Cache::flexible(
                'media-file-'.$this->id.'-file-size',
                [$this->cacheSecondsLow, $this->cacheSecondsHigh],
                fn () => help()->formatBytes(File::size($this->getFilePath())))
                ;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getExtensionAttribute() {
        return $this->getFileExtension();
    }

    private function getFilePath()
    {
        return Cache::flexible(
            'media-file-'.$this->id.'-path',
            [$this->cacheSecondsLow, $this->cacheSecondsHigh],
            fn () => Storage::disk($this->getDisk())->path($this->getFileWithDirectory()))
            ;
    }

    private function getFileExtension()
    {
        return Cache::flexible(
            'media-file-'.$this->id.'-extension',
            [$this->cacheSecondsLow, $this->cacheSecondsHigh],
            fn () => pathinfo($this->getFilePath(), PATHINFO_EXTENSION))
            ;
    }

    private function getFileUrl()
    {
        return Cache::flexible(
            'media-file-'.$this->id.'-url',
            [$this->cacheSecondsLow, $this->cacheSecondsHigh],
            fn () => Storage::disk($this->getDisk())->url($this->file))
            ;
    }

    private function getDisk()
    {
        return config('pages.image.disk');
    }

    public function getFileWithDirectory(string $name = '')
    {
        if (!$name) {
            $name = $this->file;
        }

        return $this->id . DIRECTORY_SEPARATOR . $name;
    }

    private function exists()
    {
        return Cache::flexible(
            'media-file-'.$this->id.'-exitst',
            [$this->cacheSecondsLow, $this->cacheSecondsHigh],
            fn () => Storage::disk($this->disk)->exists($this->getFileWithDirectory()))
            ;
    }
}
