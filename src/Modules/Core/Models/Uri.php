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

    public function getSlugOptions() : SlugOptions
    {
        $slug = $this->name ? 'name' : 'title';
        return SlugOptions::create()
                          ->generateSlugsFrom($slug)
                          ->saveSlugsTo('uri');
    }

    public function template()
    {
        return $this->belongsTo('RefinedDigital\CMS\Modules\Pages\Models\Template');
    }

}
