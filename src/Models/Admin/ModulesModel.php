<?php

declare(strict_types=1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;

class ModulesModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'modules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'parent',
        'is_core',
        'is_plugin',
        'is_system',
        'key',
        'slug',
        'class_name',
        'name',
        'version',
        'description',
        'extra',
        'url_pattern',
        'in_sitemap',
        'active',
        'created_by_id',
        'updated_by_id',
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
        'is_core'       => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'parent'        => ['rules' => 'if_exist|is_natural'],
        'is_plugin'     => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'is_system'     => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'key'           => ['rules' => 'if_exist|max_length[144]|unique_db_key[modules.parent+is_core+key,id,{id}]'],
        'slug'          => ['rules' => 'if_exist|permit_empty|alpha_dash|max_length[64]'],
        'class_name'    => ['rules' => 'if_exist|permit_empty|string|max_length[128]'],
        'name'          => ['rules' => 'if_exist|permit_empty|max_length[255]'],
        'version'       => ['rules' => 'if_exist|permit_empty|max_length[64]'],
        'description'   => ['rules' => 'if_exist|permit_empty|max_length[2048]'],
        'extra'         => ['rules' => 'if_exist|permit_empty'],
        'url_pattern'   => ['rules' => 'if_exist|permit_empty|max_length[255]'],
        'in_sitemap'    => ['rules' => 'if_exist|is_natural|in_list[0,1,2]'],
        'active'        => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'created_by_id' => ['rules' => 'if_exist|is_natural'],
        'updated_by_id' => ['rules' => 'if_exist|is_natural'],
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
    protected array $casts    = [
        'id'            => 'int',
        'meta_id'       => '?int',
        'parent'        => 'int',
        'is_core'       => '?int-bool',
        'is_plugin'     => '?int-bool',
        'is_system'     => '?int-bool',
        'extra'         => '?json-array',
        'in_sitemap'    => '?int-bool',
        'active'        => '?int-bool',
        'created_by_id' => 'int',
        'updated_by_id' => 'int',
        'created_at'    => 'cmsdatetime',
        'updated_at'    => 'cmsdatetime',
        'num'           => 'int',
    ];

    public function getModules(int $parent = 0): array
    {
        $this->builder()->select([
            'modules.id',
            'modules.parent',
            'modules.is_plugin',
            'modules.is_system',
            'modules.key',
            'modules.slug',
            'modules.class_name',
            'modules.name',
            'modules.version',
            'modules.description',
            'modules.extra',
            'modules.in_sitemap',
            'modules.active',
            '(SELECT COUNT(m.id) FROM modules AS m WHERE m.parent = modules.id) AS num',
        ])->where(['modules.parent' => $parent]);

        return $this->findAll();
    }

    public function forEdit(int $id): array|object|null
    {
        $this->_getSelect()->builder();

        return $this->find($id);
    }

    /**
     * @return $this
     */
    public function parentsId(int $id = 0): ModulesModel
    {
        $this->builder()->where(['id' => $id])->orWhere(['parent' => $id]);

        return $this;
    }

    public function getModulesList(): array
    {
        $this->builder()->select(['id', 'name'])
            ->where(['is_core' => 0])
            ->orderBy('parent', 'ASC')
            ->orderBy('name', 'ASC');

        return array_column($this->findAll(), 'name', 'id');
    }

    public function getModulesMeta(): array
    {
        $modules = cache()->remember('ModulesMetaData', DAY * 30, function () {
            $this->builder()->select(
                [
                    'modules.id',
                    'modules.parent',
                    'modules.key',
                    'modules.slug',
                    'modules.class_name',
                    'modules.name',
                    'modules.url_pattern',
                    'modules.in_sitemap',
                    'modules.active',
                    'metadata.id AS meta_id',
                ]
            )->join('metadata', 'metadata.module_id = modules.id', 'left')
                // ->join('metadata', 'metadata.module_id = modules.id AND metadata.parent = 1', 'left')
                ->where(['modules.is_plugin' => 0]);

            $modules = [];

            if (($all = $this->findAll()) !== null) {
                foreach ($all as $item) {
                    $modules[$item->key] = (array) $item;
                }
            }

            return $modules;
        });

        if (empty($modules)) {
            cache()->delete('ModulesMetaData');
        }

        return $modules;
    }

    public function getModulesSiteMapSchema(): array
    {
        return cache()->remember('ModulesSiteMapSchema', DAY * 30, function () {
            $schema = [];
            if (($all = $this->_getModulesSiteMapSchema()) !== null) {
                $ids = [];

                foreach ($all as $item) {
                    $ids[]              = $item->id;
                    $schema[$item->key] = (array) $item;
                }
                if (($sub = $this->_getModulesSiteMapSchema($ids)) !== null) {
                    foreach ($schema as $k => $list) {
                        foreach ($sub as $item) {
                            if ($list['id'] === $item->parent) {
                                $schema[$k]['sub'][$item->slug] = (array) $item;
                            }
                        }
                    }
                }
            }

            return $schema;
        });
    }

    public function clearCache(): void
    {
        cache()->delete('ModulesMetaData');
        cache()->delete('ModulesSiteMapSchema');
        $this->getModulesMeta();
        $this->getModulesSiteMapSchema();
    }

    private function _getSelect(): ModulesModel
    {
        $this->builder()->select([
            'id',
            'parent',
            'is_plugin',
            'is_system',
            'key',
            'slug',
            'class_name',
            'name',
            'version',
            'description',
            'extra',
            'url_pattern',
            'in_sitemap',
            'active',
        ]);

        return $this;
    }

    private function _getModulesSiteMapSchema(array $ids = []): array
    {
        $this->builder()->select(
            [
                'modules.id',
                'modules.parent',
                'modules.key',
                'modules.slug',
                'modules.class_name',
                'modules.name',
                'modules.url_pattern',
                'modules.in_sitemap',
                'modules.active',
            ]
        )->where(
            [
                'modules.active'     => 1,
                'modules.in_sitemap' => 1,
                'modules.is_system'  => 0,
                'modules.is_plugin'  => 0,
            ]
        );

        if (! empty($ids)) {
            $this->builder()->whereIn('modules.parent', $ids);
        } else {
            $this->builder()->where(['modules.parent' => 0]);
        }

        return $this->findAll();
    }
}
