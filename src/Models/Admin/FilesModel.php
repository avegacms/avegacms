<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Entities\FilesEntity;
use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Enums\FileTypes;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Validation\ValidationInterface;

class FilesModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'files';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = FilesEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'provider',
        'data',
        'extra',
        'type',
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
        'data'          => ['rules' => 'if_exist|required|max_length[2048]'],
        'extra'         => ['rules' => 'if_exist|permit_empty|max_length[16384]'],
        'provider'      => ['rules' => 'if_exist|is_natural'],
        'active'        => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
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

    // AvegaCms filter settings
    protected array  $filterFields      = [];
    protected array  $searchFields      = [];
    protected array  $sortableFields    = [];
    protected array  $filterCastsFields = [];
    protected string $searchFieldAlias  = 'q';
    protected string $sortFieldAlias    = 's';
    protected string $sortDefaultFields = '';
    protected array  $filterEnumValues  = [];
    protected int    $limit             = 20;
    protected int    $maxLimit          = 100;

    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);

        $this->validationRules['type'] = [
            'rules' => 'if_exist|required|in_list[' . implode(',', FileTypes::get('value')) . ']'
        ];
    }
}
