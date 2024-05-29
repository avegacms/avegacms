<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Cast;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\DataCaster\Cast\BaseCast;
use InvalidArgumentException;

class CmsDatetimeCast extends BaseCast
{
    public static function get(mixed $value, array $params = [], ?object $helper = null): string
    {
        if ( ! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        if ( ! $helper instanceof BaseConnection) {
            throw new InvalidArgumentException('The parameter $helper must be BaseConnection.');
        }

        $format = match ($params[0] ?? '') {
            ''      => $helper->dateFormat['datetime'],
            'ms'    => $helper->dateFormat['datetime-ms'],
            'us'    => $helper->dateFormat['datetime-us'],
            default => throw new InvalidArgumentException('Invalid parameter: ' . $params[0]),
        };

        return date($format, strtotime($value));
    }

    public static function set(mixed $value, array $params = [], ?object $helper = null): string
    {
        if ( ! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        if ( ! $helper instanceof BaseConnection) {
            throw new InvalidArgumentException('The parameter $helper must be BaseConnection.');
        }

        return date($helper->dateFormat['datetime'], strtotime($value));
    }
}