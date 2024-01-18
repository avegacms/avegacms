<?php

namespace AvegaCms\Config;

class AvegaCmsGenerators
{
    public static array $views = [
        'avegacms:controller' => 'AvegaCms\Commands\Generators\Views\avegacmscontroller.tpl.php',
        'avegacms:model'      => 'AvegaCms\Commands\Generators\Views\avegacmsmodel.tpl.php',
        'avegacms:migration'  => 'AvegaCms\Commands\Generators\Views\avegacmsmigration.tpl.php',
        'avegacms:sitemap'    => 'AvegaCms\Commands\Generators\Views\avegacmssitemapcontroller.tpl.php',
    ];
}
