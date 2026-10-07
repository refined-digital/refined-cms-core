<?php

namespace RefinedDigital\CMS\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Uri extends Model
{
    use HasSlug;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uri', 'name', 'title', 'description', 'template_id', 'uriable_type', 'uriable_id',
    ];

    protected $table = 'uri';

    protected $casts = [
      'id' => 'integer',
      'template_id' => 'integer',
      'uriable_id' => 'integer',
    ];

    public function getSlugOptions() : SlugOptions
    {
        // Str::slug drops '&' entirely; 'bars & clubs' should become 'bars-and-clubs' not 'bars-clubs'
        return SlugOptions::create()
                          ->generateSlugsFrom(fn ($model) => str_replace('&', ' and ', $model->name ?: $model->title))
                          ->saveSlugsTo('uri');
    }

    public function template()
    {
        return $this->belongsTo('RefinedDigital\CMS\Modules\Pages\Models\Template');
    }

    public function details()
    {
        return $this->morphTo(__FUNCTION__, 'uriable_type', 'uriable_id');//('RefinedDigital\CMS\Modules\Pages\Models\Page', 'uriable');
    }
}
