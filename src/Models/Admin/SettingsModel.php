<?php

declare(strict_types=1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Enums\FieldsReturnTypes;
use AvegaCms\Models\AvegaCmsModel;

class SettingsModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'locale_id',
        'module_id',
        'is_core',
        'entity',
        'slug',
        'key',
        'value',
        'default_value',
        'return_type',
        'label',
        'context',
        'sort',
        'created_at',
        'updated_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'id'            => ['rules' => 'if_exist|is_natural_no_zero'],
        'locale_id'     => ['rules' => 'if_exist|is_natural'],
        'module_id'     => ['rules' => 'if_exist|is_natural'],
        'entity'        => ['rules' => 'if_exist|required|alpha_numeric|max_length[36]'],
        'slug'          => ['rules' => 'if_exist|required|alpha_numeric|max_length[36]|unique_db_key[settings.module_id+entity+slug+key,id,{id}]'],
        'key'           => ['rules' => 'if_exist|permit_empty|alpha_numeric|max_length[36]'],
        'value'         => ['rules' => 'if_exist|permit_empty'],
        'default_value' => ['rules' => 'if_exist|permit_empty'],
        'label'         => ['rules' => 'if_exist|permit_empty|string|max_length[255]'],
        'context'       => ['rules' => 'if_exist|permit_empty|string|max_length[512]'],
        'sort'          => ['rules' => 'if_exist|is_natural'],
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks  = true;
    protected $beforeInsert    = ['setCreatedById'];
    protected $afterInsert     = ['dropSettingsCache'];
    protected $beforeUpdate    = ['setUpdatedById'];
    protected $afterUpdate     = ['dropSettingsCache'];
    protected $beforeFind      = [];
    protected $afterFind       = [];
    protected $beforeDelete    = [];
    protected $afterDelete     = [];
    public array $filterFields = [
        'id'     => 'settings.id',
        'locale' => 'settings.locale_id',
        'entity' => 'settings.entity',
        'slug'   => 'settings.slug',
        'key'    => 'settings.key',
        'label'  => 'settings.label',
    ];
    public array $searchFields      = [];
    public array $sortableFields    = [];
    public array $filterCastsFields = [
        'id'     => 'int|array',
        'locale' => 'int',
        'entity' => 'string',
        'slug'   => 'string',
        'key'    => 'string',
        'label'  => 'string',
    ];
    public string $searchFieldAlias = 'q';
    public string $sortFieldAlias   = 's';
    public int $limit               = 20;
    public int $maxLimit            = 100;
    protected array $casts          = [
        'id'            => 'int',
        'locale'        => 'int',
        'module_id'     => 'int',
        'is_core'       => '?int-bool',
        'sort'          => 'int',
        'created_by_id' => 'int',
        'updated_by_id' => 'int',
        'created_at'    => 'cmsdatetime',
        'updated_at'    => 'cmsdatetime',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->validationRules['return_type'] = 'if_exist|in_list[' . implode(
            ',',
            FieldsReturnTypes::get('value')
        ) . ']';
    }

    public function selectSettings(): AvegaCmsModel
    {
        $this->builder()->select(
            [
                'settings.id',
                'settings.locale_id',
                'settings.module_id',
                'settings.is_core',
                'settings.entity',
                'settings.slug',
                'settings.key',
                'settings.label AS lang_label',
                'IFNULL(m.slug, "AvegaCms Core") AS module_slug',
                'IFNULL(m.name, "AvegaCms Core") AS module_name',
            ]
        )->join('modules AS m', 'm.id = settings.module_id', 'left');

        return $this;
    }

    /**
     * @param int $localeId
     */
    public function getSettings(string $entity): array
    {
        $this->builder()->select(
            [
                'locale_id',
                'slug',
                'key',
                'value',
                'return_type',
            ]
        )->where('entity', $entity)
            ->orderBy('sort', 'ASC')
            ->orderBy('slug', 'ASC');

        $settings = [];

        if (! empty($result = $this->findAll())) {
            foreach ($result as $item) {
                if (! empty($item->slug) && ! empty($item->key)) {
                    $settings[$item->slug][$item->key] = [
                        'value'       => $item->value,
                        'return_type' => $item->return_type,
                    ];
                } else {
                    $settings[$item->slug] = [
                        'value'       => $item->value,
                        'return_type' => $item->return_type,
                    ];
                }
            }
            unset($result);
        }

        return $settings;
    }

    public function getId(string $entity, ?string $slug = null, ?string $property = null): int
    {
        $this->builder()->where('entity', $entity);
        if (! empty($slug)) {
            $this->builder()->where('slug', $slug);
            if (! empty($property)) {
                $this->builder()->where('key', $property);
            }
        }

        return $this->asArray()->findColumn('id')[0] ?? 0;
    }

    public function forEdit(int $id): array|object|null
    {
        $this->builder()->select([
            'id',
            'locale_id',
            'module_id',
            'is_core',
            'entity',
            'slug',
            'key',
            'value',
            'default_value',
            'return_type',
            'label',
            'context',
            'sort',
        ]);

        return $this->find($id);
    }

    protected function dropSettingsCache(): void
    {
        cache()->deleteMatching('settings_*');
    }

    protected function setCreatedById(array $data): array
    {
        $data['data']['updated_by_id'] = $data['data']['created_by_id'] = $data['data']['created_by_id'] ?? 1;

        return $data;
    }

    protected function setUpdatedById(array $data): array
    {
        $data['data']['updated_by_id'] ??= 2;

        return $data;
    }
}
