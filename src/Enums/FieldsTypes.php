<?php

declare(strict_types=1);

namespace AvegaCms\Enums;

enum FieldsTypes: string
{
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true], true) ?
            array_column(FieldsTypes::cases(), $key) : FieldsTypes::cases();
    }

    public static function list(): array
    {
        $list = [];

        foreach (FieldsTypes::cases() as $enum) {
            $list[] = ['label' => $enum->value, 'value' => $enum->name];
        }

        return $list;
    }

    case Button   = 'button';
    case Checkbox = 'checkbox';
    case Color    = 'color';
    case Date     = 'date';
    case Email    = 'email';
    case File     = 'file';
    case Hidden   = 'hidden';
    case Image    = 'image';
    case Number   = 'number';
    case Password = 'password';
    case Radio    = 'radio';
    case Range    = 'range';
    case Reset    = 'reset';
    case Search   = 'search';
    case Select   = 'select';
    case Submit   = 'submit';
    case Tel      = 'tel';
    case Text     = 'text';
    case Textarea = 'textarea';
    case Time     = 'time';
    case Url      = 'url';
    case Week     = 'week';
}
