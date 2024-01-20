<?php

namespace AvegaCms\Models\Admin;

use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Model;
use AvegaCms\Entities\FilesEntity;
use AvegaCms\Enums\{FileTypes, FileProviders};
use CodeIgniter\Validation\ValidationInterface;

class FilesModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'files';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = FilesEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'provider_id',
        'provider',
        'data',
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
        'data'          => ['rules' => 'if_exist|required|string|max_length[2048]'],
        'provider_id'   => ['rules' => 'if_exist|is_natural'],
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

    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);

        $this->validationRules = [
            $this->validationRules,
            ...[
                'provider' => 'if_exist|required|in_list[' . implode(',', FileProviders::get('value')) . ']',
                'type'     => 'if_exist|required|in_list[' . implode(',', FileTypes::get('value')) . ']',
            ]
        ];
    }
}
