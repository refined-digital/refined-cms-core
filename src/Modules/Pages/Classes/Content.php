<?php

namespace RefinedDigital\CMS\Modules\Pages\Classes;

class Content {
    public function has($key) {
        return isset($this->{$key}) && $this->{$key};
    }

    public function get($key) {
        if ($this->has($key)) {
            return $this->{$key};
        }

        return null;
    }

    public function set($key, $value)
    {
        $this->{$key} = $value;
    }
}