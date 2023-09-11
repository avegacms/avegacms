<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class LocalesEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'parent'        => 'integer',
        'slug'          => 'string',
        'locale'        => 'string',
        'locale_name'   => 'string',
        'home'          => 'string',
        'extra'         => 'json-array',
        'is_default'    => 'integer',
        'active'        => 'integer',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];
}
