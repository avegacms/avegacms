<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Frontend;

use AvegaCms\Models\AvegaCmsModel;

class ContentModel extends AvegaCmsModel
{
    protected $DBGroup        = 'default';
    protected $table          = 'content';
    protected $returnType     = 'object';
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

    protected array $casts = [
        'id'    => 'int',
        'extra' => '?json-array'
    ];

    /**
     * @param  int  $id
     * @return array|object|null
     */
    public function getContent(int $id): array|object|null
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.title',
                'content.anons',
                'content.content',
                'content.extra'
            ]
        )->join('metadata', 'metadata.id = content.id');

        return $this->find($id);
    }
}
