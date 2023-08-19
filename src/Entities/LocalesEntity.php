<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class LocalesEntity extends Entity
{
    protected $datamap = [
        'id'            => null,
        'slug'          => null,
        'locale'        => null,
        'locale_name'   => null,
        'home'          => null,
        'extra'         => null,
        'is_default'    => null,
        'active'        => null,
        'created_by_id' => null,
        'updated_by_id' => null,
        'created_at'    => null,
        'updated_at'    => null,
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
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
