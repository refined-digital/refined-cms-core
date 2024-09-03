<?php

namespace RefinedDigital\CMS\Modules\Core\Aggregates;

class AssetAggregate
{

    protected $styles = [];
    protected $scripts = [];

    public function addStyle($file): void
    {
        $this->styles[$file] = 'resources/css/components/'.$file;
    }

    public function getStyles(): array
    {
        return $this->styles;
    }

    public function addScript($file): void
    {
        $this->scripts[$file] = 'resources/js/components/'.$file;
    }

    public function getScripts(): array
    {
        return $this->scripts;
    }

}
