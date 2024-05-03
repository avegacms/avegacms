<?php

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Entities\AttributesEntity;

class AttributesModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'attributes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = AttributesEntity::class;
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

    // AvegaCms filter settings
    public array  $filterFields      = [];
    public array  $searchFields      = [];
    public array  $sortableFields    = [];
    public array  $filterCastsFields = [];
    public string $searchFieldAlias  = 'q';
    public string $sortFieldAlias    = 's';
    public array  $filterEnumValues  = [];
    public int    $limit             = 20;
    public int    $maxLimit          = 100;
}
