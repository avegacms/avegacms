<?php

namespace AvegaCms\Commands\Generators;

use AvegaCms\Config\AvegaCms;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;

class AvegaCmsModelGenerator extends BaseCommand
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
    protected $name = 'avegacms:model';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a new model file extended from AvegaCmsModel.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'avegacms:model <name> [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'name' => 'The model class name.',
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--table'     => 'Supply a table name. Default: "the lowercased plural of the class name".',
        '--dbgroup'   => 'Database group to use. Default: "default".',
        '--return'    => 'Return type, Options: [array, object, entity]. Default: "array".',
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--suffix'    => 'Append the component title to the class name (e.g. User => UserModel).',
        '--force'     => 'Force overwrite existing file.',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params): void
    {
        $this->component = 'Model';
        $this->directory = 'Models';
        $this->template  = 'avegacmsmodel.tpl.php';

        $this->classNameLang = 'CLI.generator.className.model';
        $this->generateClass($params);
    }

    /**
     * Prepare options and do the necessary replacements.
     */
    protected function prepare(string $class): string
    {
        $table   = $this->getOption('table');
        $dbGroup = $this->getOption('dbgroup');
        $return  = $this->getOption('return');

        $baseClass = class_basename($class);

        if (preg_match('/^(\S+)Model$/i', $baseClass, $match) === 1) {
            $baseClass = $match[1];
        }

        $table   = is_string($table) ? $table : plural(strtolower($baseClass));
        $dbGroup = is_string($dbGroup) ? $dbGroup : 'default';
        $return  = is_string($return) ? $return : 'array';

        if (! in_array($return, ['array', 'object', 'entity'], true)) {
            // @codeCoverageIgnoreStart
            $return = CLI::prompt(lang('CLI.generator.returnType'), ['array', 'object', 'entity'], 'required');
            CLI::newLine();
            // @codeCoverageIgnoreEnd
        }

        if ($return === 'entity') {
            $return = str_replace('Models', 'Entities', $class);

            if (preg_match('/^(\S+)Model$/i', $return, $match) === 1) {
                $return = $match[1];

                if ($this->getOption('suffix')) {
                    $return .= 'Entity';
                }
            }

            $return = '\\' . trim($return, '\\') . '::class';
            $this->call('make:entity', array_merge([$baseClass], $this->params));
        } else {
            $return = "'{$return}'";
        }

        return $this->parseTemplate($class, ['{table}', '{dbGroup}', '{return}'], [$table, $dbGroup, $return]);
    }
}
