<?php

declare(strict_types=1);

namespace AvegaCms\Commands\Generators;

use AvegaCms\Config\AvegaCms;
use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use AvegaCms\Controllers\Api\AvegaCmsAPI;
use AvegaCms\Controllers\AvegaCmsFrontendController;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;

class AvegaCmsControllerGenerator extends BaseCommand
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
    protected $name = 'avegacms:controller';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a new cms controller file.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'avegacms:controller <name> [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'name' => 'The controller class name.',
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--suffix'    => 'Append the component title to the class name (e.g. User => UserController).',
        '--force'     => 'Force overwrite existing file.',
    ];

    public function run(array $params): void
    {
        $this->component = 'Controller';
        $this->directory = 'Controllers';
        $this->template  = 'avegacmscontroller.tpl.php';

        $this->classNameLang = 'CLI.generator.className.controller';

        $this->generateClass($params);
    }

    protected function prepare(string $class): string
    {
        if (count($classPath = explode('\\', $class)) >= 3) {
            if (in_array('Api', $classPath, true) && empty(array_intersect($classPath, ['Public', 'Admin']))) {
                CLI::error(lang('Generator.error.controller.folderNotFound'), 'light_gray', 'red');
                CLI::newLine();

                exit();
            }
        }

        $type   = in_array('Api', $classPath, true) ? 'api' : 'controller';
        $access = in_array('Admin', $classPath, true) ? 'admin' : 'public';

        $useStatement = AvegaCmsFrontendController::class;
        $extends      = 'AvegaCmsFrontendController';

        if ($type === 'controller' && $access === 'admin') {
            CLI::error(lang('CLI.commandNotFound', [$access]), 'light_gray', 'red');
            CLI::newLine();

            exit();
        }
        if ($type === 'api') {
            if ($access === 'admin') {
                $useStatement = AvegaCmsAdminAPI::class;
                $extends      = 'AvegaCmsAdminAPI';
            } else {
                $useStatement = AvegaCmsAPI::class;
                $extends      = 'AvegaCmsAPI';
            }
        }

        return $this->parseTemplate(
            $class,
            ['{useStatement}', '{extends}'],
            [$useStatement, $extends],
            ['type' => $type, 'access' => $access]
        );
    }
}
