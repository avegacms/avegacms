<?php

declare(strict_types=1);

namespace AvegaCms\Enums;

enum UserConditions: string
{
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true], true) ?
            array_column(UserConditions::cases(), $key) : UserConditions::cases();
    }
    case None         = 'NONE';
    case Registration = 'REGISTRATION';
    case Auth         = 'AUTH';
    case CheckPhone   = 'CHECK_PHONE';
    case CheckEmail   = 'CHECK_EMAIL';
    case Recovery     = 'RECOVERY';
    case Password     = 'PASSWORD';
}
