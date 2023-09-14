<?php

namespace AvegaCms\Models\Frontend;

use AvegaCms\Enums\MetaDataTypes;
use AvegaCms\Enums\MetaStatuses;
use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Entities\PostRubricsEntity;

class PostRubricsModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'post_rubrics';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = PostRubricsEntity::class;
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
     * @param  array  $filter
     * @return $this
     */
    public function getRubricPosts(array $filter = []): PostRubricsModel
    {
        $date = date('Y-m-d H:i:s');

        $this->builder()->select(
            [
                'p.title',
                'p.url',
                'c.anons',
                'c.extra',
                'u.login AS author',
                'p.publish_at'
            ]
        )->join('metadata AS p', 'p.id = post_rubrics.post_id')
            ->join('metadata AS r', 'r.id = post_rubrics.rubric_id')
            ->join('content AS c', 'c.id = post_rubrics.post_id')
            ->join('users AS u', 'u.id = p.creator_id', 'left')
            ->groupStart()
            ->whereIn('r.status',
                [
                    MetaStatuses::Publish->value,
                    MetaStatuses::Future->value
                ]
            )->whereIn('p.status',
                [
                    MetaStatuses::Publish->value,
                    MetaStatuses::Future->value
                ]
            )
            ->where(
                [
                    //'post_rubrics.rubric_id' => $rubricId,
                    'p.meta_type'     => MetaDataTypes::Post->value,
                    'p.module_id'     => 0,
                    'r.module_id'     => 0,
                    'r.publish_at <=' => $date,
                    'p.publish_at <=' => $date
                ]
            )->groupEnd();

        $this->filter($filter);

        return $this;
    }
}
