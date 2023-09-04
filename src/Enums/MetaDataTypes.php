<?php

namespace AvegaCms\Enums;

enum MetaDataTypes: string
{
    case Page      = 'PAGE';
    case Post      = 'POST';
    case Category  = 'CATEGORY';
    case Module    = 'MODULE';
    case Custom    = 'CUSTOM';
    case Undefined = 'Undefined';

    public static function getValues(): array
    {
        return array_column(MetaDataTypes::cases(), 'value');
    }
}