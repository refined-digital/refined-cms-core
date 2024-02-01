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

    function desktopMobileImages($desktop = ['width' => 1920, 'height' => 1080, 'src' => ''], $mobile = ['width' => 800, 'height' => 1150, 'src' => ''])
    {
        if (isset($desktop['src']) && !isset($mobile['src'])) {
            return image()
                ->load($desktop['src'])
                ->fit()
                ->lazy()
                ->dimensions([
                    ['media' => 800, 'width' => $desktop['width'], 'height' => $desktop['height']],
                    ['width' => $desktop['width'] * 0.75, 'height' => $desktop['height'] * 0.75]
                ])
                ->pictureHtml();
        }

        $images = [];
        $images[] = image()->load($desktop['src'])
            ->fit()
            ->lazy()
            ->width($desktop['width'])
            ->height($desktop['height'])
            ->string();

        if (isset($mobile['src']) && $mobile['src']) {
            $images[] = image()->load($mobile['src'])
                ->fit()
                ->lazy()
                ->width($mobile['width'])
                ->height($mobile['height'])
                ->string();

        }


        $html = '<picture>';
        foreach ($images as $index => $img) {
            $html .= '<source srcset="' . asset($img) . '"';
            if ($index == 0) {
                $html .= ' media="(min-width: 640px)"';
            }
            $html .= '/>';
        }
        $html .= '<img src="' . asset($images[0]) . '" loading="lazy"/>';
        $html .= '</picture>';

        return $html;
    }

    public function maybeJsonDecode($data)
    {
        if (is_string($data)) {
            // Attempt to decode JSON string
            $decoded = json_decode($data, true);

            if (is_string($decoded)) {
                return $this->maybeJsonDecode($decoded);
            }

            // Check if decoding was successful
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // If not a string or decoding fails, return the original data
        return $data;
    }
}
