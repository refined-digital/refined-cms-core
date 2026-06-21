<?php

namespace RefinedDigital\CMS\Modules\Core\Forms;

use RefinedDigital\CMS\Modules\Core\Forms\Fields\Field;

/**
 * A row of fields rendered side-by-side. Compiles to the inner field array
 * the legacy renderer groups into a single form row.
 *
 * @var Field[] $fields
 */
class Row
{
    /** @param Field[] $fields */
    public function __construct(protected array $fields)
    {
    }

    /** @param Field[] $fields */
    public static function make(array $fields): static
    {
        return new static($fields);
    }

    public function toArray(): array
    {
        return array_map(fn (Field $field) => $field->toArray(), $this->fields);
    }
}
