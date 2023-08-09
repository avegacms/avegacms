<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class RolesEntity extends Entity
{
    protected $datamap = [
        'id'            => null,
        'role'          => null,
        'description'   => null,
        'color'         => null,
        'path'          => null,
        'priority'      => null,
        'active'        => null,
        'created_by_id' => null,
        'updated_by_id' => null,
        'created_at'    => null,
        'updated_at'    => null
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'role'          => 'string',
        'description'   => 'string',
        'color'         => 'string',
        'path'          => 'string',
        'priority'      => 'integer',
        'active'        => 'integer',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];
}
