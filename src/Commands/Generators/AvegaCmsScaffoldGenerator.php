<?php

namespace AvegaCms\Commands\Generators;

use AvegaCms\Config\AvegaCms;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;

class AvegaCmsScaffoldGenerator extends BaseCommand
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
    protected $name = 'avegacms:scaffold';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a complete avegacms set of scaffold files.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'avegacms:scaffold <name> [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'name' => 'The class name',
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--bare'      => 'Add the "--bare" option to controller component.',
        '--restful'   => 'Add the "--restful" option to controller component.',
        '--table'     => 'Add the "--table" option to the model component.',
        '--dbgroup'   => 'Add the "--dbgroup" option to model component.',
        '--return'    => 'Add the "--return" option to the model component.',
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--suffix'    => 'Append the component title to the class name.',
        '--force'     => 'Force overwrite existing file.',
        '--stirct'    => 'Set PHP strict model',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $this->params = $params;

        $options = [];

        if ($this->getOption('namespace')) {
            $options['namespace'] = $this->getOption('namespace');
        }

        if ($this->getOption('suffix')) {
            $options['suffix'] = null;
        }

        if ($this->getOption('force')) {
            $options['force'] = null;
        }

        $controllerOpts = [];

        if ($this->getOption('bare')) {
            $controllerOpts['bare'] = null;
        } elseif ($this->getOption('restful')) {
            $controllerOpts['restful'] = $this->getOption('restful');
        }

        $modelOpts = [
            'table'   => $this->getOption('table'),
            'dbgroup' => $this->getOption('dbgroup'),
            'return'  => $this->getOption('return'),
        ];

        $class = $params[0] ?? CLI::getSegment(2);

        // Call those commands!
        $this->call('avegacms:controller', array_merge([$class], $controllerOpts, $options));
        $this->call('avegacms:model', array_merge([$class], $modelOpts, $options));
        $this->call('make:migration', array_merge([$class], $options));
        $this->call('make:seeder', array_merge([$class], $options));
    }
}