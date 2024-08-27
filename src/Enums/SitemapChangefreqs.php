<?php

declare(strict_types=1);

namespace AvegaCms\Enums;

enum SitemapChangefreqs: string
{
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true], true) ?
            array_column(SitemapChangefreqs::cases(), $key) :
            SitemapChangefreqs::cases();
    }

    public static function fromName(string $name): string
    {
        foreach (SitemapChangefreqs::cases() as $enum) {
            if ($enum->name === $name) {
                return $enum->value;
            }
        }

        return '';
    }

    public static function list(): array
    {
        $list = [];

        foreach (SitemapChangefreqs::cases() as $enum) {
            $list[] = ['label' => $enum->value, 'value' => $enum->name];
        }

        return $list;
    }
    case Always  = 'ALWAYS';
    case Hourly  = 'HOURLY';
    case Daily   = 'DAYLI';
    case Weekly  = 'WEEKLY';
    case Monthly = 'MONTHLY';
    case Yearly  = 'YEARLY';
    case Never   = 'NEVER';
}
