<?php

namespace RefinedDigital\CMS\Modules\Core\Aggregates;

class SitemapXMLAggregate
{

    protected $data = [];

    public function add(string $name, string $class, string $baseUrl)
    {
        $this->data[$name] = [
            'model' => $class,
            'baseUrl' => $baseUrl
        ];
    }

    public function get()
    {
        return $this->data;
    }
}
