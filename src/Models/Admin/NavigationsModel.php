<?php

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Entities\NavigationsEntity;

class NavigationsModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'navigations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = NavigationsEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'parent',
        'is_admin',
        'object_id',
        'locale_id',
        'nav_type',
        'meta',
        'title',
        'slug',
        'sort',
        'active',
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
        'parent',
        'is_admin',
        'object_id',
        'locale_id',
        'nav_type',
        'meta',
        'title',
        'slug',
        'sort',
        'active',
        'created_by_id',
        'updated_by_id'
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
     * @param  int  $id
     * @return array|object|null
     */
    public function forEdit(int $id)
    {
        $this->builder()->select(
            [
                'parent',
                'is_admin',
                'object_id',
                'locale_id',
                'nav_type',
                'meta',
                'title',
                'slug',
                'sort',
                'active'
            ]
        )->where(['is_admin' => 0]);

        return $this->find($id);
    }
}
