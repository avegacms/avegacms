<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class SettingsEntity extends Entity
{
    protected $datamap = [];
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
