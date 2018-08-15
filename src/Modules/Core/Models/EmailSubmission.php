<?php

namespace RefinedDigital\CMS\Modules\Core\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class EmailSubmission extends CoreModel
{
    use SoftDeletes;

    protected $fillable = [
        'form_id', 'to', 'from', 'ip', 'data'
    ];

    protected $casts = [
        'data' => 'object'
    ];
}
