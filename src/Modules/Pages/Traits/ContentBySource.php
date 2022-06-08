<?php

namespace RefinedDigital\CMS\Modules\Pages\Traits;

trait ContentBySource
{

    public $contentBySource = true;

    /**
     * Boot the is page trait for a model.
     *
     * @return void
     */
    public static function bootContentBySource()
    {

    }
    public function getContentBySource($source)
    {
        if(isset($this->content) && gettype($this->content) !== 'string' && sizeof($this->content)) {
            foreach($this->content as $content) {
                if($content->source == $source) {
                    return help()->formatOEmbed($content->content);
                }
            }
        }
    }
}
