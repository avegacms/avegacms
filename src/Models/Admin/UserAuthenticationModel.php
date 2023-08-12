<?php

namespace AvegaCms\Models\Admin;

use CodeIgniter\Model;
use AvegaCms\Entities\PermissionsEntity;

class UserAuthenticationModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'permissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = PermissionsEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

    // Dates
    protected $useTimestamps = false;
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

    public function getRoleAccessMap(int $roleId)
    {
        $this->builder()->select(
            [
                'parent',
                'module_id',
                'parent',
                'is_system',
                'is_plugin',
                'slug',
                'access',
                'self',
                'create',
                'read',
                'update',
                'delete',
                'moderated',
                'settings',
                'extra'
            ]
        )->where(['role_id' => $roleId])
            ->orderBy('module_id', 'ASC')
            ->orderBy('parent', 'ASC');

        return $this;
    }
}
