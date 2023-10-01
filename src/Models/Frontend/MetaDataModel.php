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
    protected array  $filterFields      = [
        'module_id' => 'metadata.module_id',
        'item_id'   => 'metadata.item_id',
        'rubric'    => 'metadata.parent',
        'parent'    => 'metadata.parent',
        'locale'    => 'metadata.locale_id',
        'title'     => 'metadata.title',
        'published' => 'metadata.publish_at'
    ];
    protected array  $searchFields      = [
        'title' => 'metadata.title',
    ];
    protected array  $sortableFields    = [
        'sort'      => 'metadata.sort',
        'published' => 'metadata.publish_at'
    ];
    protected array  $filterCastsFields = [
        'module_id'  => 'integer',
        'item_id'    => 'integer',
        'rubric'     => 'integer',
        'parent'     => 'integer',
        'locale'     => 'integer',
        'title'      => 'string',
        'publish_at' => 'string'
    ];
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
                'metadata.in_sitemap',
                'metadata.use_url_pattern',
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
                MetaDataTypes::Post->value,
                MetaDataTypes::Page404->value
            ]
        )->where(
            [
                'metadata.module_id' => 0,
                'metadata.item_id'   => 0,
                'metadata.slug'      => ! empty($slug) ? $slug : 'main',
                'metadata.locale_id' => $locale
            ]
        );

        $this->checkStatus();

        return $this->first();
    }

    /**
     * @param  int  $locale
     * @param  array  $segments
     * @return array
     */
    public function getContentMetaMap(int $locale, array $segments): array
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.parent',
                'metadata.locale_id',
                'metadata.title',
                'metadata.url',
                'metadata.use_url_pattern',
                'metadata.meta'
            ]
        )->whereIn('metadata.slug', $segments)
            ->whereIn('metadata.meta_type',
                [
                    MetaDataTypes::Main->value,
                    MetaDataTypes::Page->value,
                    MetaDataTypes::Rubric->value,
                    MetaDataTypes::Post->value
                ]
            )->where(
                [
                    'metadata.module_id' => 0,
                    'metadata.locale_id' => $locale
                ]
            )->orderBy('metadata.parent', 'DESC');

        $this->checkStatus();

        return $this->findAll();
    }

    /**
     * @param  int  $moduleId
     * @param  array  $filter
     * @return array|object|null
     */
    public function getModuleMetaData(int $moduleId, array $filter = []): array|object|null
    {
        $this->builder()->select(
            [
                'id',
                'parent',
                'locale_id',
                'in_sitemap',
                'use_url_pattern',
                'title',
                'slug',
                'url',
                'meta',
                'extra_data',
                'meta_type',
                'publish_at'
            ]
        );

        $this->builder()
            ->groupStart()
            ->where(
                [
                    'module_id' => $moduleId,
                    'meta_type' => MetaDataTypes::Module->value,
                    ...$filter
                ]
            )->groupEnd();

        $this->checkStatus();

        return $this->first();
    }

    /**
     * @param  int  $id
     * @return array
     */
    public function getSubPages(int $id): array
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.parent',
                'metadata.locale_id',
                'metadata.title',
                'metadata.slug',
                'metadata.url',
                'metadata.use_url_pattern',
            ]
        )->where(
            [
                'metadata.parent'    => $id,
                'metadata.meta_type' => MetaDataTypes::Page->value,
                'metadata.module_id' => 0
            ]
        )->orderBy('metadata.sort', 'ASC');

        $this->checkStatus();

        return $this->findAll();
    }

    /**
     * @param  array  $filter
     * @return AvegaCmsModel
     */
    public function getRubricPosts(array $filter = []): AvegaCmsModel
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.parent',
                'metadata.locale_id',
                'metadata.title',
                'metadata.url',
                'metadata.use_url_pattern',
                'c.anons',
                'c.extra',
                'u.login AS author',
                'metadata.publish_at'
            ]
        )->join('content AS c', 'c.id = metadata.id')
            ->join('users AS u', 'u.id = metadata.creator_id', 'left')
            ->groupStart();

        $this->checkStatus();

        $this->builder()->where(
            [
                'metadata.meta_type' => MetaDataTypes::Post->value,
                'metadata.module_id' => 0
            ]
        )->groupEnd();

        return $this->filter($filter);
    }


    /**
     * @return MetaDataModel
     */
    protected function checkStatus(): MetaDataModel
    {
        $this->builder()
            ->groupStart()
            ->where(
                [
                    'metadata.status' => MetaStatuses::Publish->value
                ]
            )->orWhere(
                [
                    'metadata.status'        => MetaStatuses::Future->value,
                    'metadata.publish_at <=' => date('Y-m-d H:i:s')
                ])
            ->groupEnd();

        return $this;
    }
}
