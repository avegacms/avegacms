<?php

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;
use AvegaCms\Entities\EmailTemplateEntity;

class EmailTemplateModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'email_templates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = EmailTemplateEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'slug',
        'locale_id',
        'is_system',
        'label',
        'subject',
        'content',
        'template',
        'active',
        'created_by_id',
        'updated_by_id'
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
        'label'         => ['rules' => 'if_exist|permit_empty|string|max_length[255]'],
        'slug'          => ['rules' => 'if_exist|required|permit_empty|alpha_dash|max_length[64]|unique_db_key[email_templates.locale_id+is_system+slug,id,{id}]'],
        'locale_id'     => ['rules' => 'if_exist|required|is_natural_no_zero'],
        'is_system'     => ['rules' => 'if_exist|required|is_natural'],
        'subject'       => ['rules' => 'if_exist|required|string'],
        'content'       => ['rules' => 'if_exist|permit_empty|string'],
        'template'      => ['rules' => 'if_exist|permit_empty|string|max_length[255]'],
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
    protected $afterInsert    = ['clearEmailTemplateCache'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = ['clearEmailTemplateCache'];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = ['clearEmailTemplateCache'];

    // AvegaCms filter settings
    protected array  $filterFields      = [
        'slug'     => 'slug',
        'label'    => 'label',
        'locale'   => 'locale_id',
        'template' => 'template'
    ];
    protected array  $searchFields      = [
        'label'    => 'label',
        'slug'     => 'slug',
        'template' => 'template'
    ];
    protected array  $sortableFields    = [
        'locale' => 'locale_id'
    ];
    protected array  $filterCastsFields = [
        'slug'     => 'string',
        'locale'   => 'int',
        'template' => 'string'
    ];
    protected string $searchFieldAlias  = 'q';
    protected string $sortFieldAlias    = 's';
    protected array  $filterEnumValues  = [];
    protected int    $limit             = 20;
    protected int    $maxLimit          = 100;

    public function getTemplates()
    {
        $this->builder()->select(
            [
                'id',
                'slug',
                'locale_id',
                'is_system',
                'label',
                'template',
                'active'
            ]
        );

        return $this;
    }

    /**
     * @param  int  $id
     * @return array|object|null
     */
    public function forEdit(int $id): array|object|null
    {
        $this->builder()->select(
            [
                'id',
                'slug',
                'locale_id',
                'is_system',
                'label',
                'subject',
                'content',
                'template',
                'active'
            ]
        );

        return $this->find($id);
    }

    /**
     * @param  array  $data
     * @return void
     */
    public function clearEmailTemplateCache(array $data): void
    {
        if (isset($data['slug']) && isset($data['locale_id'])) {
            cache()->delete('emailTemplate_' . $data['slug'] . '_' . $data['locale_id']);
        }
    }
}
