<?php

declare(strict_types=1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;

class AttemptsEntranceModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'attempts_entrance';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'uid',
        'login',
        'code',
        'expires',
        'user_ip',
        'user_agent',
        'created_at',
        'updated_at',
    ];
    protected array $casts = [
        'code'       => 'int',
        'expires'    => 'int',
        'created_at' => 'cmsdatetime',
        'updated_at' => 'cmsdatetime',
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

    // AvegaCms filter settings
    protected array $filterFields       = [];
    protected array $searchFields       = [];
    protected array $sortableFields     = [];
    protected array $filterCastsFields  = [];
    protected string $searchFieldAlias  = 'q';
    protected string $sortFieldAlias    = 's';
    protected string $sortDefaultFields = '';
    protected array $filterEnumValues   = [];
    protected int $limit                = 20;
    protected int $maxLimit             = 100;

    public function __construct()
    {
        parent::__construct();
    }
}
