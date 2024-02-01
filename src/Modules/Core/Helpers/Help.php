<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;
use Str;

class Help {
    use Macroable;

    protected $request;
    protected $mailtoHex = '&#109;&#97;&#105;&#108;&#116;&#111;&#58;';

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function trace($data, $exit = false)
	{
		echo '<code style="display:block;background:#fff; color:#111; font-size:14px; line-height:1.3;padding:5px;border:1px solid #eee; margin:1em 0; text-align: left;">';
		if (is_object($data) || is_array($data)) {
            echo '<pre>' . print_r($data, 1) . '</pre>';
		} else {
			echo $data;
		}
		echo '</code>';

		if ($exit) exit();
    }

    public function getTableLink($field)
    {
        if ( (isset($field->sortable) && !$field->sortable) || !isset($field->sortable)) {
            return $field->name;
        }

       $get = $this->request->all();

        $dir = 'asc';
        $cDir = 'up';
        if($this->request->get('sort') == $field->field && $this->request->get('dir') == 'asc') {
            $dir = 'desc';
            $cDir = 'down';
        }

        $get['sort'] = $field->field;
        $get['dir'] = $dir;

        $link = '<a href="?'.http_build_query($get).'">';
            $link .= $field->name;

            if($field->field == $this->request->get('sort')){
                $link .= '<i class="fa fa-caret-'.$cDir.'"></i>';
            }
        $link .= '</a>';

        return $link;
    }


    public function formatBytes($size, $precision = 2)
    {
        if ($size > 0) {
            $size = (int) $size;
            $base = log($size) / log(1024);
            $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');

            return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
        } else {
            return $size;
        }
    }

    public function checkLink($url, $addTarget = false)
    {
        $prefix = '';
        $isExternal = false;

        if (\Str::startsWith($url, '[settings')) {
            $link = settings()->getByKeyCode($url, true);
            if (isset($link->link->original) && $link->link->original) {
                return $link->link->original . '" target="_blank';
            }
        }

        if (\Str::startsWith($url, '[forms')) {
            $bits = explode(':', trim($url, '[]'));
            $key = end($bits);
            return '#" data-type="'.$key;
        }

        $url = ltrim($url, '/');
        $base = rtrim(config('app.url'), '/');

        // trim the base url, if it was included (urls should be relative, not absolute)
        if (is_numeric(strpos($url, $base))) {
            $url = str_replace($base, '', $url);
        }

        // if there is no http, then add the site url
        if(!is_numeric(strpos($url, 'http://')) && !is_numeric(strpos($url, 'https://'))) {
            $prefix = $base.'/';
        } else {
            $isExternal = true;
        }

        // if there is a www and its at the start of the string, add the http://
        $www = strpos($url, 'www');
        if(is_numeric($www) && $www < 1) {
            $prefix = 'http://';
        }

        $link = $prefix.$url;

        // check if it's a file
        $info = pathinfo($link);
        $isFile = isset($info['extension']);

        if (($isFile || $isExternal) && $addTarget) {
            $link .= '" target="_blank';
        }

        return $link;
    }

    /**
     * encode Email
     *
     * returns a hexed encoded email for anti spam
     */
    public function encodeEmail($email, $text = '', $classes = '')
    {
        $emailHex = $this->encodeEmailStr($email);

        if (!$text) {
            $text = str_replace($this->mailtoHex, '', $emailHex);
        }

        return '<a href="'.$emailHex.'"' . ( $classes ? ' class="'.$classes.'"' : '') . '>'.$text.'</a>';
    }

    public function encodeEmailStr($email)
    {
        $emailHex = '';
        for($i=0; $i<strlen($email); $i++){
            $emailHex .= '&#'.hexdec(bin2hex($email[$i])).';';
        }

        return $this->mailtoHex.$emailHex;
    }

    public function encodePhone($phone, $text = '', $classes = '')
    {
        if (!$text) {
            $text = $phone;
        }

        return '<a href="'.$this->encodePhoneStr($phone).'"' . ( $classes ? ' class="'.$classes.'"' : '') . '>'.$text.'</a>';
    }

    public function encodePhoneStr($phone)
    {
        $plus = '';
        if (is_numeric(strpos($phone, '+'))) {
            $plus = '+';
        }
        return 'tel:'.$plus.Str::slug(preg_replace('/\D/', '', $phone));
    }

    public function arrToAttr($attrs)
    {
        $data = '';

        // if the type is an object, convert to array to loop over
        if (gettype($attrs) == 'object') {
            $attrs = (array) $attrs;
        }

        if (is_array($attrs) && sizeof($attrs)) {
            foreach ($attrs as $key => $value) {
                if (is_numeric($key)) {
                    $data .= ' '.$value;
                } else {
                    $data .= ' '.$key.'="'.$value.'"';
                }
            }
        }

        return $data;
    }

