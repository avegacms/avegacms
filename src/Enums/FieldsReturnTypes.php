<?php

namespace AvegaCms\Enums;

enum FieldsReturnTypes: string
{
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true], true) ?
            array_column(FieldsReturnTypes::cases(), $key) : FieldsReturnTypes::cases();
    }
    case Integer   = 'INTEGER';
    case Float     = 'FLOAT';
    case Double    = 'DOUBLE';
    case String    = 'STRING';
    case Boolean   = 'BOOLEAN';
    case Array     = 'ARRAY';
    case Time      = 'TIME';
    case Date      = 'DATE';
    case DateTime  = 'DATETIME';
    case Timestamp = 'TIMESTAMP';
    case Json      = 'JSON';
}
