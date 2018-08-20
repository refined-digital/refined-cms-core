<?php

namespace RefinedDigital\CMS\Modules\Media\Models;

use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use RefinedDigital\CMS\Modules\Media\Traits\SortableMediaTrait;
use Spatie\EloquentSortable\Sortable;
use File;

class Media extends CoreModel implements Sortable
{
    use SortableMediaTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'active', 'position', 'media_category_id', 'name', 'file', 'alt', 'description', 'mime'
    ];

    protected $appends = [
        'link', 'type', 'size'
    ];

    protected $table = 'media';

    public function getLinkAttribute()
    {
        $publicDir = '/storage/uploads/'.$this->id.'/';
        $thumb = $this->file;

        if ($this->type == 'Image') {
            // generate the thumbnail
            image()->load($this)->width(500)->save();
        }

        $link = new \stdClass();
        $link->thumb = asset($publicDir.$thumb);
        $link->original = asset($publicDir.$this->file);
        $link->basePath = pages()->getBaseHref();

        return $link;
    }

    public function getTypeAttribute()
    {
        return is_numeric(strpos($this->mime, 'image/')) ? 'Image' : 'File';
    }

    public function getSizeAttribute()
    {
        $file = storage_path('app/public/uploads/'.$this->id.'/'.$this->file);
        return help()->formatBytes(File::size($file));
    }
}
