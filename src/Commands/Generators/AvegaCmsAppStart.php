<?php

namespace AvegaCms\Commands\Generators;

use CodeIgniter\CLI\BaseCommand;
use AvegaCms\Config\AvegaCms;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;
use ReflectionException;

class AvegaCmsAppStart extends BaseCommand
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
    protected $name = 'avegacms:appstart';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Creating default data';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'avegacms:appstart';

    /**
     * @param  array  $params
     * @throws ReflectionException
     */
    public function run(array $params)
    {
        if ( ! is_file(ROOTPATH . '.env')) {
            CLI::error('File .env not exists', 'light_gray', 'red');
            CLI::newLine();
            return;
        }

        $this->call('migrate --all');
        $this->call('db:seed AvegaCms\\Database\\Seeds\\AvegaCmsInstallSeeder');
        $this->call('db:seed AvegaCms\\Database\\Seeds\\AvegaCmsTestData');
    }
}