<?php

declare(strict_types=1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Enums\NavigationTypes;
use AvegaCms\Models\AvegaCmsModel;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Validation\ValidationInterface;
use Faker\Generator;

class NavigationsModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'navigations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'parent',
        'is_admin',
        'object_id',
        'locale_id',
        'nav_type',
        'meta',
        'title',
        'slug',
        'icon',
        'sort',
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
        'id'            => ['rules' => 'if_exist|is_natural'],
        'parent'        => ['rules' => 'if_exist|is_natural'],
        'is_admin'      => ['rules' => 'if_exist|permit_empty|max_length[255]'],
        'object_id'     => ['rules' => 'if_exist|is_natural'],
        'locale_id'     => ['rules' => 'if_exist|is_natural_no_zero'],
        'meta'          => ['rules' => 'if_exist|permit_empty'],
        'title'         => ['rules' => 'if_exist|required|string|max_length[512]'],
        'slug'          => ['rules' => 'if_exist|permit_empty|string|max_length[512]|unique_db_key[navigations.parent+is_admin+locale_id+nav_type+slug,id,{id}]'],
        'icon'          => ['rules' => 'if_exist|permit_empty|string|max_length[512]'],
        'sort'          => ['rules' => 'if_exist|is_natural_no_zero'],
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
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
    protected array $casts    = [
        'id'            => 'int',
        'parent'        => 'int',
        'is_admin'      => '?int-bool',
        'object_id'     => 'int',
        'locale_id'     => 'int',
        'meta'          => '?json-array',
        'active'        => '?int-bool',
        'created_by_id' => 'int',
        'updated_by_id' => 'int',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);

        $this->validationRules['nav_type'] = 'if_exist|required|in_list[' . implode(
            ',',
            NavigationTypes::get('value')
        ) . ']';
    }

    public function forEdit(int $id): array|object|null
    {
        $this->builder()->select(
            [
                'parent',
                'is_admin',
                'object_id',
                'locale_id',
                'nav_type',
                'meta',
                'title',
                'slug',
                'icon',
                'sort',
                'active',
            ]
        )->where(['is_admin' => 0]);

        return $this->find($id);
    }

    /**
     * @return list<int>
     */
    public function fake(Generator &$faker): array
    {
        helper(['url']);
        $word = $faker->word();

        return [
            'parent'        => 0,
            'is_admin'      => 1,
            'object_id'     => 0,
            'locale_id'     => 0,
            'nav_type'      => '',
            'meta'          => '',
            'title'         => $word,
            'slug'          => mb_url_title($word),
            'sort'          => 1,
            'active'        => 1,
            'created_by_id' => 1,
            'updated_by_id' => 0,
        ];
    }
}
