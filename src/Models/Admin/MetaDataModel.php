<?php

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Entities\MetaDataEntity;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Validation\ValidationInterface;
use Faker\Generator;
use AvegaCms\Enums\{MetaStatuses, MetaDataTypes, MetaChangefreq};

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
        'extra_data',
        'status',
        'meta_type',
        'in_sitemap',
        'meta_sitemap',
        'use_url_pattern',
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
    protected $validationRules      = [
        'id'                    => ['rules' => 'if_exist|is_natural'],
        'parent'                => ['rules' => 'if_exist|is_natural'],
        'locale_id'             => ['rules' => 'if_exist|required|is_natural_no_zero'],
        'module_id'             => ['rules' => 'if_exist|is_natural'],
        'slug'                  => ['rules' => 'if_exist|required|permit_empty|string|max_length[64]|unique_db_key[metadata.parent+locale_id+module_id+item_id+use_url_pattern+slug,id,{id}]'],
        'creator_id'            => ['rules' => 'if_exist|is_natural_no_zero'],
        'item_id'               => ['rules' => 'if_exist|is_natural'],
        'title'                 => ['rules' => 'if_exist|required|string|max_length[1024]'],
        'sort'                  => ['rules' => 'if_exist|is_natural_no_zero'],
        'url'                   => ['rules' => 'if_exist|required|string|max_length[2048]'],
        'meta.title'            => ['rules' => 'if_exist|permit_empty|string|max_length[255]'],
        'meta.keywords'         => ['rules' => 'if_exist|permit_empty|string|max_length[255]'],
        'meta.description'      => ['rules' => 'if_exist|permit_empty|string|max_length[255]'],
        'meta.breadcrumb'       => ['rules' => 'if_exist|permit_empty|string|max_length[255]'],
        'meta.og:title'         => ['rules' => 'if_exist|permit_empty|string|max_length[255]'],
        'meta.og:type'          => ['rules' => 'if_exist|permit_empty|string|max_length[255]'],
        'meta.og:url'           => ['rules' => 'if_exist|permit_empty|string|max_length[2048]'],
        'meta.og:image'         => ['rules' => 'if_exist|permit_empty|string|max_length[512]'],
        'in_sitemap'            => ['rules' => 'if_exist|is_natural|in_list[0,1,2]'],
        'meta_sitemap.priority' => ['rules' => 'if_exist|is_natural_no_zero|less_than_equal_to[100]'],
        'use_url_pattern'       => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'extra_data'            => ['rules' => 'if_exist|permit_empty|string'],
        'publish_at'            => ['rules' => 'if_exist|valid_date[Y-m-d H:i:s]'],
        'rubrics.*'             => ['rules' => 'if_exist|required|is_natural_no_zero'],
        'created_by_id'         => ['rules' => 'if_exist|is_natural'],
        'updated_by_id'         => ['rules' => 'if_exist|is_natural']
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = ['clearCache'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = ['clearCache'];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = ['clearCache'];

    //AvegaCms model settings
    public array  $filterFields      = [
        'id'         => 'metadata.id',
        'locale_id'  => 'metadata.locale_id',
        'module_id'  => 'metadata.module_id',
        'item_id'    => 'metadata.item_id',
        'login'      => 'u.login',
        'title'      => 'metadata.title',
        'type'       => 'metadata.meta_type',
        'status'     => 'metadata.status',
        'publish_at' => 'metadata.publish_at'
    ];
    public array  $searchFields      = [
        'login',
        'title'
    ];
    public array  $sortableFields    = [
        'publish_at'
    ];
    public array  $filterCastsFields = [
        'id'         => 'int|array',
        'locale_id'  => 'int',
        'module_id'  => 'int',
        'item_id'    => 'int',
        'login'      => 'string',
        'title'      => 'string',
        'type'       => 'string',
        'status'     => 'string',
        'publish_at' => 'string'
    ];
    public string $searchFieldAlias  = 'q';
    public string $sortFieldAlias    = 's';
    public int    $limit             = 20;
    public int    $maxLimit          = 100;

    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);

        $this->validationRules['status'] = 'if_exist|required|in_list[' . implode(',',
                MetaStatuses::get('value')
            ) . ']';

        $this->validationRules['meta_type'] = 'if_exist|required|in_list[' . implode(',',
                MetaDataTypes::get('value')
            ) . ']';

        $this->validationRules['meta_sitemap.changefreq'] = 'if_exist|required|in_list[' . implode(',',
                MetaChangefreq::get('value')
            ) . ']';
    }

    /**
     * @return AvegaCmsModel
     */
    public function selectPages(): AvegaCmsModel
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.parent',
                'metadata.locale_id',
                'metadata.title',
                'metadata.url',
                'metadata.creator_id',
                'metadata.status',
                'metadata.meta_type',
                'metadata.in_sitemap',
                'metadata.use_url_pattern',
                'metadata.publish_at',
                'pm.title AS parent_title',
                'l.locale_name',
                'u.login AS author'
            ]
        )->join('locales AS l', 'l.id = metadata.locale_id')
            ->join('metadata AS pm', 'pm.id = metadata.parent', 'left')
            ->join('users AS u', 'u.id = metadata.creator_id', 'left')
            ->whereIn('metadata.meta_type', [MetaDataTypes::Main->value, MetaDataTypes::Page->value])
            ->where(['metadata.module_id' => 0]);

        return $this;
    }

    /**
     * @return AvegaCmsModel
     */
    public function selectRubrics(): AvegaCmsModel
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.locale_id',
                'metadata.title',
                'metadata.url',
                'metadata.creator_id',
                'metadata.status',
                'metadata.meta_type',
                'metadata.in_sitemap',
                'metadata.use_url_pattern',
                'metadata.publish_at',
                'l.locale_name',
                'u.login AS author'
            ]
        )->join('locales AS l', 'l.id = metadata.locale_id')
            ->join('users AS u', 'u.id = metadata.creator_id', 'left')
            ->where(['metadata.module_id' => 0, 'metadata.meta_type' => MetaDataTypes::Rubric->value]);

        return $this;
    }

    public function selectPosts(): AvegaCmsModel
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.parent',
                'metadata.locale_id',
                'metadata.title',
                'metadata.url',
                'metadata.creator_id',
                'metadata.status',
                'metadata.meta_type',
                'metadata.in_sitemap',
                'metadata.use_url_pattern',
                'metadata.publish_at',
                'pm.title AS rubric',
                'l.locale_name',
                'u.login AS author'
            ]
        )->join('locales AS l', 'l.id = metadata.locale_id')
            ->join('metadata AS pm', 'pm.id = metadata.parent')
            ->join('users AS u', 'u.id = metadata.creator_id', 'left')
            ->where(
                [
                    'metadata.module_id' => 0,
                    'metadata.meta_type' => MetaDataTypes::Post->value
                ]
            );

        return $this;
    }

    /**
     * @return AvegaCmsModel
     */
    public function selectMetaData(): AvegaCmsModel
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.parent',
                'metadata.locale_id',
                'metadata.module_id',
                'metadata.slug',
                'metadata.creator_id',
                'metadata.item_id',
                'metadata.title',
                'metadata.sort',
                'metadata.url',
                'metadata.meta',
                'metadata.extra_data',
                'metadata.status',
                'metadata.meta_type',
                'metadata.in_sitemap',
                'metadata.use_url_pattern',
                'c.anons',
                'c.content',
                'c.extra'
            ]
        )->join('content AS c', 'c.id = metadata.id')
            ->where(['metadata.module_id' => 0]);

        return $this;
    }

    /**
     * @param  int  $id
     * @return array|object|null
     */
    public function pageEdit(int $id): array|object|null
    {
        $this->selectMetaData()->builder()
            ->whereIn('metadata.meta_type', [MetaDataTypes::Main->value, MetaDataTypes::Page->value]);

        return $this->find($id);
    }

    /**
     * @param  int  $id
     * @return array|object|null
     */
    public function postEdit(int $id): array|object|null
    {
        $this->selectMetaData()->builder()
            ->where(['metadata.meta_type' => MetaDataTypes::Post->value]);

        return $this->find($id);
    }

    /**
     * @param  int  $id
     * @return array|object|null
     */
    public function rubricEdit(int $id): array|object|null
    {
        $this->selectMetaData()->builder()
            ->where(['metadata.meta_type' => MetaDataTypes::Rubric->value]);

        return $this->find($id);
    }

    /**
     * @return array
     */
    public function getRubrics(): array
    {
        return cache()->remember('rubricsList', 30 * DAY, function () {
            $this->builder()->select(['id', 'url', 'title', 'locale_id'])
                ->where(['meta_type' => MetaDataTypes::Rubric->value]);

            return $this->asArray()->findAll();
        });
    }

    /**
     * @return array
     */
    public function mainPages(): array
    {
        return cache()->remember('mainPages', 30 * DAY, function () {
            $this->builder()->select(['id', 'locale_id']);
            return $this->asArray()->findAll();
        });
    }

    /**
     * @param  Generator  $faker
     * @return array
     */
    public function fake(Generator &$faker): array
    {
        $title  = $faker->sentence();
        $status = MetaStatuses::get('value');
        $slug   = $faker->slug(rand(1, 6));

        return [
            'parent'        => 0,
            'locale_id'     => 0,
            'module_id'     => 0,
            'slug'          => $slug,
            'creator_id'    => 0,
            'item_id'       => 0,
            'title'         => $title,
            'sort'          => rand(1, 1000),
            'url'           => $slug,
            'meta'          => [
                'title'       => $title,
                'keywords'    => $faker->sentence(1),
                'description' => $faker->sentence(1),
                'breadcrumb'  => rand(0, 1) ? $faker->word() : '',

                'og:title' => $title,
                'og:type'  => 'website',
                'og:url'   => '',
                'og:image' => 'uploads/open_graph.png'
            ],
            'status'        => $status[array_rand($status)],
            'meta_type'     => '',
            'in_sitemap'    => rand(0, 1),
            'created_by_id' => 0,
            'publish_at'    => $faker->dateTimeBetween('-1 year', 'now', 'Asia/Omsk')->format('Y-m-d H:i:s')
        ];
    }

    /**
     * @param  int  $parentId
     * @return string
     */
    public function getParentPageUrl(int $parentId): string
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.use_url_pattern',
                'metadata.url AS rawUrl',
                'metadata.slug',
                'metadata.locale_id',
                'metadata.parent'
            ]
        )->whereIn('metadata.meta_type', [MetaDataTypes::Main->value, MetaDataTypes::Page->value]);

        if (($url = $this->find($parentId)) === null) {
            return '';
        }

        return match ($url->metaType) {
            MetaDataTypes::Main->value,
            MetaDataTypes::Page404->value => '',
            default                       => ($url->rawUrl === '/') ? '' : $url->rawUrl . '/',
        };
    }

    public function clearCache(array $data)
    {
        if (isset($data['meta_type'])) {
            if ($data['meta_type'] === MetaDataTypes::Rubric->value) {
                cache()->delete('rubricsList');
            }
        }
    }
}
