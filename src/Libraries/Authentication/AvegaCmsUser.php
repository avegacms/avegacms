<?php

declare(strict_types=1);

namespace AvegaCms\Libraries\Authentication;


class AvegaCmsUser
{
    private static $access   = null;
    private static $userData = null;

    /**
     * @return object|null
     */
    public static function data(): object
    {
        return self::$userData;
    }


    /**
     * @return object|null
     */
    public static function permission(): object
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