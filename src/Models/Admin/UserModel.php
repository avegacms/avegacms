<?php

declare(strict_types=1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Enums\UserStatuses;
use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Utilities\Auth;
use AvegaCms\Utilities\Cms;
use Faker\Generator;
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
        'attempts',
        'profile',
        'extra',
        'status',
        'condition',
        'last_ip',
        'last_agent',
        'created_by_id',
        'updated_by_id',
        'active_at',
        'blocked_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // AvegaCms model settings
    public array $filterFields = [
        'id'     => 'id',
        'login'  => 'login',
        'phone'  => 'phone',
        'email'  => 'email',
        'status' => 'status',
    ];
    public array $searchFields = [
        'login',
        'phone',
        'email',
    ];
    public array $sortableFields    = [];
    public array $filterCastsFields = [
        'id'     => 'int|array',
        'login'  => 'string',
        'phone'  => 'integer',
        'email'  => 'string',
        'status' => 'string',
    ];
    public string $searchFieldAlias = 'q';
    public string $sortFieldAlias   = 's';
    public int $limit               = 20;
    public int $maxLimit            = 100;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'id'       => ['rules' => 'if_exist|is_natural_no_zero'],
        'login'    => ['rules' => 'if_exist|required|alpha_dash|max_length[36]'],
        'avatar'   => ['rules' => 'if_exist|is_natural'],
        'phone'    => ['rules' => 'if_exist|is_natural|mob_phone'],
        'email'    => ['rules' => 'if_exist|max_length[255]|valid_email'],
        'timezone' => ['rules' => 'if_exist|required|max_length[144]'],
        'password' => ['rules' => 'if_exist|required|verify_password'],
        'path'     => ['rules' => 'if_exist|permit_empty|max_length[512]'],
        'profile'  => ['rules' => 'if_exist|permit_empty'],
        'extra'    => ['rules' => 'if_exist|permit_empty'],
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
    protected array $casts    = [
        'id'            => 'int',
        'avatar'        => 'int',
        'expires'       => 'int',
        'attempts'      => 'int',
        'is_verified'   => '?int-bool',
        'profile'       => '?json-array',
        'extra'         => '?json-array',
        'created_by_id' => 'int',
        'updated_by_id' => 'int',
        'active_at'     => 'cmsdatetime',
        'blocked_at'    => '?cmsdatetime',
        'created_at'    => 'cmsdatetime',
        'updated_at'    => 'cmsdatetime',
        'deleted_at'    => 'cmsdatetime',
    ];

    /**
     * @throws ReflectionException
     */
    public function __construct()
    {
        parent::__construct();

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

    public function forEdit(int $id): array|object|null
    {
        $this->builder()->select(
            [
                'users.id',
                'users.email',
                'users.status',
                'user_roles.role_id',
            ]
        )->join(
            'user_roles',
            'user_roles.user_id = users.id'
        )->join(
            'roles',
            'roles.id = user_roles.role_id'
        )->where(['roles.module_id' => 0]);

        return $this->find($id);
    }

    protected function hashPassword(array $data): array
    {
        if (empty($data['data']['password'] ?? '')) {
            return $data;
        }
        $data['data']['password'] = Auth::setPassword($data['data']['password']);

        return $data;
    }

    public function fake(Generator &$faker): array
    {
        $statuses = UserStatuses::get('value');

        return [
            'login'         => $faker->word() . '_' . $faker->word(),
            'email'         => $faker->email(),
            'phone'         => '79' . mt_rand(100000000, 999999999),
            'status'        => $statuses[array_rand($statuses)],
            'extra'         => [],
            'active_at'     => $faker->dateTimeBetween('-1 week', 'now', 'Asia/Omsk')->format('Y-m-d H:i:s'),
            'created_by_id' => 1,
        ];
    }
}
