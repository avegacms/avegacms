<?php

namespace AvegaCms\Enums;

enum MetaDataTypes: string
{
    case Main      = 'MAIN';
    case Page      = 'PAGE';
    case Page404   = 'PAGE_404';
    case Module    = 'MODULE';
    case Custom    = 'CUSTOM';
    case Undefined = 'UNDEFINED';

    /**
     * @param  string|null  $key
     * @return array
     */
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true]) ?
            array_column(MetaDataTypes::cases(), $key) : MetaDataTypes::cases();
    }
}