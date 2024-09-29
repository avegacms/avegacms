<?php

declare(strict_types=1);

namespace AvegaCms\Enums;

enum MetaDataTypes: string
{
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true], true) ?
            array_column(MetaDataTypes::cases(), $key) : MetaDataTypes::cases();
    }
    case Main      = 'MAIN';
    case Page      = 'PAGE';
    case Page404   = 'PAGE_404';
    case Module    = 'MODULE';
    case Custom    = 'CUSTOM';
    case Undefined = 'UNDEFINED';
}
