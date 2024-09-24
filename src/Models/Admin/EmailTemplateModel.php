<?php

declare(strict_types=1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Models\AvegaCmsModel;

class EmailTemplateModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'email_templates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'slug',
        'module_id',
        'is_system',
        'label',
        'subject',
        'content',
        'variables',
        'view',
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
        'module_id'     => ['rules' => 'if_exist|is_natural'],
        'label'         => ['rules' => 'if_exist|permit_empty|string|max_length[255]'],
        'slug'          => ['rules' => 'if_exist|required|permit_empty|alpha_dash|max_length[64]|unique_db_key[email_templates.module_id+is_system+slug,id,{id}]'],
        'is_system'     => ['rules' => 'if_exist|required|is_natural'],
        'subject'       => ['rules' => 'if_exist|required|string'],
        'content'       => ['rules' => 'if_exist|permit_empty|string'],
        'variables'     => ['rules' => 'if_exist|permit_empty|string'],
        'view'          => ['rules' => 'if_exist|permit_empty|string|max_length[255]'],
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
    protected $afterInsert    = ['clearEmailTemplateCache'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = ['clearEmailTemplateCache'];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = ['clearEmailTemplateCache'];

    // AvegaCms filter settings
    protected array $filterFields = [
        'slug'  => 'email_templates.slug',
        'label' => 'email_templates.label',
        'view'  => 'email_templates.view',
    ];
    protected array $searchFields = [
        'label',
        'slug',
        'view',
    ];
    protected array $sortableFields    = [];
    protected array $filterCastsFields = [
        'slug'   => 'string',
        'locale' => 'int',
        'view'   => 'string',
    ];
    protected string $searchFieldAlias = 'q';
    protected string $sortFieldAlias   = 's';
    protected array $filterEnumValues  = [];
    protected int $limit               = 20;
    protected int $maxLimit            = 100;
    protected array $casts             = [
        'id'            => 'int',
        'module_id'     => 'int',
        'is_system'     => 'int',
        'content'       => 'json-array',
        'variables'     => 'json-array',
        'active'        => '?int-bool',
        'created_by_id' => 'int',
        'updated_by_id' => 'int',
        'created_at'    => 'cmsdatetime',
        'updated_at'    => 'cmsdatetime',
    ];

    /**
     * @return $this
     */
    public function getTemplates(): EmailTemplateModel
    {
        $this->builder()->select(
            [
                'email_templates.id',
                'email_templates.slug',
                'email_templates.module_id',
                'email_templates.is_system',
                'email_templates.label',
                'email_templates.view',
                'email_templates.active',
            ]
        );

        return $this;
    }

    public function forEdit(int $id): array|object|null
    {
        $this->builder()->select(
            [
                'email_templates.id',
                'email_templates.slug',
                'email_templates.module_id',
                'email_templates.is_system',
                'email_templates.label',
                'email_templates.subject',
                'email_templates.content',
                'email_templates.view',
                'email_templates.active',
            ]
        );

        return $this->find($id);
    }

    public function getEmailTemplate(string $slug): array|object|null
    {
        return cache()->remember(
            'emailTemplate' . ucfirst($slug),
            30 * DAY,
            function () use ($slug) {
                $this->builder()->select(
                    [
                        'email_templates.subject',
                        'email_templates.content',
                        'email_templates.view',
                    ]
                )->where(
                    [
                        'email_templates.slug'   => $slug,
                        'email_templates.active' => 1,
                    ]
                );

                return $this->first() ?? [];
            }
        );
    }

    public function clearEmailTemplateCache(array $data): void
    {
        if (isset($data['slug'])) {
            cache()->delete('emailTemplate' . ucfirst($data['slug']));
        }
    }
}
