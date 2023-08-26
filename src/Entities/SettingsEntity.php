<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class SettingsEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'entity'        => 'string',
        'slug'          => 'string',
        'key'           => 'string',
        'value'         => 'string',
        'default_value' => 'string',
        'return_type'   => 'string',
        'label'         => 'string',
        'context'       => 'string',
        'rules'         => 'string',
        'sort'          => 'integer',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];
}
