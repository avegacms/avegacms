<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;

class ContentModel extends AvegaCmsModel
{
    protected $DBGroup        = 'default';
    protected $table          = 'content';
    protected $returnType     = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields  = true;
    protected $allowedFields  = [
        'id',
        'anons',
        'content',
        'extra'
    ];

    // Validation
    protected $validationRules      = [
        'id'      => ['rules' => 'if_exist|required|is_natural_no_zero'],
        'anons'   => ['rules' => 'if_exist|permit_empty|string'],
        'content' => ['rules' => 'if_exist|permit_empty|string'],
        'extra'   => ['rules' => 'if_exist|permit_empty|string']
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

    protected array $casts = [
        'id'    => 'int',
        'extra' => '?json-array'
    ];
}
