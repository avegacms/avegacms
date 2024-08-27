<?php

namespace AvegaCms\Commands\Generators;

use AvegaCms\Config\AvegaCms;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\GeneratorTrait;

class AvegaCmsFactoryGenerator extends BaseCommand
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
    protected $name = 'avegacms:factory';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a new Factory Class file.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'avegacms:factory <name> [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'name' => 'The Factory class name.',
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--suffix'    => 'Append the component title to the class name (e.g. User => UsersFactory).',
        '--model'     => 'Extended model',
        '--force'     => 'Force overwrite existing file.',
    ];

    public function run(array $params)
    {
        $this->component = 'Factories';
        $this->directory = 'Database\Factories';
        $this->template  = 'avegacmsfactory.tpl.php';

        $this->classNameLang = 'CLI.generator.className.model';
        $this->generateClass($params);
    }

    protected function prepare(string $class): string
    {
        $model     = $this->getOption('model');
        $modelName = explode('/', $model);
        $modelName = end($modelName);
        $namespace = trim(str_replace('/', '\\', $this->getOption('namespace') ?? APP_NAMESPACE), '\\') . '\\';

        $model = trim(str_replace('/', '\\', $model), '\\');

        if (! empty($namespace)) {
            $model = $namespace . 'Models\\' . $model;
        }

        return $this->parseTemplate($class, ['{model}', '{modelName}'], [$model, $modelName]);
    }
}
