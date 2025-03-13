<?php

declare(strict_types=1);

namespace AvegaCms\Enums;

enum FieldsReturnTypes: string
{
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true], true) ?
            array_column(self::cases(), $key) : self::cases();
    }

    public static function list(): array
    {
        $list = [];

        foreach (self::cases() as $enum) {
            $list[] = ['label' => $enum->value, 'value' => $enum->name];
        }

        return $list;
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
