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
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'id'            => ['rules' => 'if_exist|is_natural_no_zero'],
        'role_id'       => ['rules' => 'if_exist|is_natural'],
        'parent'        => ['rules' => 'if_exist|is_natural'],
        'module_id'     => ['rules' => 'if_exist|is_natural'],
        'is_module'     => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'is_plugin'     => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'slug'          => ['rules' => 'if_exist|permit_empty|alpha_dash|max_length[64]'],
        'access'        => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'self'          => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'create'        => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'read'          => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'update'        => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'delete'        => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'moderated'     => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'settings'      => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'extra'         => ['rules' => 'if_exist|permit_empty'],
        'created_by_id' => ['rules' => 'if_exist|is_natural'],
        'updated_by_id' => ['rules' => 'if_exist|is_natural']
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
     * @param  int  $roleId
     * @return array
     */
    public function getDefaultPermissions(int $roleId = 0): array
    {
        $this->_getSelect()->builder()->where(['role_id' => $roleId]);

        return $this->findAll();
    }

    /**
     * @param  int  $roleId
     * @param  int  $moduleId
     * @return array
     */
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
                'extra'
            ]
        );

        return $this;
    }
}
