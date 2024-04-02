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

}
