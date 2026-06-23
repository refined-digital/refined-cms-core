<?php

namespace RefinedDigital\CMS\Modules\Core\Forms\Fields;

/**
 * Plain text input — the renderer's default element, so no type is set.
 */
class TextInput extends Field
{
    public function email(): static
    {
        return $this->type('email');
    }

    public function url(): static
    {
        return $this->type('url');
    }

    public function number(): static
    {
        return $this->type('number');
    }
}
