<?php

namespace AvegaCms\Commands\Generators;

use AvegaCms\Config\AvegaCms;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\DotEnv;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\GeneratorTrait;
use CodeIgniter\Encryption\Encryption;
use phpDocumentor\Reflection\Types\Self_;

class AvegaCmsAppStarter extends BaseCommand
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
    protected $description = 'Installing AvegaCms';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'avegacms:appstart';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'name' => 'The controller class name.',
    ];

    /**
     * @var array|string[]
     */
    private static array $envTypes = [
        'production',
        'development',
    ];


    /**
     * @inheritDoc
     */
    public function run(array $params)
    {
        // TODO: Сделать проверку на существующую установку cms

        $this->createNewEnvironmentFile();
    }

    private function createNewEnvironmentFile(): bool
    {
        CLI::write('Создание Env файла:', 'yellow');
        CLI::newLine();

        $env['environment'] = self::$envTypes[CLI::promptByKey('Set environment:', self::$envTypes,
            ['required', 'in_list[0,1]'])];

        $env['baseURL'] = CLI::prompt('Set your app.baseURL', null, ['required', 'valid_url']);

        if (empty($env['db']['hostname'] = CLI::prompt('Set your database.hostname (by default localhost)', null,
            ['permit_empty']))) {
            $env['db']['hostname'] = 'localhost';
        }

        $env['db']['database'] = CLI::prompt('Set your database.database', null, ['required']);
        $env['db']['username'] = CLI::prompt('Set your database.username', null, ['required']);
        $env['db']['password'] = CLI::prompt('Set your database.password', null, ['required']);

        if (empty($env['db']['driver'] = CLI::prompt('Set your database.DBDriver (by default MySQLi)', null,
            ['permit_empty']))) {
            $env['db']['driver'] = 'MySQLi';
        }

        if ( ! empty($dbprefix = CLI::prompt('Set your database.DBPrefix (by default empty)', null,
            ['permit_empty']))) {
            $env['db']['dbprefix'] = $dbprefix;
        }

        if ( ! empty($dbprefix = CLI::prompt('Set your database.port (by default 3306)', null,
            ['permit_empty']))) {
            $env['db']['port'] = $dbprefix;
        }

        $env['encryption'] = 'hex2bin:' . bin2hex(Encryption::createKey());

        $env['logger'] = ($env['environment'] === 'production') ? 4 : 9;

        dd($env);

        return true;

        $baseEnv = ROOTPATH . 'env';
        $envFile = ROOTPATH . '.env';

        if ( ! is_file($envFile)) {
            if ( ! is_file($baseEnv)) {
                CLI::write('Both default shipped `env` file and custom `.env` are missing.', 'yellow');
                CLI::write('It is impossible to write the new environment type.', 'yellow');
                CLI::newLine();

                return false;
            }

            copy($baseEnv, $envFile);
        }

        dd($envFile);
        //return true;
    }
}