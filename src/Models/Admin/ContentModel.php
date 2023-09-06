<?php

namespace AvegaCms\Models\Admin;

use CodeIgniter\Model;
use AvegaCms\Entities\ContentEntity;
use Faker\Generator;

class ContentModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'content';
    protected $primaryKey       = 'meta_id';
    protected $useAutoIncrement = false;
    protected $returnType       = ContentEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'meta_id',
        'caption',
        'anons',
        'content',
        'extra'
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

    public function fake(Generator &$faker): array
    {
        return [
            'meta_id' => 0,
            'caption' => $faker->sentence(),
            'anons'   => $faker->paragraph(1),
            'content' => $faker->paragraph(rand(6, 36))
        ];
    }
}
