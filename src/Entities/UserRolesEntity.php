<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class UserRolesEntity extends Entity
{
    protected $datamap = [
        'role_id'       => null,
        'user_id'       => null,
        'created_by_id' => null,
        'created_at'    => null
    ];
    protected $dates   = ['created_at'];
    protected $casts   = [
        'role_id'       => 'integer',
        'user_id'       => 'integer',
        'created_by_id' => 'integer',
        'created_at'    => 'datetime'
    ];
}
