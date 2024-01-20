<?php

namespace AvegaCms\Models\Admin;

use CodeIgniter\Model;
use AvegaCms\Entities\LoginEntity;
use AvegaCms\Enums\UserStatuses;

class LoginModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = LoginEntity::class;
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
        'deleted_at'
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

    /**
     * @param  array  $fields
     * @param  string|null  $role
     * @return array|LoginEntity|null
     */
    public function getUser(array $fields, ?string $role = null): array|null|LoginEntity
    {
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
                'users.condition'
            ]
        )->where($fields)
            ->whereIn(
                'users.status',
                [
                    UserStatuses::Active->value,
                    UserStatuses::Registration->value
                ]
            );

        if ( ! is_null($role)) {
            $this->builder()->join('user_roles', 'user_roles.user_id = users.id')
                ->join('roles', 'roles.id = user_roles.role_id');

            if (str_starts_with($role, '!')) {
                $this->builder()->where(['roles.role !=' => str_ireplace('!', '', $role)]);
            } else {
                $this->builder()->where(['roles.role' => $role]);
            }
        }

        return $this->first();
    }
}
