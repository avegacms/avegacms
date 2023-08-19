<?php

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Entities\LocalesEntity;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Validation\ValidationInterface;

class LocalesModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'locales';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = LocalesEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'slug',
        'locale',
        'locale_name',
        'home',
        'extra',
        'is_default',
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
        'id'            => ['rules' => 'if_exist|is_natural_no_zero'],
        'slug'          => ['rules' => 'if_exist|required|alpha_dash|max_length[20]|is_unique[locales.slug,id,{id}]'],
        'locale'        => ['rules' => 'if_exist|required|max_length[32]'],
        'locale_name'   => ['rules' => 'if_exist|required|max_length[100]'],
        'home'          => ['rules' => 'if_exist|required|max_length[255]'],
        'extra'         => ['rules' => 'if_exist|permit_empty'],
        'is_default'    => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
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

    /**
     * @param  int  $id
     * @return array|object|null
     */
    public function forEdit(int $id): array|object|null
    {
        $this->builder()->select([
            'id',
            'slug',
            'locale',
            'locale_name',
            'home',
            'extra',
            'is_default',
            'active'
        ]);

        return $this->find($id);
    }
}
