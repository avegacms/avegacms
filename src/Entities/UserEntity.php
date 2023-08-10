<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class UserEntity extends Entity
{
    protected $datamap = [
        'id'            => null,
        'login'         => null,
        'avatar'        => null,
        'phone'         => null,
        'email'         => null,
        'timezone'      => null,
        'password'      => null,
        'secret'        => null,
        'path'          => null,
        'expires'       => null,
        'reset'         => null,
        'extra'         => null,
        'status'        => null,
        'condition'     => null,
        'last_ip'       => null,
        'last_agent'    => null,
        'created_by_id' => null,
        'updated_by_id' => null,
        'active_at'     => null,
        'created_at'    => null,
        'updated_at'    => null,
        'deleted_at'    => null
    ];
    protected $dates   = ['active_at', 'created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id'            => 'integer',
        'login'         => 'string',
        'avatar'        => 'string',
        'phone'         => 'integer',
        'email'         => 'string',
        'timezone'      => 'string',
        'password'      => 'string',
        'secret'        => 'string',
        'path'          => 'string',
        'reset'         => 'integer',
        'extra'         => 'json-array',
        'status'        => 'string',
        'condition'     => 'string',
        'last_ip'       => 'string',
        'last_agent'    => 'string',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'active_at'     => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime'
    ];
}
