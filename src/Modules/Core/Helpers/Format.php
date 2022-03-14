<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;
use Str;

class Format
{
    use Macroable;

    public function heading($content, $search = '|', $replace = '<br/>')
    {
        return str_replace($search, $replace, $content);
    }

    public function button($content, $additionalClasses = [], $target = false)
    {
        if (isset($content->link) && $content->link) {
            $linkText = isset($content->link_title) && $content->link_title ? $content->link_title : 'Find out more';
            $classes = array_merge(['button'], $additionalClasses);

            $attributes = [
                'class' => implode(' ', $classes)
            ];

            if ($target) {
                $attributes['target'] = $target;
            }

            $link = help()->checkLink($content->link);

            if (is_numeric(strpos($link, 'http')) && !is_numeric(strpos($link, config('app.url')))) {
                $attributes['target'] = '_blank';
            }

            return '<a href="'.$link.'"'.help()->arrToAttr($attributes).'>'.$linkText.'</a>';

        }

        return '';
    }

    public function removeAttributes($content, $attributes = [])
    {
        foreach ($attributes as $attribute) {
            $content = preg_replace('/'.$attribute.'=\"\d*\"/', '', $content);
        }

        return $content;
    }
}
