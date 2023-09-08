<?php

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Entities\MetaDataEntity;
use Faker\Generator;
use AvegaCms\Enums\{MetaStatuses, MetaDataTypes};

class MetaDataModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'metadata';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = MetaDataEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'parent',
        'locale_id',
        'module_id',
        'slug',
        'creator_id',
        'item_id',
        'title',
        'sort',
        'url',
        'meta',
        'extra',
        'status',
        'meta_type',
        'in_sitemap',
        'created_by_id',
        'updated_by_id',
        'publish_at',
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

    /**
     * @return AvegaCmsModel
     */
    public function selectPages(): AvegaCmsModel
    {
        $this->builder()->select(
            [
                'metadata.*',
                'pm.title AS parent_title'
            ]
        )->join('metadata AS pm', 'pm.id = metadata.parent', 'left')
            ->whereIn('metadata.meta_type', [MetaDataTypes::Main->value, MetaDataTypes::Page->value]);

        return $this;
    }

    /**
     * @param  Generator  $faker
     * @return array
     */
    public function fake(Generator &$faker): array
    {
        $title = $faker->sentence();
        $url = mb_url_title($title);
        $status = MetaStatuses::getValues();

        return [

            'parent'        => 0,
            'locale_id'     => 0,
            'module_id'     => 0,
            'slug'          => $url,
            'creator_id'    => 0,
            'item_id'       => 0,
            'title'         => $title,
            'sort'          => rand(1, 1000),
            'url'           => strtolower($url),
            'meta'          => [
                'keywords'     => $faker->sentence(1),
                'descriptions' => $faker->sentence(1),
                'breadcrumb'   => rand(0, 1) ? $faker->word() : ''
            ],
            'status'        => $status[array_rand($status)],
            'meta_type'     => '',
            'in_sitemap'    => rand(0, 1),
            'created_by_id' => 0,
            'publish_at'    => $faker->dateTimeBetween('-1 year', 'now', 'Asia/Omsk')->format('Y-m-d H:i:s')
        ];
    }
}
