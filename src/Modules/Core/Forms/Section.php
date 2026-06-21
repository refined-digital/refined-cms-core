<?php

namespace RefinedDigital\CMS\Modules\Core\Forms;

/**
 * A positioned column within a tab (left / right / bottom). Holds blocks and
 * compiles to the legacy { blocks: [ … ] } shape under sections.{position}.
 */
class Section
{
    public const LEFT = 'left';
    public const RIGHT = 'right';
    public const BOTTOM = 'bottom';

    protected array $blocks = [];

    public function __construct(protected string $position)
    {
    }

    public static function make(string $position): static
    {
        return new static($position);
    }

    public static function left(): static
    {
        return new static(self::LEFT);
    }

    public static function right(): static
    {
        return new static(self::RIGHT);
    }

    public static function bottom(): static
    {
        return new static(self::BOTTOM);
    }

    /** @param Block[] $blocks */
    public function schema(array $blocks): static
    {
        $this->blocks = $blocks;

        return $this;
    }

    public function position(): string
    {
        return $this->position;
    }

    public function toArray(): array
    {
        return [
            'blocks' => array_map(fn (Block $block) => $block->toArray(), $this->blocks),
        ];
    }
}
