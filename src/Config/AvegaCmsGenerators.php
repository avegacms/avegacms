<?php

namespace AvegaCms\Config;

class AvegaCmsGenerators
{
    public static array $views = [
        'avegacms:controller' => 'AvegaCms\Commands\Generators\Views\avegacmscontroller.tpl.php',
        'avegacms:factory'    => 'AvegaCms\Commands\Generators\Views\avegacmsfactory.tpl.php',
        'avegacms:model'      => 'AvegaCms\Commands\Generators\Views\avegacmsmodel.tpl.php',
        'avegacms:migration'  => 'AvegaCms\Commands\Generators\Views\avegacmsmigration.tpl.php',
    ];
}
