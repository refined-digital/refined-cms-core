<?php

namespace RefinedDigital\CMS\Modules\Media\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAltText extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'media_id',
        'type_id',
        'type_details',
        'field_name',
        'alt',
    ];

    protected $table = 'media_alt_text';

}
