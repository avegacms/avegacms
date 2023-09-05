<?php

namespace AvegaCms\Enums;

enum UserConditions: string
{
    case None     = 'NONE';
    case Auth     = 'AUTH';
    case Recovery = 'RECOVERY';
    case Password = 'PASSWORD';

    public static function getValues(): array
    {
        return array_column(UserConditions::cases(), 'value');
    }
}