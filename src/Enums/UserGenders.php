<?php

declare(strict_types=1);

namespace AvegaCms\Enums;

enum UserGenders: string
{
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true], true) ?
            array_column(UserGenders::cases(), $key) : UserGenders::cases();
    }

    public static function list(): array
    {
        $list = [];

        foreach (UserGenders::cases() as $enum) {
            $list[] = ['label' => lang('Users.enums.' . $enum->name), 'value' => $enum->value];
        }

        return $list;
    }
    case Male   = 'MALE';
    case Female = 'FEMALE';
}
