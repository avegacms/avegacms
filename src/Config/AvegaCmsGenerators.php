<?php

namespace AvegaCms\Config;

class AvegaCmsGenerators
{
    public static $views = [
        'avegacms:controller' => 'AvegaCms\Commands\Generators\Views\avegacmscontroller.tpl.php',
        'avegacms:model'      => 'AvegaCms\Commands\Generators\Views\avegacmsmodel.tpl.php',
    ];
}
