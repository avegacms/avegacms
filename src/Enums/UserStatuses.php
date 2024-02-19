<?php

namespace AvegaCms\Enums;

enum UserStatuses: string
{
    case Registration = 'REGISTRATION';
    case Active       = 'ACTIVE';
    case Moderated    = 'MODERATED';
    case Banned       = 'BANNED';
    case Deleted      = 'DELETED';
    case NotDefined   = 'NOT_DEFINED';

    /**
     * @param  string|null  $key
     * @return array
     */
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true]) ?
            array_column(UserStatuses::cases(), $key) : UserStatuses::cases();
    }

    /**
     * @return array
     */
    public static function list(): array
    {
        $list = [];
        foreach (UserStatuses::cases() as $enum) {
            $list[] = ['label' => lang('Users.enums.' . $enum->name), 'value' => $enum->value];
        }
        return $list;
    }
}
