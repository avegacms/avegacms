<?php

declare(strict_types = 1);

namespace AvegaCms\Commands\Generators;

use AvegaCms\Config\AvegaCms;
use AvegaCms\Libraries\Sitemap\Sitemap;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;
use Exception;

class AvegaCmsSitemapGenerator extends BaseCommand
{
    use GeneratorTrait;

    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'AvegaCMS (v ' . AvegaCms::AVEGACMS_VERSION . ')';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'avegacms:sitemap';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a sitemap controller file for module.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'avegacms:sitemap [module] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'moduleName' => 'Название модуля',
        'parameter'  => 'Параметр для генератора Sitemap'
    ];

    /**
     * Actually execute a command.
     *
     * @param  array  $params
     * @throws Exception
     */
    public function run(array $params): void
    {
        $moduleName = $params[0] ?? null;
        $parameter  = $params[1] ?? null;

        if (is_null($moduleName)) {
            CLI::write("Глобальная генерация Sitemap", 'green');
        } else {
            CLI::write("Генерация Sitemap для модуля: {$moduleName}", 'green');
        }

        (new Sitemap($moduleName, $parameter))->run();
    }
}
