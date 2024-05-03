<?php

declare(strict_types = 1);

namespace AvegaCms\Enums;

enum SitemapChangefreqs: string
{
    case Always  = 'ALWAYS';
    case Hourly  = 'HOURLY';
    case Daily   = 'DAYLI';
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
            array_column(SitemapChangefreqs::cases(), $key) :
            SitemapChangefreqs::cases();
    }

    /**
     * @param  string  $name
     * @return string
     */
    public static function fromName(string $name): string
    {
        foreach (SitemapChangefreqs::cases() as $enum) {
            if ($enum->name === $name) {
                return $enum->value;
            }
        }
        return '';
    }

    /**
     * @return array
     */
    public static function list(): array
    {
        $list = [];

        foreach (SitemapChangefreqs::cases() as $enum) {
            $list[] = ['label' => $enum->value, 'value' => $enum->name];
        }

        return $list;
    }
}
