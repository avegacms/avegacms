<?php

namespace AvegaCms\Enums;


enum EntityTypes: string
{
    case Content = 'CONTENT';
    case Module  = 'MODULE';

    public static function getValues(): array
    {
        return array_column(EntityTypes::cases(), 'value');
    }
}