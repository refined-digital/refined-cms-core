<?php

namespace RefinedDigital\CMS\Modules\Pages\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use RefinedDigital\CMS\Modules\Core\Models\CoreModel;

class PageContent extends CoreModel
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'page_id', 'page_content_type_id', 'position', 'name', 'source', 'note', 'content',
    ];

    protected $casts = [
      'id' => 'integer',
      'page_id' => 'integer',
      'page_content_type_id' => 'integer',
      'position' => 'integer',
    ];


    public function type()
    {
        return $this->belongsTo('RefinedDigital\CMS\Modules\Pages\Models\PageContentType', 'page_content_type_id')
                ->select('id', 'name');
    }

}
