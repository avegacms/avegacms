<?php

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Entities\MetaDataEntity;

class PostRubricsModel extends AvegaCmsModel
{
    protected $DBGroup        = 'default';
    protected $table          = 'post_rubrics';
    protected $returnType     = MetaDataEntity::class;
    protected $useSoftDeletes = false;
    protected $protectFields  = true;
    protected $allowedFields  = [
        'post_id',
        'rubric_id',
        'is_main'
    ];

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

    //AvegaCms model settings
    public array  $filterFields      = [
        'id'         => 'p.id',
        'locale_id'  => 'p.locale_id',
        'rubric_id'  => 'c.rubric_id',
        'login'      => 'u.login',
        'status'     => 'p.status',
        'publish_at' => 'p.publish_at'
    ];
    public array  $searchFields      = [
        'login' => 'u.login',
        'title' => 'p.title'
    ];
    public array  $sortableFields    = [
        'publish_at' => 'p.publish_at'
    ];
    public array  $filterCastsFields = [
        'id'         => 'int|array',
        'locale_id'  => 'int',
        'login'      => 'string',
        'title'      => 'string',
        'status'     => 'string',
        'publish_at' => 'string'
    ];
    public string $searchFieldAlias  = 'q';
    public string $sortFieldAlias    = 's';
    public int    $limit             = 20;
    public int    $maxLimit          = 100;

    /**
     * @return AvegaCmsModel
     */
    public function selectPosts(): AvegaCmsModel
    {
        $this->builder()->select(
            [
                'p.id',
                'p.parent',
                'p.locale_id',
                'p.title',
                'p.url',
                'p.creator_id',
                'p.status',
                'p.meta_type',
                'p.in_sitemap',
                'p.publish_at',
                'l.locale_name',
                'u.login AS author',
                'c.title AS rubric',
                'post_rubrics.rubric_id'
            ]
        )->join('metadata AS p', 'p.id = post_rubrics.post_id')
            ->join('metadata AS c', 'c.id = post_rubrics.rubric_id')
            ->join('locales AS l', 'l.id = p.locale_id')
            ->join('users AS u', 'u.id = p.creator_id', 'left')
            ->groupBy('post_rubrics.post_id');

        return $this;
    }
}
