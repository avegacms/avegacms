<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Enums\UserStatuses;
use AvegaCms\Models\AvegaCmsModel;
use CodeIgniter\Model;

class UserRolesModel extends AvegaCmsModel
{
    protected $DBGroup        = 'default';
    protected $table          = 'user_roles';
    protected $returnType     = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields  = true;
    protected $allowedFields  = [
        'role_id',
        'user_id',
        'created_by_id',
        'created_at'
    ];

    //AvegaCms model settings
    public array  $filterFields      = [
        'id'     => 'u.id',
        'login'  => 'u.login',
        'phone'  => 'u.phone',
        'email'  => 'u.email',
        'status' => 'u.status',
        'role'   => 'r.role',
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
        'avatar' => 'string',
        'phone'  => 'int',
        'email'  => 'string',
        'status' => 'string',
        'role'   => 'string'
    ];
    public string $searchFieldAlias  = 'q';
    public string $sortFieldAlias    = 's';
    public int    $limit             = 20;
    public int    $maxLimit          = 100;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $deletedField  = '';

    // Validation
    protected $validationRules      = [
        'role_id'       => ['rules' => 'if_exist|is_natural_no_zero'],
        'user_id'       => ['rules' => 'if_exist|is_natural_no_zero'],
        'created_by_id' => ['rules' => 'if_exist|is_natural_no_zero']
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

    protected array $casts = [
        'role_id'       => 'int',
        'user_id'       => 'int',
        'created_by_id' => 'int',
        'created_at'    => 'cmsdatetime'
    ];

    public function getUsers(): AvegaCmsModel
    {
        $this->builder()->select(
            [
                'u.id',
                'u.login',
                'u.avatar',
                'u.phone',
                'u.email',
                'u.timezone',
                'u.status',
                'u.active_at',
                'user_roles.role_id',
                'r.role'
            ]
        )->join('users AS u', 'u.id = user_roles.user_id')
            ->join('roles AS r', 'r.id = user_roles.role_id')
            ->groupBy(' user_roles.user_id');

        return $this;
    }

    /**
     * @param  int  $userId
     * @param  string  $role
     * @return $this
     */
    public function getUserRoles(int $userId, string $role = ''): AvegaCmsModel
    {
        $this->builder()->select(['user_roles.role_id', 'user_roles.user_id', 'r.role', 'r.self_auth'])
            ->join('users AS u', 'u.id = user_roles.user_id')
            ->join('roles AS r', 'r.id = user_roles.role_id')
            ->whereIn('u.status', [UserStatuses::Active->value, UserStatuses::Registration->value])
            ->where(
                [
                    'user_roles.user_id' => $userId,
                    'r.active'           => 1
                ]
            )->orderBy('r.priority', 'ASC');

        if ( ! empty($role)) {
            $this->builder()->where(['r.role' => $role]);
        }

        return $this;
    }
}
