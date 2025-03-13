<?php

declare(strict_types=1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Enums\UserStatuses;
use AvegaCms\Models\AvegaCmsModel;

class LoginModel extends AvegaCmsModel
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
        'deleted_at',
    ];

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
    protected array $casts    = [
        'id'            => 'int',
        'avatar'        => '?cmsfile',
        'profile'       => '?json-array',
        'extra'         => '?json-array',
        'expires'       => 'int',
        'created_by_id' => 'int',
        'updated_by_id' => 'int',
        'created_at'    => '?cmsdatetime',
        'updated_at'    => '?cmsdatetime',
        'deleted_at'    => '?cmsdatetime',
        'roleId'        => 'int',
        'moduleId'      => 'int',
        'selfAuth'      => 'int',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function getUser(array $fields, ?string $role = null): ?object
    {
        $list = [];

        foreach ($fields as $key => $field) {
            if (! empty($field)) {
                if (in_array($key, ['id', 'login', 'email', 'phone'], true)) {
                    $list['users.' . $key] = $field;
                } elseif ($key === 'role_id') {
                    $list['user_roles.' . $key] = $field;
                } elseif ($key === 'role') {
                    $list['roles.' . $key] = $field;
                }
            }
        }

        $this->builder()->select(
            [
                'users.id',
                'users.login',
                'users.avatar',
                'users.phone',
                'users.email',
                'users.timezone',
                'users.password',
                'users.secret',
                'users.path',
                'users.expires',
                'users.profile',
                'users.extra',
                'users.status',
                'users.condition',
                'roles.role',
                'roles.module_id AS moduleId',
                'roles.self_auth AS selfAuth',
                'user_roles.role_id AS roleId',
                'modules.slug AS module',
            ]
        )->join('user_roles', 'user_roles.user_id = users.id')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->join('modules', 'modules.id = roles.module_id', 'left')
            ->where($list)
            ->whereIn('users.status', [UserStatuses::Active->value, UserStatuses::Registration->value]);

        if (null !== $role) {
            if (str_starts_with($role, '!')) {
                $this->builder()->where(['roles.role !=' => str_ireplace('!', '', $role)]);
            } else {
                $this->builder()->where(['roles.role' => $role]);
            }
        }

        return $this->first();
    }
}
