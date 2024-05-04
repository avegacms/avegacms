<?php

declare(strict_types = 1);

namespace AvegaCms\Enums;

enum EntityTypes: string
{
    case Content = 'CONTENT';
    case Module  = 'MODULE';

    /**
     * @param  string|null  $key
     * @return array
     */
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true]) ?
            array_column(EntityTypes::cases(), $key) : EntityTypes::cases();
    }
}