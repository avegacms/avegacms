<?php

declare(strict_types=1);

namespace AvegaCms\Models\Frontend;

use AvegaCms\Enums\MetaDataTypes;
use AvegaCms\Enums\MetaStatuses;
use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Utilities\Cms;

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
    protected $afterFind      = ['prepMetaData'];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // AvegaCms filter settings
    protected array $filterFields = [
        'id'        => 'metadata.id',
        'module_id' => 'metadata.module_id',
        'item_id'   => 'metadata.item_id',
        'rubric'    => 'metadata.parent',
        'parent'    => 'metadata.parent',
        'locale'    => 'metadata.locale_id',
        'title'     => 'metadata.title',
        'sort'      => 'metadata.sort',
        'published' => 'metadata.publish_at',
    ];
    protected array $searchFields = [
        'title',
    ];
    protected array $sortableFields = [
        'sort',
        'published',
    ];
    protected array $filterCastsFields = [
        'id'         => 'integer|array',
        'module_id'  => 'integer',
        'item_id'    => 'integer',
        'rubric'     => 'integer',
        'parent'     => 'integer',
        'locale'     => 'integer',
        'title'      => 'string',
        'publish_at' => 'string',
    ];
    protected string $searchFieldAlias = 'q';
    protected string $sortFieldAlias   = 's';
    protected array $filterEnumValues  = [];
    protected int $limit               = 20;
    protected int $maxLimit            = 100;
    protected array $casts             = [
        'id'              => 'int',
        'post_id'         => 'int',
        'rubric_id'       => 'int',
        'parent'          => 'int',
        'locale_id'       => 'int',
        'module_id'       => 'int',
        'creator_id'      => 'int',
        'item_id'         => 'int',
        'preview_id'      => '?cmsfile',
        'sort'            => 'int',
        'meta'            => '?json-array',
        'extra_data'      => '?json-array',
        'in_sitemap'      => '?int-bool',
        'meta_sitemap'    => '?json-array',
        'use_url_pattern' => '?int-bool',
        'rubrics'         => '?json-array',
        'created_by_id'   => 'int',
        'updated_by_id'   => 'int',
        'publish_at'      => '?cmsdatetime',
        'created_at'      => 'cmsdatetime',
        'updated_at'      => 'cmsdatetime',
    ];
    protected int $level = 7;

    public function __construct()
    {
        parent::__construct();
    }

    public function getContentMetaData(int $locale, string $slug = ''): ?object
    {
        $this->contentMetaDataSelect();

        $this->builder()->whereIn('metadata.meta_type', [MetaDataTypes::Main->name, MetaDataTypes::Page->name])
            ->where(['metadata.locale_id' => $locale]);

        if (empty($slug)) {
            $this->builder()->where(['metadata.slug' => 'main']);
        } else {
            $this->builder()->where(['metadata.hash_url' => $slug]);
        }

        $this->checkStatus();

        return $this->first();
    }

    public function getContentMetaData404(int $locale): array|object|null
    {
        $this->contentMetaDataSelect();

        $this->builder()->where(
            [
                'metadata.meta_type' => MetaDataTypes::Page404->name,
                'metadata.locale_id' => $locale,
            ]
        );

        return $this->first();
    }

    public function getMetaMap(int $id, ?int $clearLast = null): array
    {
        $this->builder()->from('metadata AS md_' . $this->level)->where(['md_' . $this->level . '.id' => $id]);

        for ($i = $this->level; $i > 0; $i--) {
            $this->builder()->select(['md_' . $i . '.id AS id' . $i]);
            if (($p = $i - 1)) {
                $this->builder()->join('metadata AS md_' . $p, 'md_' . $p . '.id = md_' . $i . '.parent', 'left');
            }
        }

        $list = array_filter($this->asArray()->first());

        if ($clearLast === null) {
            unset($list['id' . $this->level]);
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
                'metadata.hash_url',
                'metadata.use_url_pattern',
                'metadata.meta',
                'metadata.meta_type',
            ]
        )->whereIn('metadata.id', $list)
            ->whereNotIn(
                'metadata.meta_type',
                [
                    MetaDataTypes::Page404->name,
                    MetaDataTypes::Undefined->name,
                ]
            )->orderBy('metadata.parent', 'DESC');

        $this->checkStatus();

        return $this->findAll();
    }

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
                'metadata.hash_url',
                'metadata.meta',
                'metadata.extra_data',
                'metadata.meta_type',
                'metadata.publish_at',
            ]
        );

        $this->builder()
            ->groupStart()
            ->where(
                [
                    'metadata.module_id' => $moduleId,
                    'metadata.meta_type' => MetaDataTypes::Module->name,
                    ...$filter,
                ]
            )->groupEnd();

        $this->checkStatus();

        return $this->first();
    }

    public function getMetadataModule(int $moduleId = 0): ?object
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.parent',
                'metadata.locale_id',
                'metadata.module_id',
                'metadata.item_id',
                'metadata.creator_id',
                'metadata.slug',
                'metadata.title',
                'metadata.sort',
                'metadata.url',
                'metadata.use_url_pattern',
                'metadata.meta',
                'metadata.meta_sitemap',
                'metadata.extra_data',
                'metadata.status',
                'metadata.preview_id',
                'metadata.in_sitemap',
                'metadata.publish_at',
                'metadata.created_at',
                'metadata.updated_at',
            ]
        )->where(
            [
                'metadata.module_id' => $moduleId,
            ]
        );

        return $this;
    }

    public function getSubPages(int $id): array
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.parent',
                'metadata.locale_id',
                'metadata.preview_id AS preview',
                'metadata.title',
                'metadata.slug',
                'metadata.url',
                'metadata.hash_url',
                'metadata.use_url_pattern',
            ]
        )->where(['metadata.parent' => $id])
            ->orderBy('metadata.sort', 'ASC');

        $this->checkStatus();

        return $this->findAll();
    }

    protected function checkStatus(): MetaDataModel
    {
        $this->builder()
            ->groupStart()
            ->where(['metadata.status' => MetaStatuses::Publish->name])
            ->orGroupStart()
            ->where(
                [
                    'metadata.status'        => MetaStatuses::Future->name,
                    'metadata.publish_at <=' => date('Y-m-d H:i:s'),
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
                'metadata.preview_id AS preview',
                'metadata.url',
                'metadata.hash_url',
                'metadata.slug',
                'metadata.in_sitemap',
                'metadata.use_url_pattern',
                'metadata.title',
                'metadata.meta',
                'metadata.extra_data',
                'metadata.meta_type',
                'metadata.publish_at',
            ]
        )->where(['metadata.item_id' => 0]);

        return $this;
    }

    protected function prepMetaData(array $data): array
    {
        if (null !== $data['data']) {
            if ($data['singleton'] === true) {
                if (isset($data['data']->url)) {
                    $data['data']->url = Cms::urlPattern(
                        $data['data']->url,
                        $data['data']->use_url_pattern,
                        $data['data']->id,
                        $data['data']->slug,
                        $data['data']->locale_id,
                        $data['data']->parent
                    );
                }
            } else {
                foreach ($data['data'] as $item) {
                    if (isset($item->url)) {
                        $item->url = Cms::urlPattern(
                            $item->url,
                            $item->use_url_pattern,
                            $item->id,
                            $item->slug,
                            $item->locale_id,
                            $item->parent
                        );
                    }
                }
            }
        }

        return $data;
    }
}
