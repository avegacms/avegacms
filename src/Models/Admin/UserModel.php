<?php

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Entities\UserEntity;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Validation\ValidationInterface;
use Faker\Generator;
use AvegaCms\Enums\UserStatuses;
use AvegaCms\Utils\Cms;
use ReflectionException;

class UserModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = UserEntity::class;
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
        'login' => 'login',
        'phone' => 'phone',
        'email' => 'email'
    ];
    public array  $sortableFields    = [];
    public array  $filterCastsFields = [
        'id'     => 'int|array',
        'login'  => 'string',
        'avatar' => 'string',
        'phone'  => 'int',
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
        'login'    => ['rules' => 'if_exist|required|alpha_dash|max_length[36]|is_unique[users.login,id,{id}]'],
        'avatar'   => ['rules' => 'if_exist|permit_empty|max_length[255]'],
        'phone'    => ['rules' => 'if_exist|is_natural|exact_length[11]|regex_match[/^79\d{9}/]'],
        'email'    => ['rules' => 'if_exist|max_length[255]|valid_email'],
        'timezone' => ['rules' => 'if_exist|required|max_length[144]'],
        'password' => ['rules' => 'if_exist|required|verifyPassword'],
        'path'     => ['rules' => 'if_exist|permit_empty|max_length[512]'],
        'extra'    => ['rules' => 'if_exist|permit_empty']
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * @param  ConnectionInterface|null  $db
     * @param  ValidationInterface|null  $validation
     * @throws ReflectionException
     */
    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);
        $this->initUserValidationRules();
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
                'extra',
                'status'
            ]
        );

        return $this->find($id);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    protected function initUserValidationRules(): void
    {
        $this->validationRules['status'] = 'if_exist|in_list[' . implode(',', UserStatuses::getValues()) . ']';

        $settings = Cms::settings('core.auth.loginType');

        $loginType = explode(':', $settings);

        foreach ($loginType as $type) {
            if (isset($this->validationRules[$type])) {
                $this->validationRules[$type]['rules'] = match ($type) {
                    'email' => $this->validationRules[$type]['rules'] . '|required|is_unique[users.email,id,{id}]',
                    'phone' => $this->validationRules[$type]['rules'] . '|required|is_unique[users.phone,id,{id}]'
                };
            }
        }

        unset($settings, $loginType);
    }

    /**
     * @param  Generator  $faker
     * @return array
     */
    public function fake(Generator &$faker): array
    {
        $statuses = UserStatuses::getValues();

        return [
            'login'         => $faker->word() . '_' . $faker->word(),
            'email'         => $faker->email(),
            'phone'         => '79' . rand(100000000, 999999999),
            'status'        => $statuses[array_rand($statuses)],
            'password'      => $faker->password(),
            'active_at'     => $faker->dateTimeBetween('-1 week', 'now', 'Asia/Omsk')->format('Y-m-d H:i:s'),
            'created_by_id' => 1
        ];
    }
}
