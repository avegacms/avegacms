<?php

namespace AvegaCms\Models\Frontend;

use AvegaCms\Enums\{MetaStatuses, MetaDataTypes};
use AvegaCms\Entities\ContentEntity;
use AvegaCms\Models\AvegaCmsModel;

class ContentModel extends AvegaCmsModel
{
    protected $DBGroup        = 'default';
    protected $table          = 'contents';
    protected $returnType     = ContentEntity::class;
    protected $useSoftDeletes = false;
    protected $protectFields  = true;
    protected $allowedFields  = [];

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

    public function getSubPages(int $id)
    {
        $this->builder()->select(
            [
                'contents.id',
                'm.title',
                'm.url',
                'contents.anons',
            ]
        )->join('metadata AS m', 'm.id = contents.id')
            ->whereIn('m.status',
                [
                    MetaStatuses::Publish->value,
                    MetaStatuses::Future->value
                ]
            )
            ->where(
                [
                    'm.parent'        => $id,
                    'm.meta_type'     => MetaDataTypes::Page->value,
                    'm.module_id'     => 0,
                    'm.publish_at <=' => date('Y-m-d H:i:s')
                ]
            )->orderBy('m.sort', 'ASC');

        return $this;
    }
}
