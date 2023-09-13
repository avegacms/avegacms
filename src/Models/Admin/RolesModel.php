<?php

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Entities\RolesEntity;

class RolesModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = RolesEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'role',
        'description',
        'color',
        'path',
        'active',
        'priority',
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
        'role'          => ['rules' => 'if_exist|required|alpha_dash|max_length[36]|is_unique[roles.role,id,{id}]'],
        'description'   => ['rules' => 'if_exist|permit_empty'],
        'color'         => ['rules' => 'if_exist|required|max_length[7]'],
        'path'          => ['rules' => 'if_exist|permit_empty|max_length[512]'],
        'priority'      => ['rules' => 'if_exist|is_natural|max_length[3]'],
        'active'        => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
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

    public function getRolesList()
    {
        return cache()->remember('UserRolesList', DAY * 30, function () {
            $roles = [];
            $this->builder()->select(['id', 'role'])->orderBy('role', 'ASC');
            $rolesData = $this->findAll();
            foreach ($rolesData as $role) {
                $roles[] = $role->toArray();
            }
            return array_column($roles, 'role', 'id');
        });
    }
}
