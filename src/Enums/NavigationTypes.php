<?php

namespace AvegaCms\Enums;

enum NavigationTypes: string
{
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true], true) ?
            array_column(NavigationTypes::cases(), $key) : NavigationTypes::cases();
    }
    case Group   = 'GROUP';
    case Link    = 'LINK';
    case Button  = 'BUTTON';
    case Divider = 'DIVIDER';
}
