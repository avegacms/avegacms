<?php

declare(strict_types=1);

namespace AvegaCms\Models\Admin;

use CodeIgniter\Model;

class PermissionsModel extends Model
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
        'parent',
        'module_id',
        'is_module',
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
        'extra',
        'created_by_id',
        'updated_by_id',
        'created_at',
        'updated_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'id'            => ['rules' => 'if_exist|is_natural_no_zero'],
        'role_id'       => ['rules' => 'if_exist|is_natural'],
        'parent'        => ['rules' => 'if_exist|is_natural'],
        'module_id'     => ['rules' => 'if_exist|is_natural'],
        'slug'          => ['rules' => 'if_exist|permit_empty|alpha_dash|max_length[64]|unique_db_key[permissions.role_id+module_id+is_module+is_system+is_plugin+parent+slug,id,{id}]'],
        'extra'         => ['rules' => 'if_exist|permit_empty'],
        'created_by_id' => ['rules' => 'if_exist|is_natural'],
        'updated_by_id' => ['rules' => 'if_exist|is_natural'],
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
    protected array $casts    = [
        'id'            => 'int',
        'role_id'       => 'int',
        'parent'        => 'int',
        'module_id'     => 'int',
        'is_module'     => '?int-bool',
        'is_system'     => '?int-bool',
        'is_plugin'     => '?int-bool',
        'access'        => '?int-bool',
        'self'          => '?int-bool',
        'create'        => '?int-bool',
        'read'          => '?int-bool',
        'update'        => '?int-bool',
        'delete'        => '?int-bool',
        'moderated'     => '?int-bool',
        'settings'      => '?int-bool',
        'extra'         => '?json-array',
        'created_by_id' => 'int',
        'updated_by_id' => 'int',
        'created_at'    => 'cmsdatetime',
        'updated_at'    => 'cmsdatetime',
    ];

    public function getDefaultPermissions(int $roleId = 0): array
    {
        $this->_getSelect()->builder()->where(['role_id' => $roleId]);

        return $this->findAll();
    }

    public function getActions(int $roleId, int $moduleId): array
    {
        $this->_getSelect()->builder()
            ->where(['role_id' => $roleId])
            ->groupStart()
            ->where(['module_id' => $moduleId])
            ->orWhere(['parent' => $moduleId])
            ->groupEnd()
            ->orderBy('parent', 'ASC');

        return $this->findAll();
    }

    public function forEdit(int $id): array|object|null
    {
        $this->_getSelect()->builder()->where(['role_id !=' => 0, 'module_id !=' => 0]);

        return $this->find($id);
    }

    private function _getSelect(): Model
    {
        $this->builder()->select(
            [
                'id',
                'role_id',
                'parent',
                'module_id',
                'is_module',
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
                'extra',
            ]
        );

        return $this;
    }
}
