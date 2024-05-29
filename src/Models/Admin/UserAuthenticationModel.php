<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Utilities\Cms;
use CodeIgniter\Model;

class UserAuthenticationModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'permissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'role_id',
        'module_id',
        'is_module',
        'is_system',
        'is_plugin',
        'parent',
        'slug',
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
        'updated_at',
    ];

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

    protected array $casts = [
        'id'            => 'int',
        'role_id'       => 'int',
        'module_id'     => 'int',
        'is_module'     => '?int-bool',
        'is_system'     => '?int-bool',
        'is_plugin'     => '?int-bool',
        'parent'        => '?int-bool',
        'access'        => '?int-bool',
        'self'          => '?int-bool',
        'create'        => '?int-bool',
        'read'          => '?int-bool',
        'update'        => '?int-bool',
        'delete'        => '?int-bool',
        'moderated'     => '?int-bool',
        'settings'      => '?array-bool',
        'extra'         => '?json-array',
        'created_by_id' => 'int',
        'updated_by_id' => 'int',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];

    /**
     * @param  string  $role
     * @param  int  $roleId
     * @return array
     */
    public function getRoleAccessMap(string $role, int $roleId): array
    {
        return cache()->remember('RAM_' . $role, DAY * 30, function () use ($roleId) {
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
            )->where(['role_id' => $roleId]);

            return Cms::getTree($this->asArray()->findAll());
        });
    }
}
