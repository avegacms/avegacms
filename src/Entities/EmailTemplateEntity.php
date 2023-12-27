<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int|null $id
 * @property string|null $label
 * @property string|null $slug
 * @property int|null $moduleId
 * @property int|null $localeId
 * @property int|null $isSystem
 * @property string|null $subject
 * @property string|null $content
 * @property string|null $variables
 * @property string|null $template
 * @property int|null $active
 * @property int|null $createdById
 * @property int|null $updatedById
 */
class EmailTemplateEntity extends Entity
{
    protected $datamap = [
        'moduleId'    => 'module_id',
        'localeId'    => 'locale_id',
        'isSystem'    => 'is_system',
        'createdById' => 'created_by_id',
        'updatedById' => 'updated_by_id'
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'module_id'     => 'integer',
        'label'         => 'string',
        'slug'          => 'string',
        'locale_id'     => 'integer',
        'is_system'     => 'integer',
        'subject'       => 'string',
        'content'       => 'string',
        'variables'     => 'string',
        'template'      => 'string',
        'active'        => 'int-bool',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];

}
