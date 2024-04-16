<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Enums\FileTypes;
use AvegaCms\Models\AvegaCmsModel;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Validation\ValidationInterface;

class FilesLinksModel extends AvegaCmsModel
{
    protected bool $isFM        = false;

    protected $DBGroup          = 'default';
    protected $table            = 'files_links';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'object';
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
    protected $afterInsert    = ['updateDirectories'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = ['updateDirectories'];
    protected $beforeFind     = [];
    protected $afterFind      = ['updateFilesLinks'];
    protected $beforeDelete   = [];
    protected $afterDelete    = ['updateDirectories'];

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
        'item'   => 'int|array',
        'active' => 'int'
    ];
    protected string $searchFieldAlias  = 'q';
    protected string $sortFieldAlias    = 's';
    protected string $sortDefaultFields = '';
    protected array  $filterEnumValues  = [];
    protected int    $limit             = 20;
    protected int    $maxLimit          = 100;

    protected array $casts = [
        'id'            => 'int',
        'user_id'       => 'int',
        'parent'        => 'int',
        'module_id'     => 'int',
        'entity_id'     => 'int',
        'item_id'       => 'int',
        'data'          => 'json-array',
        'extra'         => '?json-array',
        'active'        => 'int-bool',
        'created_by_id' => 'int',
        'updated_by_id' => 'int',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'provider'      => 'int'
    ];

    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null, bool $isFM = false)
    {
        parent::__construct($db, $validation);
        $this->isFM                    = $isFM;
        $this->validationRules['type'] = [
            'rules' => 'if_exist|required|in_list[' . implode(',', FileTypes::get('value')) . ']'
        ];
    }

    /**
     * @param  array  $filter
     * @return object|null
     */
    public function getFiles(array $filter): object|null
    {
        $this->builder()->select(
            [
                'files_links.id',
                'files_links.user_id',
                'files_links.parent',
                'files_links.module_id',
                'files_links.entity_id',
                'files_links.item_id',
                'files_links.type',
                'files_links.active',
                'files.data',
                'files.provider',
                'files.created_at'
            ]
        )->join('files', 'files.id = files_links.id')
            ->groupStart()
            ->where(['files_links.type !=' => FileTypes::Directory->value])
            ->groupEnd();

        return $this->filter($filter);
    }

    /**
     * @param  int|null  $id
     * @param  int|null  $parent
     * @param  int|null  $moduleId
     * @param  int|null  $entityId
     * @param  int|null  $itemId
     * @return object|null
     */
    public function getDirectoryData(
        ?int $id,
        ?int $parent,
        ?int $moduleId,
        ?int $entityId,
        ?int $itemId
    ): object|null {
        $this->builder()->select(
            [
                'files.id',
                'files.data',
                'files.provider',
                'files.active',
                'files_links.user_id',
                'files_links.parent',
                'files_links.module_id',
                'files_links.entity_id',
                'files_links.item_id',
                'files.created_at'
            ]
        )->join('files', 'files.id = files_links.id')
            ->where(
                [
                    'files_links.type' => FileTypes::Directory->value,
                    ...(! is_null($id) ? ['files.id' => $id] : []),
                    ...(! is_null($parent) ? ['files_links.parent_id' => $parent] : []),
                    ...(! is_null($moduleId) ? ['files_links.module_id' => $moduleId] : []),
                    ...(! is_null($entityId) ? ['files_links.entity_id' => $entityId] : []),
                    ...(! is_null($itemId) ? ['files_links.item_id' => $itemId] : []),
                ]
            );

        return $this->first();
    }

    /**
     * @param  string  $path
     * @return object|null
     */
    public function getDirectories(string $path = ''): object|null
    {
        $directories = cache()->remember('FileManagerDirectories', 30 * DAY, function () {
            $this->builder()->select(
                [
                    'files.id',
                    'files.data',
                    'files.provider',
                    'files.type',
                    'files.active',
                    'files_links.user_id',
                    'files_links.parent',
                    'files_links.module_id',
                    'files_links.entity_id',
                    'files_links.item_id',
                    'files.created_at'
                ]
            )->join('files', 'files.id = files_links.id')
                ->where(['files_links.type' => FileTypes::Directory->value]);

            $result      = $this->findAll();
            $directories = [];
            if ( ! empty($result)) {
                foreach ($result as $item) {
                    $url = $item->data;
                    unset($item->data);
                    $item->url               = $url['url'];
                    $directories[$item->url] = $item;
                }
            }

            return (object) $directories;
        });

        return is_null($directories) ? null : (empty($path) ? $directories : ($directories->{$path} ?? null));
    }

    /**
     * @param  array  $data
     * @return void
     */
    public function updateDirectories(array $data): void
    {
        if (isset($data['data']['type']) && $data['data']['type'] === FileTypes::Directory->value) {
            cache()->delete('FileManagerDirectories');
            $this->getDirectories();
        }
    }

    public function updateFilesLinks(array $data)
    {
        if (in_array($data['method'], ['first', 'find', 'findAll'])) {
            foreach ($data['data'] as $file) {
                switch ($file->type) {
                    case FileTypes::Directory->value:
                        break;
                    case FileTypes::File->value:
                        $file->data['path']     = base_url($file->data['path']);
                        $file->data['sizeText'] = $this->_getTextFileSize($file->data['size']);
                        break;
                    case FileTypes::Image->value:

                        $file->data['sizeText']         = $this->_getTextFileSize($file->data['size']);
                        $file->data['path']['original'] = base_url($file->data['path']['original']);
                        if ( ! empty($file->data['path']['webp'])) {
                            $file->data['path']['webp'] = base_url($file->data['path']['webp']);
                        }

                        // Проверяем признак запроса от файлового менеджера
                        if ($this->isFM) {
                            $file->data['thumb']['original'] = base_url($file->data['thumb']['original']);
                            if ( ! empty($file->data['thumb']['webp'])) {
                                $file->data['thumb']['webp'] = base_url($file->data['thumb']['webp']);
                            }
                        } else {
                            unset($file->data['thumb']);
                        }

                        if ( ! empty($file->data['variants'] ?? '')) {
                            foreach ($file->data['variants'] as $k => $variants) {
                                if (is_array($variants)) {
                                    foreach ($variants as $pointer => $variant) {
                                        $file->data['variants'][$k][$pointer] = base_url($variant);
                                    }
                                } else {
                                    $file->data['variants'][$k] = base_url($file->data['variants'][$k]);
                                }
                            }
                        }

                        break;
                }

                $file->created_at = date('d.m.Y H:i', $file->created_at->date);
            }
        }

        return $data;
    }

    /**
     * @param  int  $size
     * @return string
     */
    private function _getTextFileSize(int $size): string
    {
        if (($size = ($size / 1024)) < 1024) {
            return round($size, 1) . ' ' . lang('Uploader.sizes.kb');
        }
        if (($size = (($size / 1024) / 1024)) < 1024) {
            return round($size, 1) . ' ' . lang('Uploader.sizes.mb');
        }
        return round($size, 1) . ' ' . lang('Uploader.sizes.gb');
    }
}