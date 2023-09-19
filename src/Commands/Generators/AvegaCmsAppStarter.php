<?php

namespace AvegaCms\Commands\Generators;

use AvegaCms\Config\AvegaCms;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\DotEnv;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\GeneratorTrait;
use CodeIgniter\Encryption\Encryption;
use ReflectionException;

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
     * @param  array  $params
     * @return void
     * @throws ReflectionException
     */
    public function run(array $params): void
    {
        if (is_file(ROOTPATH . '.env')) {
            CLI::error('File .env already exists', 'light_gray', 'red');
            CLI::newLine();
            return;
        }

        if ($this->createNewEnvironmentFile()) {
            $this->call('php spark migrate --all');
            $this->call('php spark db:seed AvegaCms\\Database\\Seeds\\AvegaCmsInstallSeeder');
        }
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

        if (empty($env['db']['dbDriver'] = CLI::prompt('Set your database.DBDriver (by default MySQLi)', null,
            ['permit_empty']))) {
            $env['db']['dbDriver'] = 'MySQLi';
        }

        if (empty($env['db']['dbPrefix'] = CLI::prompt('Set your database.DBPrefix (by default empty)', null,
            ['permit_empty']))) {
            $env['db']['dbPrefix'] = '';
        }

        if (empty($env['db']['port'] = CLI::prompt('Set your database.port (by default 3306)', null,
            ['permit_empty', 'is_natural']))) {
            $env['db']['port'] = 3306;
        }

        $env['encryption'] = 'hex2bin:' . bin2hex(Encryption::createKey());

        $env['logger'] = ($env['environment'] === 'production') ? 4 : 9;

        $writeResult = file_put_contents(
                ROOTPATH . '.env',
                str_replace(
                    [
                        "# CI_ENVIRONMENT = production",
                        "# app.baseURL = ''",
                        '# database.default.hostname = localhost',
                        '# database.default.database = ci4',
                        '# database.default.username = root',
                        '# database.default.password = root',
                        '# database.default.DBDriver = MySQLi',
                        '# database.default.DBPrefix =',
                        '# database.default.port = 3306',
                        '# encryption.key =',
                        '# logger.threshold = 4'
                    ],
                    [
                        'CI_ENVIRONMENT = ' . $env['environment'],
                        "app.baseURL = '" . $env['baseURL'] . "'",
                        "database.default.hostname = '" . $env['db']['hostname'] . "'",
                        'database.default.database = ' . $env['db']['database'],
                        'database.default.username = ' . $env['db']['username'],
                        'database.default.password = ' . $env['db']['password'],
                        'database.default.DBDriver = ' . $env['db']['dbDriver'],
                        'database.default.dbPrefix = ' . $env['db']['dbPrefix'],
                        'database.default.port = ' . $env['db']['port'],
                        'encryption.key = ' . $env['encryption'],
                        'logger.threshold = ' . $env['logger']
                    ],
                    file_get_contents(ROOTPATH . 'env'),
                    $count
                )
            ) !== false && $count > 0;

        if ( ! $writeResult) {
            CLI::error('Failed to create .env file', 'light_gray', 'red');
            CLI::newLine();

            return false;
        }

        (new DotEnv(ROOTPATH))->load();

        return true;
    }
}