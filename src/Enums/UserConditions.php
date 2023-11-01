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
     * @param  array  $exclude
     * @return array
     */
    public static function getValues(array $exclude = []): array
    {
        return array_diff(array_column(UserConditions::cases(), 'value'), $exclude);
    }
}