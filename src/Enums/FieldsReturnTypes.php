<?php

namespace AvegaCms\Enums;

enum FieldsReturnTypes: string
{
    case Integer   = 'INTEGER';
    case Float     = 'FLOAT';
    case Double    = 'DOUBLE';
    case String    = 'STRING';
    case Boolean   = 'BOOLEAN';
    case Array     = 'ARRAY';
    case DateTime  = 'DATETIME';
    case Timestamp = 'TIMESTAMP';
    case Json      = 'JSON';

    /**
     * @param  string|null  $key
     * @return array
     */
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true]) ?
            array_column(FieldsReturnTypes::cases(), $key) : FieldsReturnTypes::cases();
    }
}