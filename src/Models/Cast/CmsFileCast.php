<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Cast;

use CodeIgniter\DataCaster\Cast\BaseCast;

class CmsFileCast extends BaseCast
{
    /**
     * @param  mixed  $value
     * @param  array  $params
     * @param  object|null  $helper
     * @return array|int
     */
    public static function get(mixed $value, array $params = [], ?object $helper = null): array|int
    {
        if ( ! is_string($value)) {
            self::invalidTypeValueError($value);
        }

        if ((str_starts_with($value, 'a:') || str_starts_with($value, 's:'))) {
            $value = unserialize($value, ['allowed_classes' => false]);
            foreach ($value as &$id) {
                $id = (int) $id;
            }
            return (array) $value;
        } else {
            return (int) $value;
        }
    }

    /**
     * @param  mixed  $value
     * @param  array  $params
     * @param  object|null  $helper
     * @return int|string
     */
    public static function set(mixed $value, array $params = [], ?object $helper = null): int|string
    {
        return is_array($value) ? serialize($value) : (int) $value;
    }
}