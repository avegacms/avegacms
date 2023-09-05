<?php

namespace AvegaCms\Enums;

enum NavigationTypes: string
{
    case Group   = 'GROUP';
    case Link    = 'LINK';
    case Button  = 'BUTTON';
    case Divider = 'DIVIDER';

    public static function getValues(): array
    {
        return array_column(NavigationTypes::cases(), 'value');
    }
}