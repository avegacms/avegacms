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

    /**
     * @return array
     */
    public static function list(): array
    {
        $list = [];
        foreach (UserGenders::cases() as $enum) {
            $list[] = ['label' => lang('Users.enums.' . $enum->name), 'value' => $enum->value];
        }
        return $list;
    }
}