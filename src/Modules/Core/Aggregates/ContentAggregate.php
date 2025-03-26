<?php

namespace RefinedDigital\CMS\Modules\Core\Aggregates;

use Illuminate\Support\Collection;

class ContentAggregate
{
    protected Collection $modules;
    protected array $newFields = [];

    public function __construct()
    {
        $this->modules = collect();
    }

    public function registerNewField(string $name, array $config): self
    {
        $this->newFields[$name] = $config;

        return $this;
    }

    public function register(string $moduleClass): self
    {
        $this->modules->push( $moduleClass );

        return $this;
    }

    public function get(): Collection
    {
        return $this->modules;
    }

    public function getForConfig(): array
    {
        return $this->modules->map(function($module) {
            $item = new $module();

            return $item->getForConfig($module);
        })->toArray();
    }

    public function getNewFields(): array
    {
        return $this->newFields;
    }
}
