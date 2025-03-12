<?php

declare(strict_types=1);

namespace RefinedDigital\CMS\Modules\Core\Aggregates;

class AssetAggregate
{
    protected $styles = [];
    protected $scripts = [];

    protected $moduleStyles = [];
    protected $moduleScripts = [];

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

    public function addModuleStyle($file): self
    {
        $this->moduleStyles[$file] = $file;

        return $this;
    }

    public function getModuleStyles(): string
    {
        return join(PHP_EOL, array_map(function ($style) {
            return '<link href="'.$this->formatParameters($style).'" rel="stylesheet">';
        }, $this->moduleStyles));
    }

    public function addModuleScript($key, $file, $attributes = []): self
    {
        $this->moduleScripts[$key] = [
            'src' => $file,
            'attributes' => $attributes,
        ];

        return $this;
    }

    public function getModuleScripts(): string
    {
        return join(PHP_EOL, array_map(function ($script) {
            $attributes = $script['attributes'] ?? [];
            $attributes['src'] = \Str::contains($script['src'], 'recaptcha/api.js')
                ? $script['src']
                : $this->formatParameters($script['src']);
            return '<script'.help()->arrToAttr($attributes).'></script>';
        }, $this->moduleScripts));
    }

    private function formatParameters(string $value): string
    {
        $parameters = [];
        if (\Str::contains($value, '?')) {
            $query = parse_url($value, PHP_URL_QUERY);

            if ($query) {
                parse_str($query, $params);
                $parameters = array_merge($parameters, $params);
            }

            $value = str_replace('?'.$query, '', $value);
        }

        if (!isset($parameters['v'])) {
            $parameters['v'] = uniqid();
        }

        return $value.'?'.http_build_query($parameters);
    }
}
