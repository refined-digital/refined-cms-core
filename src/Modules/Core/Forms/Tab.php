<?php

namespace RefinedDigital\CMS\Modules\Core\Forms;

use InvalidArgumentException;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\Field;

/**
 * A top-level form tab. Its schema may contain Sections (left/right/bottom),
 * Blocks, or bare Rows/Fields — each compiling to the matching legacy tab
 * shape the form blades understand:
 *   { name, sections: { left, right, bottom } }
 *   { name, blocks: [ … ] }
 *   { name, fields: [ [field, …], … ] }
 */
class Tab
{
    protected array $items = [];

    public function __construct(protected string $name)
    {
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    /** @param array<Section|Block|Row|Field> $items */
    public function schema(array $items): static
    {
        $this->items = $items;

        return $this;
    }

    public function toArray(): array
    {
        $tab = ['name' => $this->name];

        if ($this->containsOnly(Section::class)) {
            $sections = [];
            foreach ($this->items as $section) {
                $sections[$section->position()] = $section->toArray();
            }
            $tab['sections'] = $sections;

            return $tab;
        }

        if ($this->containsOnly(Block::class)) {
            $tab['blocks'] = array_map(fn (Block $block) => $block->toArray(), $this->items);

            return $tab;
        }

        // bare rows / fields — flatten to the { fields: [ [..], .. ] } shape
        $rows = [];
        foreach ($this->items as $item) {
            if ($item instanceof Row) {
                $rows[] = $item->toArray();
            } elseif ($item instanceof Field) {
                $rows[] = Row::make([$item])->toArray();
            } else {
                throw new InvalidArgumentException(
                    'A Tab schema must contain only Sections, only Blocks, or Rows/Fields — not a mix.'
                );
            }
        }
        $tab['fields'] = $rows;

        return $tab;
    }

    protected function containsOnly(string $class): bool
    {
        if ($this->items === []) {
            return false;
        }

        foreach ($this->items as $item) {
            if (! $item instanceof $class) {
                return false;
            }
        }

        return true;
    }
}
