<?php

namespace RefinedDigital\CMS\Modules\Core\Routing;

use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;

class CustomUrlGenerator extends BaseUrlGenerator
{
    public function route($name, $parameters = [], $absolute = true)
    {
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        // Automatically add the 'tenant' parameter if it's missing and needed
        if (is_array($parameters) && !array_key_exists('tenant', $parameters) && $this->requiresTenantParameter($name)) {
            $parameters['tenant'] = $this->getTenantIdentifier();
            if (isset($parameters[0]) && is_numeric($parameters[0])) {
                $parameters['id'] = $parameters[0];
            }
        }

        return parent::route($name, $parameters, $absolute);
    }

    protected function requiresTenantParameter($name)
    {
        // Implement logic to determine if the route needs a 'tenant' parameter
        // For example, check if the route name starts with 'refined.'
        return str_starts_with($name, 'refined.') && help()->isMultiTenancy();
    }

    /**
     * Retrieve the tenant identifier.
     *
     * @return mixed
     */
    protected function getTenantIdentifier()
    {
        // Implement logic to retrieve the tenant identifier
        // Example: return session('tenant_id') or subdomain logic
        return request()->segment(1) ?? 'default-tenant';
    }

}