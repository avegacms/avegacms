<?php

namespace AvegaCms\Models\Admin;

use CodeIgniter\Model;
use AvegaCms\Entities\UserRolesEntity;

class UserRolesModel extends Model
{
    protected $DBGroup        = 'default';
    protected $table          = 'user_roles';
    protected $returnType     = UserRolesEntity::class;
    protected $useSoftDeletes = false;
    protected $protectFields  = true;
    protected $allowedFields  = [
        'role_id',
        'user_id',
        'created_by_id',
        'created_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $deletedField  = '';

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
     * @param  string  $role
     * @return $this
     */
    public function getUserRoles(int $userId, string $role = '')
    {
        $this->builder()->select(['user_roles.role_id', 'user_roles.user_id', 'r.role'])
            ->join('users AS u', 'u.id = user_roles.user_id')
            ->join('roles AS r', 'r.id = user_roles.role_id')
            ->orderBy('r.priority', 'ASC')
            ->where(
                [
                    'user_roles.user_id' => $userId,
                    'u.status'           => 'active',
                    'r.active'           => 1
                ]
            );

        if ( ! empty($role)) {
            $this->builder()->where(['r.role' => $role]);
        }

        return $this;
    }
}
