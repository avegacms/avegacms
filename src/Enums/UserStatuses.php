<?php

declare(strict_types=1);

namespace AvegaCms\Enums;

enum UserStatuses: string
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
            $list[] = ['label' => lang('Users.enums.' . $enum->name), 'value' => $enum->value];
        }

        return $list;
    }
    case Registration = 'REGISTRATION';
    case Active       = 'ACTIVE';
    case Moderated    = 'MODERATED';
    case Banned       = 'BANNED';
    case Deleted      = 'DELETED';
    case NotDefined   = 'NOT_DEFINED';
}
