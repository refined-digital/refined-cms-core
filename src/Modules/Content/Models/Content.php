<?php

namespace RefinedDigital\CMS\Modules\Content\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $fillable = [
        'contentable_id',
        'contentable_type',
        'content_class',
        'position',
        'data',
        'field',
    ];

    protected $casts = [
        'data' => 'json'
    ];

}
