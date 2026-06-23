<?php

namespace RefinedDigital\CMS\Modules\Core\Aggregates;

/**
 * Registry of form-builder integrations. Installable packages self-register from
 * their service provider's register():
 *
 *   app(FormBuilderIntegrationAggregate::class)->register('mailchimp', [
 *     'name'        => 'Mailchimp',
 *     'icon'        => '<svg>…</svg>',
 *     'description' => 'Subscribe submitters to a Mailchimp audience',
 *     'processor'   => \Vendor\…\Process::class,   // implements FormBuilderIntegrationInterface
 *     'settings'    => [ ['name'=>'list_id','label'=>'List ID','type'=>'text','required'=>true] ],
 *     'view'        => null,  // optional: front-end injection markup callback/class
 *   ]);
 *
 * Lives in core because both the form-builder editor (registered into core's Vue
 * app) and the admin API need it, and core is the shared dependency.
 */
class FormBuilderIntegrationAggregate
{
    protected array $integrations = [];

    public function register(string $key, array $config): void
    {
        $this->integrations[$key] = array_merge([
            'key'         => $key,
            'name'        => $key,
            'icon'        => null,
            'description' => null,
            'processor'   => null,
            'settings'    => [],
            'view'        => null,
        ], $config);
    }

    public function get(string $key)
    {
        return $this->integrations[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->integrations[$key]);
    }

    public function all(): array
    {
        return $this->integrations;
    }
}
