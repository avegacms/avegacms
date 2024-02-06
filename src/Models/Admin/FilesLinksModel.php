<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Enums\FileTypes;
use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Entities\FilesLinksEntity;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Validation\ValidationInterface;

class FilesLinksModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'files_links';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = FilesLinksEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'user_id',
        'parent',
        'module_id',
        'entity_id',
        'item_id',
        'uid',
        'type',
        'active',
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
        'id'            => ['rules' => 'if_exist|is_natural'],
        'user_id'       => ['rules' => 'if_exist|is_natural'],
        'parent'        => ['rules' => 'if_exist|is_natural'],
        'module_id'     => ['rules' => 'if_exist|is_natural'],
        'entity_id'     => ['rules' => 'if_exist|is_natural'],
        'item_id'       => ['rules' => 'if_exist|is_natural'],
        'uid'           => ['rules' => 'if_exist|permit_empty|max_length[64]'],
        'active'        => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'created_by_id' => ['rules' => 'if_exist|is_natural'],
        'updated_by_id' => ['rules' => 'if_exist|is_natural']
    ];
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
        'id'     => 'files_links.id',
        'parent' => 'files_links.parent',
        'module' => 'files_links.module_id',
        'entity' => 'files_links.entity_id',
        'item'   => 'files_links.item_id',
        'active' => 'files_links.active',
    ];
    protected array  $searchFields      = [];
    protected array  $sortableFields    = [];
    protected array  $filterCastsFields = [
        'id'     => 'int|array',
        'parent' => 'int',
        'module' => 'int',
        'entity' => 'int',
        'item'   => 'int',
        'active' => 'int'
    ];
    protected string $searchFieldAlias  = 'q';
    protected string $sortFieldAlias    = 's';
    protected string $sortDefaultFields = '';
    protected array  $filterEnumValues  = [];
    protected int    $limit             = 20;
    protected int    $maxLimit          = 100;

    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);
        $this->validationRules['type'] = [
            'rules' => 'if_exist|required|in_list[' . implode(',', FileTypes::get('value')) . ']'
        ];
    }

    /**
     * @param  array  $filter
     * @return $this
     */
    public function getFiles(array $filter): FilesLinksModel
    {
        $this->builder()->select(
            [
                'files_links.id',
                'files_links.user_id',
                'files_links.parent',
                'files_links.module_id',
                'files_links.entity_id',
                'files_links.item_id',
                'files_links.uid',
                'files_links.type',
                'files_links.active',
                'files.data',
                'files.provider_id',
                'files.provider'
            ]
        )->join('files', 'files.id = files_links.id')
            ->where(['files_links.type !=' => FileTypes::Directory->value]);

        return $this->filter($filter);
    }

    /**
     * @param  int|null  $parent
     * @param  int|null  $moduleId
     * @param  int|null  $entityId
     * @param  int|null  $itemId
     * @return int
     */
    public function getDirectoryData(?int $parent, ?int $moduleId, ?int $entityId, ?int $itemId): int
    {
        $this->builder()->where(
            [
                'type' => FileTypes::Directory->value,
                ...(! is_null($parent) ? ['parent_id' => $parent] : []),
                ...(! is_null($moduleId) ? ['module_id' => $moduleId] : []),
                ...(! is_null($entityId) ? ['entity_id' => $entityId] : []),
                ...(! is_null($itemId) ? ['item_id' => $itemId] : []),
            ]
        );

        return ! is_null($id = $this->findColumn('id')) ? (int) $id[0] : 0;
    }
}
