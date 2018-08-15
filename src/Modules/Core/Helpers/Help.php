<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;

class Help {
    use Macroable;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function trace($data, $exit = false)
	{
		echo '<code style="display:block;background:#fff; color:#111; font-size:14px; line-height:1.3;padding:5px;border:1px solid #eee; margin:1em 0;">';
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

        $url = ltrim($url, '/');

        // if there is no http, then add the site url
        if(!is_numeric(strpos($url, 'http://')) && !is_numeric(strpos($url, 'https://'))) {
            $prefix = rtrim(config('app.url'), '/').'/';
        } else {
            $isExternal = true;
        }

        // if there is a www and its at the start of the string, add the http://
        $www = strpos($url, 'www');
        if(is_numeric($www) && $www < 1) {
            $prefix = 'http://';
        }

        $link = $prefix.$url;

        if ($isExternal && $addTarget) {
            $link .= '" target="_blank';
        }

        return $link;
    }

    /**
     * encode Email
     *
     * returns a hexed encoded email for anti spam
     */
    public function encodeEmail($email)
    {
        $emailHex = '';
        $mailto = '&#109;&#97;&#105;&#108;&#116;&#111;&#58;';

        for($i=0; $i<strlen($email); $i++){
            $emailHex .= '&#'.hexdec(bin2hex($email[$i])).';';
        }

        return '<a href="'.$mailto.$emailHex.'">'.$emailHex.'</a>';
    }

    public function encodePhone($phone)
    {
        return '<a href="'.$this->encodePhoneStr($phone).'">'.$phone.'</a>';
    }

    public function encodePhoneStr($phone)
    {
        return 'tel:'.$this->clean(preg_replace('/\D/', '', $phone));
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
}