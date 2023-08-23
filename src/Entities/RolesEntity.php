<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class RolesEntity extends Entity
{
    protected $datamap = [];
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
