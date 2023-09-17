<?php

namespace AvegaCms\Utils;

class Email
{
    /**
     * @param  string  $template
     * @param  array  $to
     * @param  array  $data
     * @param  array  $attach
     * @param  array  $config
     * @return bool
     */
    public static function send(
        string $template,
        array $to,
        int $locale = 0,
        array $data = [],
        array $attach = [],
        array $config = []
    ): bool {
        helper(['avegacms']);
        $defConfig = settings('core.email');
    }
}