<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Cast;

use CodeIgniter\DataCaster\Cast\BaseCast;

class CmsFileCast extends BaseCast
{
    // Массив для хранения значений
    private static array $fileId = [];

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
            foreach ($value as $id) {
                self::$fileId[] = (int) $id;
            }
            return (array) $value;
        } else {
            self::$fileId[] = $value;
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

    // Метод для получения всех сохраненных значений
    public static function getFilesId(): array
    {
        return self::$fileId;
    }

    // Метод для сброса массива значений
    public static function resetFilesId(): void
    {
        self::$fileId = [];
    }
}