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

    public static function getValues(): array
    {
        return array_column(UserStatuses::cases(), 'value');
    }
}
