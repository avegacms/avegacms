<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Utilities\Auth;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Validation\ValidationInterface;
use Faker\Generator;
use AvegaCms\Enums\UserStatuses;
use AvegaCms\Utilities\Cms;
use ReflectionException;

class UserModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'login',
        'avatar',
        'phone',
        'email',
        'timezone',
        'password',
        'secret',
        'path',
        'expires',
        'is_verified',
        'profile',
        'extra',
        'status',
        'condition',
        'last_ip',
        'last_agent',
        'created_by_id',
        'updated_by_id',
        'active_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    //AvegaCms model settings
    public array  $filterFields      = [
        'id'     => 'id',
        'login'  => 'login',
        'phone'  => 'phone',
        'email'  => 'email',
        'status' => 'status',
    ];
    public array  $searchFields      = [
        'login',
        'phone',
        'email'
    ];
    public array  $sortableFields    = [];
    public array  $filterCastsFields = [
        'id'     => 'int|array',
        'login'  => 'string',
        'phone'  => 'integer',
        'email'  => 'string',
        'status' => 'string',
    ];
    public string $searchFieldAlias  = 'q';
    public string $sortFieldAlias    = 's';
    public int    $limit             = 20;
    public int    $maxLimit          = 100;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'id'       => ['rules' => 'if_exist|is_natural_no_zero'],
        'login'    => ['rules' => 'if_exist|required|alpha_dash|max_length[36]'],
        'avatar'   => ['rules' => 'if_exist|is_natural'],
        'phone'    => ['rules' => 'if_exist|is_natural|mob_phone'],
        'email'    => ['rules' => 'if_exist|max_length[255]|valid_email'],
        'timezone' => ['rules' => 'if_exist|required|max_length[144]'],
        'password' => ['rules' => 'if_exist|required|verify_password'],
        'path'     => ['rules' => 'if_exist|permit_empty|max_length[512]'],
        'profile'  => ['rules' => 'if_exist|permit_empty'],
        'extra'    => ['rules' => 'if_exist|permit_empty']
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['hashPassword'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected array $casts = [
        'id'            => 'int',
        'avatar'        => 'int',
        'expires'       => 'int',
        'is_verified'   => '?int-bool',
        'profile'       => '?json-array',
        'extra'         => '?json-array',
        'created_by_id' => 'int',
        'updated_by_id' => 'int',
        'active_at'     => 'cmsdatetime',
        'created_at'    => 'cmsdatetime',
        'updated_at'    => 'cmsdatetime',
        'deleted_at'    => 'cmsdatetime'
    ];

    /**
     * @param  ConnectionInterface|null  $db
     * @param  ValidationInterface|null  $validation
     * @throws ReflectionException
     */
    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);

        $this->validationRules['status'] = 'if_exist|in_list[' . implode(',', UserStatuses::get('value')) . ']';

        $settings = Cms::settings('core.auth.loginType');

        $loginType = explode(':', $settings);

        foreach ($loginType as $type) {
            if (isset($this->validationRules[$type])) {
                $this->validationRules[$type]['rules'] = match ($type) {
                    'login' => $this->validationRules[$type]['rules'] . '|required|is_unique[users.login,id,{id}]',
                    'email' => $this->validationRules[$type]['rules'] . '|required|is_unique[users.email,id,{id}]',
                    'phone' => $this->validationRules[$type]['rules'] . '|required|is_unique[users.phone,id,{id}]'
                };
            }
        }

        unset($settings, $loginType);
    }

    /**
     * @param  int  $id
     * @return array|object|null
     */
    public function forEdit(int $id): array|object|null
    {
        $this->builder()->select(
            [
                'id',
                'login',
                'avatar',
                'phone',
                'email',
                'timezone',
                'password',
                'path',
                'is_verified',
                'profile',
                'extra',
                'status'
            ]
        );

        return $this->find($id);
    }

    /**
     * @param  array  $data
     * @return array
     */
    protected function hashPassword(array $data): array
    {
        if (empty($data['data']['password'] ?? '')) {
            return $data;
        }
        $data['data']['password'] = Auth::setPassword($data['data']['password']);
        return $data;
    }

    /**
     * @param  Generator  $faker
     * @return array
     */
    public function fake(Generator &$faker): array
    {
        $statuses = UserStatuses::get('value');

        return [
            'login'         => $faker->word() . '_' . $faker->word(),
            'email'         => $faker->email(),
            'phone'         => '79' . rand(100000000, 999999999),
            'status'        => $statuses[array_rand($statuses)],
            'extra'         => [],
            'active_at'     => $faker->dateTimeBetween('-1 week', 'now', 'Asia/Omsk')->format('Y-m-d H:i:s'),
            'created_by_id' => 1
        ];
    }
}
