<?php

declare(strict_types=1);

namespace AvegaCms\Enums;

enum UserStatuses: string
{
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true], true) ?
            array_column(UserStatuses::cases(), $key) : UserStatuses::cases();
    }

    public static function list(): array
    {
        $list = [];

        foreach (UserStatuses::cases() as $enum) {
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
