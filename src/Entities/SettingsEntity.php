<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int|null $id
 * @property int|null $moduleId
 * @property int|null $isCore
 * @property string|null $entity
 * @property string|null $key
 * @property string|null $value
 * @property string|null $defaultValue
 * @property string|null $returnType
 * @property string|null $label
 * @property string|null $langLabel
 * @property string|null $context
 * @property string|null $langContext
 * @property int|null $sort
 * @property int|null $createdById
 * @property int|null $updatedById
 */
class SettingsEntity extends Entity
{
    protected $datamap = [
        'moduleId'     => 'module_id',
        'isCore'       => 'is_core',
        'defaultValue' => 'default_value',
        'returnType'   => 'return_type',
        'langLabel'    => 'lang_label',
        'langContext'  => 'lang_context',
        'createdById'  => 'created_by_id',
        'updatedById'  => 'updated_by_id'
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'module_id'     => 'integer',
        'is_core'       => 'integer',
        'entity'        => 'string',
        'slug'          => 'string',
        'key'           => 'string',
        'value'         => 'string',
        'default_value' => 'string',
        'return_type'   => 'string',
        'label'         => 'string',
        'lang_label'    => 'string',
        'context'       => 'string',
        'lang_context'  => 'string',
        'sort'          => 'integer',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];

    public function getLangLabel(): string
    {
        return lang($this->attributes['lang_label']);
    }

    public function getLangContext(): string
    {
        return lang($this->attributes['lang_context']);
    }
}
