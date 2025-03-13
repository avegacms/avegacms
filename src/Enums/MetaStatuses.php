<?php

declare(strict_types=1);

namespace AvegaCms\Enums;

enum MetaStatuses: string
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
            $list[] = ['label' => lang('Enums.MetaStatuses.' . $enum->name), 'value' => $enum->value];
        }

        return $list;
    }
    case Publish   = 'PUBLISH';
    case Future    = 'FUTURE';
    case Moderated = 'MODERATED';
    case Draft     = 'DRAFT';
    case Trash     = 'TRASH';
}
