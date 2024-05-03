<?php

namespace AvegaCms\Models\Admin;

use CodeIgniter\Model;
use AvegaCms\Entities\ContentEntity;
use Faker\Generator;

class ContentModel extends Model
{
    protected $DBGroup        = 'default';
    protected $table          = 'content';
    protected $returnType     = ContentEntity::class;
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
        'anons'   => ['rules' => 'if_exist|required|permit_empty|string'],
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

    public function fake(Generator &$faker): array
    {
        return [
            'id'      => 0,
            'anons'   => $faker->paragraph(1),
            'content' => $faker->paragraph(rand(6, 36))
        ];
    }
}
