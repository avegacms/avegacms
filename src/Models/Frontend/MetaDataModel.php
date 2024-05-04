<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Frontend;

use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Utilities\{Cms, SeoUtils};
use AvegaCms\Enums\{MetaStatuses, MetaDataTypes, SitemapChangefreqs};

class MetaDataModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'metadata';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

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
    protected array  $filterFields      = [
        'module_id' => 'metadata.module_id',
        'item_id'   => 'metadata.item_id',
        'rubric'    => 'metadata.parent',
        'parent'    => 'metadata.parent',
        'locale'    => 'metadata.locale_id',
        'title'     => 'metadata.title',
        'sort'      => 'metadata.sort',
        'published' => 'metadata.publish_at'
    ];
    protected array  $searchFields      = [
        'title'
    ];
    protected array  $sortableFields    = [
        'sort',
        'published'
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

    protected array $casts = [
        'id'              => 'int',
        'post_id'         => 'int',
        'rubric_id'       => 'int',
        'parent'          => 'int',
        'locale_id'       => 'int',
        'module_id'       => 'int',
        'creator_id'      => 'int',
        'item_id'         => 'int',
        'sort'            => 'int',
        'meta'            => '?json-array',
        'extra_data'      => '?json-array',
        'in_sitemap'      => '?int-bool',
        'meta_sitemap'    => '?json-array',
        'use_url_pattern' => '?int-bool',
        'rubrics'         => '?json-array',
        'created_by_id'   => 'int',
        'updated_by_id'   => 'int',
        'publish_at'      => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /**
     * @param  int  $locale
     * @param  string  $slug
     * @return array|object|null
     */
    public function getContentMetaData(int $locale, string $slug = ''): array|object|null
    {
        $this->contentMetaDataSelect();

        $this->builder()->whereIn('metadata.meta_type',
            [
                MetaDataTypes::Main->name,
                MetaDataTypes::Page->name,
                MetaDataTypes::Rubric->name,
                MetaDataTypes::Post->name
            ]
        )->where(
            [
                'metadata.slug'      => ! empty($slug) ? $slug : 'main',
                'metadata.locale_id' => $locale
            ]
        );

        $this->checkStatus();

        return $this->first();
    }

    /**
     * @param  int  $locale
     * @return array|object|null
     */
    public function getContentMetaData404(int $locale): array|object|null
    {
        $this->contentMetaDataSelect();

        $this->builder()->where(
            [
                'metadata.meta_type' => MetaDataTypes::Page404->name,
                'metadata.locale_id' => $locale
            ]
        );

        return $this->first();
    }

    /**
     * @param  int  $id
     * @param  int|null  $clearLast
     * @return array
     */
    public function getMetaMap(int $id, ?int $clearLast = null): array|object|null
    {
        $level = 7;
        $this->builder()->from('metadata AS md_' . $level)->where(['md_' . $level . '.id' => $id]);
        for ($i = $level; $i > 0; $i--) {
            $this->builder()->select(['md_' . $i . '.id AS id' . $i]);
            if (($p = $i - 1)) {
                $this->builder()->join('metadata AS md_' . $p, 'md_' . $p . '.id = md_' . $i . '.parent', 'left');
            }
        }

        $list = array_filter($this->asArray()->first());

        if ($clearLast === null) {
            unset($list['id' . $level]);
        }

        if (empty($list)) {
            return [];
        }

        $this->builder()->select(
            [
                'metadata.id',
                'metadata.parent',
                'metadata.locale_id',
                'metadata.module_id',
                'metadata.title',
                'metadata.slug',
                'metadata.url',
                'metadata.use_url_pattern',
                'metadata.meta',
                'metadata.meta_type'
            ]
        )->whereIn('metadata.id', $list)
            ->whereNotIn('metadata.meta_type',
                [
                    MetaDataTypes::Page404->name,
                    MetaDataTypes::Undefined->name
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
                'metadata.id',
                'metadata.parent',
                'metadata.locale_id',
                'metadata.module_id',
                'metadata.in_sitemap',
                'metadata.use_url_pattern',
                'metadata.title',
                'metadata.slug',
                'metadata.url',
                'metadata.meta',
                'metadata.extra_data',
                'metadata.meta_type',
                'metadata.publish_at'
            ]
        );

        $this->builder()
            ->groupStart()
            ->where(
                [
                    'metadata.module_id' => $moduleId,
                    'metadata.meta_type' => MetaDataTypes::Module->name,
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
        )->where(['metadata.parent' => $id])
            ->orderBy('metadata.sort', 'ASC');

        $this->checkStatus();

        return $this->findAll();
    }

    /**
     * @param  array  $filter
     * @return MetaDataModel
     */
    public function getRubricPosts(array $filter = []): MetaDataModel
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
                'metadata.meta_type' => MetaDataTypes::Post->name,
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
            ->where(['metadata.status' => MetaStatuses::Publish->name])
            ->orGroupStart()
            ->where(
                [
                    'metadata.status'        => MetaStatuses::Future->name,
                    'metadata.publish_at <=' => date('Y-m-d H:i:s')
                ]
            )->groupEnd()
            ->groupEnd();

        return $this;
    }

    /**
     * @return $this
     */
    protected function contentMetaDataSelect(): MetaDataModel
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.parent',
                'metadata.locale_id',
                'metadata.url',
                'metadata.slug',
                'metadata.in_sitemap',
                'metadata.use_url_pattern',
                'metadata.title',
                'metadata.meta',
                'metadata.extra_data',
                'metadata.meta_type',
                'metadata.publish_at'
            ]
        )->where(
            [
                'metadata.module_id' => 0,
                'metadata.item_id'   => 0
            ]
        );

        return $this;
    }
}
