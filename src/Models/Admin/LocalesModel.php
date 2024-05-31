<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;

class LocalesModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'locales';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'parent',
        'slug',
        'locale',
        'locale_name',
        'home',
        'extra',
        'is_default',
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
        'parent'        => ['rules' => 'if_exist|is_natural'],
        'slug'          => ['rules' => 'if_exist|required|alpha_dash|max_length[20]|is_unique[locales.slug,id,{id}]'],
        'locale'        => ['rules' => 'if_exist|required|max_length[32]'],
        'locale_name'   => ['rules' => 'if_exist|required|max_length[100]'],
        'home'          => ['rules' => 'if_exist|required|max_length[255]'],
        'extra'         => ['rules' => 'if_exist|permit_empty'],
        'is_default'    => ['rules' => 'if_exist|is_natural|in_list[0,1]'],
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
    protected $afterInsert    = ['clearCacheLocales'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = ['clearCacheLocales'];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = ['clearCacheLocales'];

    protected array $casts = [
        'id'            => 'int',
        'parent'        => 'int',
        'extra'         => '?json-array',
        'is_default'    => '?int-bool',
        'active'        => '?int-bool',
        'created_by_id' => 'int',
        'updated_by_id' => 'int',
        'created_at'    => 'cmsdatetime',
        'updated_at'    => 'cmsdatetime'
    ];

    /**
     * @param  int  $id
     * @return array|object|null
     */
    public function forEdit(int $id): array|object|null
    {
        $this->builder()->select([
            'id',
            'parent',
            'slug',
            'locale',
            'locale_name',
            'home',
            'extra',
            'is_default',
            'active'
        ]);

        return $this->find($id);
    }

    /**
     * @param  bool  $active
     * @return array
     */
    public function getLocalesList(bool $active = true): array
    {
        return cache()->remember('Locales' . ($active ? 'Active' : 'All'), DAY * 30, function () use ($active) {
            $this->builder()->select(['id', 'slug', 'locale', 'home', 'locale_name', 'is_default', 'extra']);
            if ($active) {
                $this->builder()->where(['active' => 1]);
            }
            $locales = [];
            foreach ($this->findAll() as $locale) {
                $locales[] = (array) $locale;
            }
            return $locales;
        });
    }

    /**
     * @return void
     */
    protected function clearCacheLocales(): void
    {
        cache()->delete('LocalesActive');
        cache()->delete('LocalesAll');
        cache()->delete('settings_core');
        $this->getLocalesList();
        $this->getLocalesList(false);
    }

}
