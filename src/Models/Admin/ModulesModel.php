<?php

namespace AvegaCms\Models\Admin;

use CodeIgniter\Model;
use AvegaCms\Entities\ModulesEntity;

class ModulesModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'modules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = ModulesEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'parent',
        'is_core',
        'is_plugin',
        'is_system',
        'slug',
        'name',
        'version',
        'description',
        'extra',
        'in_sitemap',
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
        'id'            => ['rules' => 'if_exist|is_natural_no_zero'],
        'is_core'       => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'parent'        => ['rules' => 'if_exist|is_natural'],
        'is_plugin'     => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'is_system'     => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
        'slug'          => ['rules' => 'if_exist|permit_empty|alpha_dash|max_length[64]|unique_db_key[modules.parent+is_core+slug,id,{id}]'],
        'name'          => ['rules' => 'if_exist|permit_empty|max_length[255]'],
        'version'       => ['rules' => 'if_exist|permit_empty|max_length[64]'],
        'description'   => ['rules' => 'if_exist|permit_empty|max_length[2048]'],
        'extra'         => ['rules' => 'if_exist|permit_empty'],
        'in_sitemap'    => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
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
    protected $afterInsert    = ['clearCache'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = ['clearCache'];
    protected $beforeFind     = [];
    protected $afterFind      = ['clearCache'];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * @param  int  $parent
     * @return array
     */
    public function getModules(int $parent = 0): array
    {
        $this->builder()->select([
            'modules.id',
            'modules.parent',
            'modules.is_plugin',
            'modules.is_system',
            'modules.slug',
            'modules.name',
            'modules.version',
            'modules.description',
            'modules.extra',
            'modules.in_sitemap',
            'modules.active',
            '(SELECT COUNT(m.id) FROM modules AS m WHERE m.parent = modules.id) AS num'
        ])->where(['modules.parent' => $parent]);

        return $this->findAll();
    }

    /**
     * @param  int  $id
     * @return array|object|null
     */
    public function forEdit(int $id): array|object|null
    {
        $this->_getSelect()->builder();

        return $this->find($id);
    }

    public function parentsId(int $id = 0)
    {
        $this->builder()->where(['id' => $id])->orWhere(['parent' => $id]);

        return $this;
    }

    /**
     * @return array
     */
    public function getModulesList(): array
    {
        $this->builder()->select(['id', 'name'])
            ->where(['is_core' => 0])
            ->orderBy('parent', 'ASC')
            ->orderBy('name', 'ASC');

        return array_column($this->findAll(), 'name', 'id');
    }

    /**
     * @return array
     */
    public function getModulesMeta(): array
    {
        return cache()->remember('ModulesMetaData', DAY * 30, function () {
            $this->builder()->select(
                [
                    'id',
                    'parent',
                    'slug',
                    'name',
                    'active'
                ]
            )->where(
                [
                    'is_core'   => 0,
                    'is_system' => 0,
                    'is_plugin' => 0
                ]
            );

            $all = $this->findAll();

            $modules = [];

            foreach ($all as $item) {
                if ($item->parent === 0) {
                    $modules[$item->slug] = $item->toArray();
                    foreach ($all as $subItem) {
                        if ($subItem->parent === $item->id) {
                            $modules[$item->slug][$subItem->slug] = $subItem->toArray();
                        }
                    }
                }
            }

            return $modules;
        });
    }

    /**
     * @return void
     */
    public function clearCache(): void
    {
        cache()->delete('ModulesMetaData');
        $this->getModulesMeta();
    }

    /**
     * @return Model
     */
    private function _getSelect(): Model
    {
        $this->builder()->select([
            'id',
            'parent',
            'is_plugin',
            'is_system',
            'slug',
            'name',
            'version',
            'description',
            'extra',
            'in_sitemap',
            'active',
        ]);

        return $this;
    }
}
