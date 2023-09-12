<?php

namespace AvegaCms\Models\Frontend;

use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Entities\MetaDataEntity;
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
    protected array  $filterFields      = [];
    protected array  $searchFields      = [];
    protected array  $sortableFields    = [];
    protected array  $filterCastsFields = [];
    protected string $searchFieldAlias  = 'q';
    protected string $sortFieldAlias    = 's';
    protected array  $filterEnumValues  = [];
    protected int    $limit             = 20;
    protected int    $maxLimit          = 100;

    /**
     * @param  int  $locale
     * @param  string  $slug
     * @return array|object|null
     */
    public function getContentMetaData(int $locale, string $slug = ''): array|object|null
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.parent',
                'metadata.locale_id',
                'metadata.title',
                'metadata.meta',
                'metadata.extra_data',
                'metadata.meta_type',
                'metadata.publish_at'
            ]
        )->whereIn('metadata.meta_type',
            [
                MetaDataTypes::Main->value,
                MetaDataTypes::Page->value,
                MetaDataTypes::Rubric->value,
                MetaDataTypes::Post->value
            ]
        )->whereIn('metadata.status',
            [
                MetaStatuses::Publish->value,
                MetaStatuses::Future->value
            ]
        )->where(
            [
                'metadata.module_id'     => 0,
                'metadata.item_id'       => 0,
                'metadata.slug'          => ! empty($slug) ? $slug : 'main',
                'metadata.locale_id'     => $locale,
                'metadata.publish_at <=' => date('Y-m-d H:i:s')
            ]
        );

        return $this->first();
    }

    /**
     * @param  int  $locale
     * @param  array  $segments
     * @return array
     */
    public function getContentMetaMap(int $locale, array $segments): array
    {
        $this->builder()->select(['metadata.id', 'metadata.locale_id', 'metadata.meta'])
            ->whereIn('metadata.slug', $segments)
            ->whereIn('metadata.status',
                [
                    MetaStatuses::Publish->value,
                    MetaStatuses::Future->value
                ]
            )->whereIn('metadata.meta_type',
                [
                    MetaDataTypes::Main->value,
                    MetaDataTypes::Page->value,
                    MetaDataTypes::Rubric->value
                ]
            )->where(
                [
                    'metadata.module_id'     => 0,
                    'metadata.item_id'       => 0,
                    'metadata.locale_id'     => $locale,
                    'metadata.publish_at <=' => date('Y-m-d H:i:s')
                ]
            )->orderBy('metadata.parent', 'DESC');

        return $this->findAll();
    }
}
