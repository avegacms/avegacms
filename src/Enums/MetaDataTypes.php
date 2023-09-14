<?php

namespace AvegaCms\Enums;

enum MetaDataTypes: string
{
    case Main      = 'MAIN';
    case Page      = 'PAGE';
    case Page404   = 'PAGE_404';
    case Post      = 'POST';
    case Rubric    = 'RUBRIC';
    case Module    = 'MODULE';
    case Custom    = 'CUSTOM';
    case Undefined = 'UNDEFINED';

    public static function getValues(): array
    {
        return array_column(MetaDataTypes::cases(), 'value');
    }
}