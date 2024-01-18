<?php

namespace AvegaCms\Commands\Generators;

use AvegaCms\Config\AvegaCms;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;

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
    protected $description = '';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'avegacms:sitemap [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--force' => 'Force overwrite existing file.',
    ];

    /**
     * Actually execute a command.
     *
     * @param  array  $params
     */
    public function run(array $params)
    {
        $this->component = 'Controller';
        $this->directory = 'Controllers';
        $this->template  = 'avegacmssitemapcontroller.tpl.php';

        $this->classNameLang = 'CLI.generator.className.controller';

        $this->generateClass($params);
    }

    protected function prepare(string $class): string
    {
        if (empty($class)) {
            CLI::error(lang('Generator.error.controller.sitemap'), 'light_gray', 'red');
            CLI::newLine();
            exit();
        }
        return $this->parseTemplate($class);
    }
}
