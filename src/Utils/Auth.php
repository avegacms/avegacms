<?php

namespace AvegaCms\Utils;

class Auth
{
    /**
     * @param  string  $pass
     * @return string
     */
    public static function setPassword(string $pass): string
    {
        return password_hash($pass, PASSWORD_DEFAULT);
    }
}