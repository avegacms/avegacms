<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Utilities\{Cms, SeoUtils};
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Validation\ValidationInterface;
use AvegaCms\Enums\{MetaStatuses, MetaDataTypes, SitemapChangefreqs};
use ReflectionException;

class MetaDataModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'metadata';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'parent',
        'locale_id',
        'module_id',
        'item_id',
        'slug',
        'creator_id',
        'title',
        'url',
        'sort',
        'meta',
        'extra_data',
        'status',
        'meta_type',
        'in_sitemap',
        'meta_sitemap',
        'use_url_pattern',
        'publish_at',
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
        'id'                    => ['rules' => 'if_exist|required|is_natural_no_zero'],
        'parent'                => ['rules' => 'if_exist|required|is_natural'],
        'locale_id'             => ['rules' => 'if_exist|required|is_natural_no_zero'],
        'module_id'             => ['rules' => 'if_exist|required|is_natural'],
        'item_id'               => ['rules' => 'if_exist|required|is_natural'],
        'slug'                  => ['rules' => 'if_exist|permit_empty'],
        'url'                   => ['rules' => 'if_exist|permit_empty'],
        'creator_id'            => ['rules' => 'if_exist|required|is_natural'],
        'title'                 => [
            'label' => 'Название',
            'rules' => 'if_exist|required|max_length[512]'
        ],
        'sort'                  => ['rules' => 'if_exist|required|is_natural'],
        'extra_data'            => ['rules' => 'if_exist|permit_empty'],
        'publish_at'            => ['rules' => 'if_exist|permit_empty|valid_date[Y-m-d H:i:s]'],
        'created_by_id'         => ['rules' => 'if_exist|is_natural'],
        'updated_by_id'         => ['rules' => 'if_exist|is_natural'],
        // Метаданные для JSON полей
        'meta.title'            => ['rules' => 'if_exist|permit_empty|max_length[512]'],
        'meta.keywords'         => ['rules' => 'if_exist|permit_empty|max_length[512]'],
        'meta.description'      => ['rules' => 'if_exist|permit_empty|max_length[512]'],
        'meta.breadcrumb'       => ['rules' => 'if_exist|permit_empty|max_length[255]'],
        'meta.og:title'         => ['rules' => 'if_exist|permit_empty|max_length[512]'],
        'meta.og:type'          => ['rules' => 'if_exist|permit_empty|max_length[512]'],
        'meta.og:url'           => ['rules' => 'if_exist|permit_empty|max_length[512]'],
        'meta.og:image'         => ['rules' => 'if_exist|permit_empty|is_natural'],
        'meta_sitemap.priority' => ['rules' => 'if_exist|permit_empty|is_natural']
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['beforeMetaDataInsert'];
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
        'meta_sitemap'    => '?json-array',
        'extra_data'      => '?json-array',
        'in_sitemap'      => '?int-bool',
        'use_url_pattern' => '?int-bool',
        'rubrics'         => '?json-array',
        'created_by_id'   => 'int',
        'updated_by_id'   => 'int',
        'publish_at'      => 'cmsdatetime',
        'created_at'      => 'cmsdatetime',
        'updated_at'      => 'cmsdatetime',
    ];

    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);

        $this->validationRules['status'] = [
            'label' => 'Статус',
            'rules' => 'if_exist|required|in_list[' . implode(',',
                    MetaStatuses::get('name')) . ']'
        ];

        $this->validationRules['meta_type'] = [
            'label' => 'Тип страницы',
            'rules' => 'if_exist|required|in_list[' . implode(',',
                    MetaDataTypes::get('name')) . ']'
        ];

        $this->validationRules['meta_sitemap.changefreq'] = [
            'label' => 'Тип страницы',
            'rules' => 'if_exist|required|in_list[' . implode(',',
                    SitemapChangefreqs::get('name')) . ']'
        ];
    }


    /**
     * @param  array  $data
     * @return array
     * @throws ReflectionException
     */
    protected function beforeMetaDataInsert(array $data): array
    {
        helper(['url']);
        $meta = $data['data'];

        if ((isset($meta['slug']) && empty($meta['slug'])) && ! empty($meta['title'])) {
            $meta['slug'] = strtolower(mb_substr(mb_url_title($meta['title']), 0, 120));
        }

        if (isset($meta['url']) && empty($meta['url'])) {
            $url         = ! empty($meta['slug'] ?? '') ? $meta['slug'] : mb_url_title(strtolower($meta['title']));
            $meta['url'] = match ($meta['meta_type'] ?? '') {
                MetaDataTypes::Main->name => Cms::settings('core.env.useMultiLocales') ? SeoUtils::Locales($meta['locale_id'])['slug'] : '/',
                MetaDataTypes::Page->name => $this->getParentUrl($meta['parent'] ?? 0) . $url,
                default                   => strtolower($url)
            };
        }

        if (isset($meta['meta'])) {
            $meta['meta']                = json_decode($meta['meta'], true);
            $meta['meta']['title']       = ! empty($meta['meta']['title'] ?? '') ? $meta['meta']['title'] : $meta['title'];
            $meta['meta']['keywords']    = ! empty($meta['meta']['keywords'] ?? '') ? $meta['meta']['keywords'] : '';
            $meta['meta']['description'] = ! empty($meta['meta']['description'] ?? '') ? $meta['meta']['description'] : '';

            $meta['meta']['breadcrumb'] = $meta['meta']['breadcrumb'] ?? '';

            $meta['meta']['og:title'] = ! empty($meta['meta']['og:title'] ?? '') ? $meta['meta']['og:title'] : $meta['title'];
            $meta['meta']['og:type']  = ! empty($meta['meta']['og:type'] ?? '') ? $meta['meta']['og:type'] : 'website';
            $meta['meta']['og:url']   = $meta['meta']['og:url'] ?? $meta['url'];
            $meta['meta']['og:image'] = $meta['meta']['og:image'] ?? 0;

            $meta['meta'] = json_encode($meta['meta']);
        }

        if (isset($meta['meta_sitemap'])) {
            $meta['meta_sitemap']               = json_decode($meta['meta_sitemap'], true);
            $meta['meta_sitemap']['priority']   = $meta['meta_sitemap']['priority'] ?? 50;
            $meta['meta_sitemap']['changefreq'] = $meta['meta_sitemap']['changefreq'] ?? SitemapChangefreqs::Monthly->value;

            $meta['meta_sitemap'] = json_encode($meta['meta_sitemap']);
        }

        $data['data'] = $meta;

        return $data;
    }

    /**
     * @param  int  $parentId
     * @return string
     */
    protected function getParentUrl(int $parentId): string
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.use_url_pattern',
                'metadata.url',
                'metadata.slug',
                'metadata.locale_id',
                'metadata.meta_type',
                'metadata.parent'
            ]
        )->whereIn('metadata.meta_type', [MetaDataTypes::Main->value, MetaDataTypes::Page->value]);

        if (($parent = $this->find($parentId)) === null) {
            return '';
        }

        return match ($parent->meta_type) {
            MetaDataTypes::Main->value,
            MetaDataTypes::Page404->value => '',
            default                       => ($parent->url === '/') ? '' : $parent->url . '/',
        };
    }
}
