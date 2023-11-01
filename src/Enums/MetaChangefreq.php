<?php

namespace AvegaCms\Enums;

enum MetaChangefreq: string
{
    case Always  = 'ALWAYS';
    case Hourly  = 'HOURLY';
    case Daily   = 'DAILY';
    case Weekly  = 'WEEKLY';
    case Monthly = 'MONTHLY';
    case Yearly  = 'YEARLY';
    case Never   = 'NEVER';

    /**
     * @param  string|null  $key
     * @return array
     */
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true]) ?
            array_column(MetaChangefreq::cases(), $key) : MetaChangefreq::cases();
    }
}
