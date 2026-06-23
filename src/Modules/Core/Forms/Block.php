<?php

namespace RefinedDigital\CMS\Modules\Core\Forms;

use RefinedDigital\CMS\Modules\Core\Forms\Fields\Field;

/**
 * A titled card containing rows of fields. Compiles to the legacy
 * { name, fields: [ [field, …], … ] } block shape.
 *
 * schema() accepts Row objects, or bare Field objects (each placed on its
 * own row), so simple blocks don't need explicit Row::make() wrapping.
 */
class Block
{
    protected array $rows = [];

    public function __construct(protected ?string $name = null)
    {
    }

    public static function make(?string $name = null): static
    {
        return new static($name);
    }

    /** @param array<Row|Field> $items */
    public function schema(array $items): static
    {
        foreach ($items as $item) {
            $this->rows[] = $item instanceof Row ? $item : Row::make([$item]);
        }

        return $this;
    }

    public function toArray(): array
    {
        $block = [
            'fields' => array_map(fn (Row $row) => $row->toArray(), $this->rows),
        ];

        if ($this->name !== null) {
            $block = ['name' => $this->name] + $block;
        }

        return $block;
    }
}
