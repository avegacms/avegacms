<?php

namespace AvegaCms\Enums;

enum UserConditions: string
{
    case None         = 'NONE';
    case Registration = 'REGISTRATION';
    case Auth         = 'AUTH';
    case CheckPhone   = 'CHECK_PHONE';
    case CheckEmail   = 'CHECK_EMAIL';
    case Recovery     = 'RECOVERY';
    case Password     = 'PASSWORD';

    /**
     * @param  string|null  $key
     * @return array
     */
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true]) ?
            array_column(UserConditions::cases(), $key) : UserConditions::cases();
    }
}