<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class TagsEntity extends Entity
{
    protected $datamap = [
        'id'            => null,
        'name'          => null,
        'slug'          => null,
        'active'        => null,
        'created_by_id' => null,
        'updated_by_id' => null,
        'created_at'    => null,
        'updated_at'    => null
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'name'          => 'string',
        'slug'          => 'string',
        'active'        => 'int-bool',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];
}
