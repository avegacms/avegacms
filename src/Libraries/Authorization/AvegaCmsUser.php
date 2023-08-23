<?php

declare(strict_types=1);

namespace AvegaCms\Libraries\Authorization;


class AvegaCmsUser
{
    private static object|null $access   = null;
    private static object|null $userData = null;

    /**
     * @return object|null
     */
    public static function data(): object|null
    {
        return self::$userData;
    }

    /**
     * @return object|null
     */
    public static function permission(): object|null
    {
        return self::$access;
    }

    /**
     * @param  string  $key
     * @param  object|null  $value
     * @return void
     */
    public static function set(string $key, ?object $value = null): void
    {
        match ($key) {
            'user'       => self::$userData = $value ?? null,
            'permission' => self::$access = $value ?? null
        };
    }
}