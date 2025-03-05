<?php

declare(strict_types=1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;

class RolesModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'role',
        'description',
        'color',
        'path',
        'self_auth',
        'module_id',
        'role_entity',
        'active',
        'priority',
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
        'role'          => ['rules' => 'if_exist|required|alpha_dash|max_length[36]|is_unique[roles.role,id,{id}]'],
        'description'   => ['rules' => 'if_exist|permit_empty|max_length[512]'],
        'color'         => ['rules' => 'if_exist|required|max_length[7]'],
        'path'          => ['rules' => 'if_exist|permit_empty|max_length[512]'],
        'priority'      => ['rules' => 'if_exist|is_natural|max_length[3]'],
        'module_id'     => ['rules' => 'if_exist|is_natural'],
        'role_entity'   => ['rules' => 'if_exist|permit_empty|max_length[512]'],
        'created_by_id' => ['rules' => 'if_exist|is_natural'],
        'updated_by_id' => ['rules' => 'if_exist|is_natural'],
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = ['clearCache'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = ['clearCache'];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = ['clearCache'];
    protected array $casts    = [
        'id'            => 'int',
        'self_auth'     => '?int-bool',
        'module_id'     => '?int',
        'active'        => '?int-bool',
        'priority'      => 'int',
        'created_by_id' => 'int',
        'updated_by_id' => 'int',
        'created_at'    => 'cmsdatetime',
        'updated_at'    => 'cmsdatetime',
    ];

    public function getRolesList(): array
    {
        return cache()->remember('RolesList', DAY * 30, function () {
            $this->builder()->select(['id', 'role'])
                ->orderBy('role', 'ASC');

            $rolesData = $this->findAll();

            $list = [];

            if ($rolesData !== null) {
                foreach ($rolesData as $role) {
                    $list[] = [
                        'label' => $role->role,
                        'value' => $role->id,
                    ];
                }
            }

            unset($rolesData);

            return $list;
        });
    }

    public function getActiveRoles(): array
    {
        return cache()->remember('ActiveRoles', DAY * 30, function () {
            $this->builder()
                ->select(['id', 'role', 'path', 'self_auth', 'module_id', 'role_entity'])
                ->where(['active' => 1]);
            $rolesData = $this->asArray()->findAll();

            return array_column($rolesData, null, 'role');
        });
    }

    public function clearCache(): void
    {
        cache()->delete('RolesList');
        cache()->delete('ActiveRoles');
        $this->getRolesList();
        $this->getActiveRoles();
    }
}
