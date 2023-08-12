<?php

namespace AvegaCms\Models\Admin;

use CodeIgniter\Model;
use AvegaCms\Entities\PermissionsEntity;

class PermissionsModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'permissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = PermissionsEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'role_id',
        'parent',
        'module_id',
        'is_plugin',
        'module_slug',
        'access',
        'self',
        'create',
        'read',
        'update',
        'delete',
        'moderated',
        'settings',
        'extra',
        'created_by_id',
        'updated_by_id',
        'created_at',
        'updated_at'
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
}
