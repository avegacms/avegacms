<?php

namespace AvegaCms\Enums;

enum UserGenders: string
{
    case Male   = 'MALE';
    case Female = 'FEMALE';

    /**
     * @param  string|null  $key
     * @return array
     */
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true]) ?
            array_column(UserGenders::cases(), $key) : UserGenders::cases();
    }
}