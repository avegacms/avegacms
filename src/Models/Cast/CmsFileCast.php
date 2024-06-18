<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Cast;

use CodeIgniter\DataCaster\Cast\BaseCast;

class CmsFileCast extends BaseCast
{
    // Массив для хранения значений
    private static array $values = [];

    public static function get(mixed $value, array $params = [], ?object $helper = null): mixed
    {
        self::$values[] = $value;
        return $value; // Возвращаем значение как есть
    }

    public static function set(mixed $value, array $params = [], ?object $helper = null): int
    {
        return (int) $value; // Просто возвращаем значение без изменений
    }

    // Метод для получения всех сохраненных значений
    public static function getValues(): array
    {
        return self::$values;
    }

    // Метод для сброса массива значений
    public static function resetValues(): void
    {
        self::$values = [];
    }
}