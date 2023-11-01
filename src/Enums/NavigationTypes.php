<?php

namespace AvegaCms\Enums;

enum NavigationTypes: string
{
    case Group   = 'GROUP';
    case Link    = 'LINK';
    case Button  = 'BUTTON';
    case Divider = 'DIVIDER';
    
    /**
     * @param  string|null  $key
     * @return array
     */
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true]) ?
            array_column(NavigationTypes::cases(), $key) : NavigationTypes::cases();
    }
}