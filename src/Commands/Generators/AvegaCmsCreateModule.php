<?php

namespace AvegaCms\Commands\Generators;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;
use AvegaCms\Config\AvegaCms;

class AvegaCmsCreateModule extends BaseCommand
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
    protected $name = 'avegacms:module';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a new folder for module (without code only folders)';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'avegacms:module <name>';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'name' => 'The module name.',
    ];

    /**
     * @var array
     */
    protected array $moduleFolder = [
        'Config'      => 'Routes.php',
        'Controllers' => [
            'Api' => [
                'Admin'  => '.gitkeep',
                'Public' => '.gitkeep'
            ]
        ],
        'Database'    => [
            'Migrations' => '.gitkeep',
            'Seeds'      => '.gitkeep'
        ],
        'Entities'    => '.gitkeep',
        'Filters'     => '.gitkeep',
        'Helpers'     => '.gitkeep',
        'Language'    => '.gitkeep',
        'Libraries'   => '.gitkeep',
        'Models'      => [
            'Admin'    => '.gitkeep',
            'Frontend' => '.gitkeep'
        ],
        'Views'       => '.gitkeep'
    ];

    /**
     * @inheritDoc
     */
    public function run(array $params)
    {
        $module = $params[0];

        $this->createFolders($this->moduleFolder, ROOTPATH . 'modules' . DIRECTORY_SEPARATOR . ucfirst($module));
    }

    protected function createFolders(array $folders, string $path)
    {
        foreach ($folders as $folder => $item) {
            if (mkdir($file = $path . DIRECTORY_SEPARATOR . $folder, 0777, true)) {
                if (is_array($item)) {
                    $this->createFolders($item, $file);
                } else {
                    $file = fopen($file . DIRECTORY_SEPARATOR . $item, 'x+b');
                    fclose($file);
                }
            }
        }
    }
}