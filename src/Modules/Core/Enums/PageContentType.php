<?php

namespace RefinedDigital\CMS\Modules\Core\Enums;

enum PageContentType: int
{
    case RICH = 1;

    case STATIC = 2;

    case PLAIN = 3;

    case IMAGE = 4;

    case FILE = 5;

    case SELECT = 6;

    case LINK = 7;

    case NUMBER = 8;

    case REPEATABLE = 9;

    case PASSWORD = 10;

    case COLOUR = 11;

    case MULTISELECT = 12;

    case COLOUR_SET = 13;

    /**
     * Resolves a field definition's 'type' into its integer id. Accepts the
     * enum case, its int value, or the case name as a string ('rich').
     */
    public static function resolveId(self|int|string $type): int
    {
        if ($type instanceof self) {
            return $type->value;
        }

        if (is_string($type) && !is_numeric($type)) {
            foreach (self::cases() as $case) {
                if (strcasecmp($case->name, $type) === 0) {
                    return $case->value;
                }
            }

            throw new \InvalidArgumentException('Unknown content field type "'.$type.'"');
        }

        return (int) $type;
    }

}