    public function getClientIP() {

        if (array_key_exists('HTTP_X_REAL_IP', $_SERVER)) {
            $ipaddress = $_SERVER['HTTP_X_REAL_IP'];
        } else if(array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if(array_key_exists('HTTP_X_FORWARDED', $_SERVER)) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if(array_key_exists('HTTP_FORWARDED_FOR', $_SERVER)) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if(array_key_exists('HTTP_FORWARDED', $_SERVER)) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if(array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    public function array_splice($array, $position, $data)
    {
        array_splice($array, $position + 1, 0, [$data]);
        return $array;
    }

    public function getYoutubeEmbedLink($shareLink, $autoplay = true)
    {
        $videoBits = explode('/', $shareLink);
        $videoKey = $videoBits[sizeof($videoBits)-1];
        return '//www.youtube.com/embed/'.$videoKey.'?rel=0&showinfo=0&autoplay='.$autoplay;
    }

    public function getVimeoEmbedLink($link, $attrs =['muted' => true, 'controls' => 0, 'loop' => 1, 'autoplay' => 1])
    {
        $videoBits = explode('/', $link);
        $videoKey = $videoBits[sizeof($videoBits)-1];
        $joiner = is_numeric(strpos($videoKey, '?')) ? '&' : '?';
        return 'https://player.vimeo.com/video/'.$videoKey.$joiner.http_build_query($attrs);
    }

    public function getVideoShareLink($link, $autoPlay, $attrs)
    {
        if (is_numeric(strpos($link, 'youtube'))) {
            return $this->getYoutubeEmbedLink($link, $autoPlay);
        } else if (is_numeric(strpos($link, 'vimeo'))) {
            return $this->getVimeoEmbedLink($link, $attrs);
        }
    }

    public function getDaysOfWeek()
    {
        return [
            '1' => 'Monday',
            '2' => 'Tuesday',
            '3' => 'Wednesday',
            '4' => 'Thursday',
            '5' => 'Friday',
            '6' => 'Saturday',
            '7' => 'Sunday',
        ];
    }

    public function formatOEmbed($content)
    {
        preg_match_all('#<figure class="media">(.*?)</figure>#', $content, $matches);
        if (sizeof($matches) && sizeof($matches[0])) {
            foreach ($matches[0] as $key => $figure) {
                $attrs = [
                    'src' => '',
                    'allowfullscreen' => true,
                    'frameborder' => 0,
                ];

                $src = new \SimpleXMLElement($matches[1][$key]);
                $link = $src['url'];

                if (is_numeric(strpos($link, 'youtub'))) {
                    $link = $this->getYoutubeEmbedLink($link, false);
                }
                $attrs['src'] = $link;
                $dimensions = $this->getDimensionsFromURL($link);
                $attrs['width'] = $dimensions->width;
                $attrs['height'] = $dimensions->height;


                $newFigure = '<figure class="embed">';
                    $newFigure .= '<iframe'.$this->attrsToString($attrs).'></iframe>';
                $newFigure .= '</figure>';

                $content = str_replace($figure, $newFigure, $content);
            }
        }

        return $content;
    }

    public function attrsToString($attrs)
    {
        $string = '';
        foreach ($attrs as $key => $value) {
            $string .= ' '.$key.'="'.$value.'"';
        }

        return $string;
    }

    public function getDimensionsFromURL($url)
    {
        $dimensions = new \stdClass();
        $dimensions->width = 560;
        $dimensions->height = 315;
        $bits = explode('&', $url);
        if (sizeof($bits)) {
            foreach ($bits as $b) {
                $d = explode('=', $b);
                if ($d[0] === 'width') {
                    $dimensions->width = $d[1];
                }
                if ($d[0] === 'height') {
                    $dimensions->height = $d[1];
                }
            }
        }

        return $dimensions;
    }

    public function explodeAndTrim($data)
    {
        $items = explode(',', $data);
        $values = array_map(function($item) {
            return trim($item);
        }, $items);

        return array_filter($values);
    }

    public function getUploadMaxFilesize()
    {
        $size = (int) ini_get('upload_max_filesize');

        $htaccess = explode(PHP_EOL, file_get_contents(public_path('.htaccess')));

        // see if there is an upload_max_filesize
        if (sizeof($htaccess)) {
            foreach ($htaccess as $line) {
                if (is_numeric(strpos($line, 'upload_max_filesize'))) {
                    $lineItems = explode(' ', $line);
                    $size = (int) $lineItems[sizeof($lineItems)-1];
                }
            }
        }

        return $size;
    }

    public function isMultiTenancy(): bool
    {
        return (bool) env('APP_MULTI_TENANCY');
    }

    public function getIncomingUrl($host = false): string
    {
        $parsedUrl = parse_url(request()->url());
        $cleanedUrl = '';

        if (isset($parsedUrl['scheme'])) {
            $cleanedUrl .= $parsedUrl['scheme'] . '://';
        }

        if (isset($parsedUrl['host'])) {
            $cleanedUrl .= $host ? $host : $parsedUrl['host'];
        }

        if (isset($parsedUrl['port'])) {
            $cleanedUrl .= ':' . $parsedUrl['port'];
        }

        return $cleanedUrl;
    }

    public function getSiteUrl(): string
    {
        if ($this->isMultiTenancy()) {
            $url = $this->getIncomingUrl().'/'.request()->segment(1);
            return rtrim($url, '/');
        }

        return rtrim(config('app.url'), '/');
    }

    public function getPublicUrl(): string
    {
        if ($this->isMultiTenancy()) {
            $tenant = tenant();
            $domain = $tenant->domains()->first()->domain;
            $url = $this->getIncomingUrl($domain);
            return rtrim($url, '/');
        }

        return rtrim(env('PUBLIC_URL') ?? config('app.url'), '/');
    }
}
