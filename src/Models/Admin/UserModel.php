<?php

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Entities\UserEntity;
use Faker\Generator;

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
        'reset',
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
    protected $validationRules      = [];
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

    public function usersList()
    {
        $this->builder()->select(
            [
                'id',
                'login',
                'avatar',
                'phone',
                'email',
                'timezone',
                'status',
                'active_at'
            ]
        );

        return $this;
    }

    /**
     * @param  Generator  $faker
     * @return array
     */
    public function fake(Generator &$faker): array
    {
        return [
            'login'         => $faker->word() . '_' . $faker->word(),
            'email'         => $faker->email,
            'phone'         => '79' . rand(100000000, 999999999),
            'status'        => array_rand(
                [
                    'pre-registration',
                    'active',
                    'banned',
                    'deleted',
                    ''
                ], 1
            ),
            'active_at'     => $faker->dateTimeBetween('-1 week', 'now', 'Asia/Omsk')->format('Y-m-d H:i:s'),
            'created_by_id' => 1
        ];
    }
}
