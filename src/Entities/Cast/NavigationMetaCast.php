<?php

namespace AvegaCms\Entities\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

class NavigationMetaCast extends BaseCast
{
    public static function get($value, array $params = [])
    {
        return base64_decode($value, true);
    }

    public static function set($value, array $params = [])
    {
        return base64_encode($value);
    }
}
