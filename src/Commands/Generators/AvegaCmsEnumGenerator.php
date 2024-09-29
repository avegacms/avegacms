<?php

declare(strict_types=1);

namespace AvegaCms\Commands\Generators;

use AvegaCms\Config\AvegaCms;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\GeneratorTrait;

class AvegaCmsEnumGenerator extends BaseCommand
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
    protected $name = 'avegacms:enum';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a new Enum file extended from.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'avegacms:enum [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'name' => 'The Enum class name.',
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--suffix'    => 'Append the component title to the class name (e.g. User => UserModel).',
        '--force'     => 'Force overwrite existing file.',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params): void
    {
        $this->component = 'Enum';
        $this->directory = 'Enums';
        $this->template  = 'avegacmsenum.tpl.php';

        $this->generateClass($params);
    }
}
