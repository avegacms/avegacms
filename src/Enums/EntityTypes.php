<?php

declare(strict_types=1);

namespace AvegaCms\Enums;

enum EntityTypes: string
{
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true], true) ?
            array_column(EntityTypes::cases(), $key) : EntityTypes::cases();
    }
    case Content = 'CONTENT';
    case Module  = 'MODULE';
}
