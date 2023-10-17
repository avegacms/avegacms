<?php

namespace AvegaCms\Enums;

enum UserGenders: string
{
    case Male   = 'MALE';
    case Female = 'FEMALE';

    public static function getValues(): array
    {
        return array_column(UserGenders::cases(), 'value');
    }
}