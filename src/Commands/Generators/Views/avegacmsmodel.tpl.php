<@php

namespace {namespace};

use AvegaCms\Models\AvegaCmsModel;

class {class} extends AvegaCmsModel
{
    protected $DBGroup          = '{dbGroup}';
    protected $table            = '{table}';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = {return};
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
    protected array $filterFields      = [];
    protected array $searchFields      = [];
    protected array $sortableFields    = [];
    protected array $filterCastsFields = [];
    protected string $searchFieldAlias = 'q';
    protected string $sortFieldAlias   = 's';
    protected array $filterEnumValues  = [];
    protected int $limit               = 20;
    protected int $maxLimit            = 100;
}
