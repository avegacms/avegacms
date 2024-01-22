<?php

namespace AvegaCms\Entities;

/**
 * @property int|null $id
 * @property string|null $label
 * @property string|null $slug
 * @property int|null $moduleId
 * @property int|null $isSystem
 * @property string|null $subject
 * @property string|null $content
 * @property string|null $variables
 * @property string|null $view
 * @property int|null $active
 * @property int|null $createdById
 * @property int|null $updatedById
 */
class EmailTemplateEntity extends AvegaCmsEntity
{
    protected $datamap = [
        'moduleId'    => 'module_id',
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
        'is_system'     => 'integer',
        'subject'       => 'json-array',
        'content'       => 'json-array',
        'variables'     => 'json-array',
        'view'          => 'string',
        'active'        => '?int-bool',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];

}
