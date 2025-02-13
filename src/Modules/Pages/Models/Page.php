<?php

namespace RefinedDigital\CMS\Modules\Pages\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use RefinedDigital\CMS\Modules\Core\Traits\ClearResponseCacheTrait;
use RefinedDigital\CMS\Modules\Core\Traits\HasSettings;
use RefinedDigital\CMS\Modules\Pages\Traits\ContentBySource;
use RefinedDigital\CMS\Modules\Pages\Traits\HasContentBlocks;
use RefinedDigital\CMS\Modules\Pages\Traits\IsPage;
use RefinedDigital\CMS\Modules\Pages\Traits\SortablePageTrait;
use RefinedDigital\CMS\Modules\Core\Enums\PageContentType;
use Spatie\EloquentSortable\Sortable;

class Page extends CoreModel implements Sortable
{
    use SoftDeletes;
    use IsPage;
    use SortablePageTrait;
    use ClearResponseCacheTrait;
    use ContentBySource;
    use HasSettings;
    use HasContentBlocks;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'page_holder_id',
        'parent_id',
        'active',
        'hide_from_menu',
        'hide_from_sitemap',
        'protected',
        'page_type',
        'form_id',
        'position',
        'name',
        'banner',
        'data',
        'content',
        'settings',
    ];

    protected $appends = [ 'banner_image' ];

    protected $casts = [
        'id' => 'integer',
        'active' => 'integer',
        'position' => 'integer',
        'page_holder_id' => 'integer',
        'parent_id' => 'integer',
        'hide_from_menu' => 'integer',
        'hide_from_sitemap' => 'integer',
        'protected' => 'integer',
        'page_type' => 'integer',
        'form_id' => 'integer',
        'banner' => 'integer',
        'data' => 'object',
        'content' => 'object',
        'settings' => 'object',
    ];

}
