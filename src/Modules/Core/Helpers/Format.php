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

    public function button($content, $additionalClasses = [], $target = false, $additionalAttributes = [])
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

            if (sizeof($additionalAttributes)) {
                $attributes = array_merge($attributes, $additionalAttributes);
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

    public function colour($content, $replacements = [])
    {
        if (!sizeof($replacements)) {
            return $content;
        }

        foreach ($replacements as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
            }
            $search = ['['.$key.']', '[/'.$key.']'];
            $replace = ['<span class="text-colour--'.$value.'">', '</span>'];
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }
}