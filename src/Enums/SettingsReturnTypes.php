<?php

namespace AvegaCms\Enums;

enum SettingsReturnTypes: string
{
    case Integer   = 'INTEGER';
    case Float     = 'FLOAT';
    case String    = 'STRING';
    case Boolean   = 'BOOLEAN';
    case Array     = 'ARRAY';
    case DateTime  = 'DATETIME';
    case Timestamp = 'TIMESTAMP';
    case Json      = 'JSON';

    public static function getValues(): array
    {
        return array_column(SettingsReturnTypes::cases(), 'value');
    }
}