<?php

namespace AvegaCms\Commands\Generators;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;
use CodeIgniter\Controller;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\RESTful\ResourcePresenter;
use AvegaCms\Controllers\Api\CmsResourceController;

use function _PHPStan_8b0bfd44f\RingCentral\Psr7\str;

class AvegaCmsControllerGenerator extends BaseCommand
{
    use GeneratorTrait;

    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'AvegaCMS';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'makecms:controller';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a new controller file.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'makecms:controller <name> [options]';

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
        //'--bare'      => 'Extends from CodeIgniter\Controller instead of BaseController.',
        '--restful' => 'Extends from a RESTful resource, Options: [controller, cmsapi]. Default: "controller".',

        '--type'      => 'Controller type, options [controller, api]. Default: "controller".',
        '--access'    => 'Controller access type, options [public, admin]. Default: "public".',
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--suffix'    => 'Append the component title to the class name (e.g. User => UserController).',
        '--force'     => 'Force overwrite existing file.',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $this->component = 'Controller';
        $this->directory = 'Controllers';
        $this->template = 'avegacmscontroller.tpl.php';

        $this->classNameLang = 'CLI.generator.className.controller';
        $this->generateClass($params);
    }

    /**
     * @param  string  $class
     * @return string
     */
    protected function prepare(string $class): string
    {
        if (($type = $this->getOption('type')) === true) {
            $type = 'controller';
        }

        if (($access = $this->getOption('type')) === true) {
            $access = 'public';
        }

        if ( ! in_array($type, ['controller', 'api'], true)) {
            // @codeCoverageIgnoreStart
            $rest = CLI::prompt(lang('CLI.generator.parentClass'), ['controller', 'api'], 'required');
            CLI::newLine();
            // @codeCoverageIgnoreEnd
        }

        if ( ! in_array($access, ['public', 'admin'], true)) {
            // @codeCoverageIgnoreStart
            $rest = CLI::prompt(lang('CLI.generator.parentClass'), ['public', 'admin'], 'required');
            CLI::newLine();
            // @codeCoverageIgnoreEnd
        }

        if ($type === 'api') {
            if ($access === 'admin') {
            } else {
            }
        } else {
            if ($access === 'public') {
            } else {
                //error
            }
        }

        return $this->parseTemplate(
            $class,
            ['{useStatement}', '{extends}'],
            [$useStatement, $extends],
            ['type' => $rest]
        );
    }

    /**
     * Prepare options and do the necessary replacements.
     */
    protected function prepare1(string $class): string
    {
        $bare = $this->getOption('bare');
        $rest = $this->getOption('restful');

        $useStatement = trim(APP_NAMESPACE, '\\') . '\Controllers\BaseController';
        $extends = 'BaseController';

        // Gets the appropriate parent class to extend.
        if ($bare || $rest) {
            if ($bare) {
                $useStatement = Controller::class;
                $extends = 'Controller';
            } elseif ($rest) {
                $rest = is_string($rest) ? $rest : 'controller';

                if ( ! in_array($rest, ['controller', 'cmsapi', 'presenter'], true)) {
                    // @codeCoverageIgnoreStart
                    $rest = CLI::prompt(lang('CLI.generator.parentClass'), ['controller', 'cmsapi', 'presenter'],
                        'required');
                    CLI::newLine();
                    // @codeCoverageIgnoreEnd
                }


                if ($rest === 'controller') {
                    $useStatement = ResourceController::class;
                    $extends = 'ResourceController';
                } elseif ($rest === 'cmsapi') {
                    $useStatement = CmsResourceController::class;
                    $extends = 'CmsResourceController';
                } elseif ($rest === 'presenter') {
                    $useStatement = ResourcePresenter::class;
                    $extends = 'ResourcePresenter';
                }
            }
        }

        return $this->parseTemplate(
            $class,
            ['{useStatement}', '{extends}'],
            [$useStatement, $extends],
            ['type' => $rest]
        );
    }
}