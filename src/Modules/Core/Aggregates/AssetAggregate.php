<?php

namespace RefinedDigital\CMS\Modules\Core\Aggregates;

class AssetAggregate
{

    protected $styles = [];
    protected $scripts = [];

    public function addStyle($file): self
    {
        $this->styles[$file] = 'resources/css/components/'.$file;

        return $this;
    }

    public function getStyles(): array
    {
        return $this->styles;
    }

    public function addScript($file): self
    {
        $this->scripts[$file] = 'resources/js/components/'.$file;

        return $this;
    }

    public function getScripts(): array
    {
        return $this->scripts;
    }

}
