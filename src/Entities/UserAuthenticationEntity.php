<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class UserAuthenticationEntity extends Entity
{
    protected $datamap = [
        'id'            => null,
        'role_id'       => null,
        'parent'        => null,
        'module_id'     => null,
        'is_system'     => null,
        'is_plugin'     => null,
        'slug'          => null,
        'access'        => null,
        'self'          => null,
        'create'        => null,
        'read'          => null,
        'update'        => null,
        'delete'        => null,
        'moderated'     => null,
        'settings'      => null,
        'extra'         => null,
        'created_by_id' => null,
        'updated_by_id' => null,
        'created_at'    => null,
        'updated_at'    => null
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'role_id'       => 'integer',
        'parent'        => 'integer',
        'module_id'     => 'integer',
        'is_system'     => 'integer',
        'is_plugin'     => 'integer',
        'slug'          => 'string',
        'access'        => 'boolean',
        'self'          => 'boolean',
        'create'        => 'boolean',
        'read'          => 'boolean',
        'update'        => 'boolean',
        'delete'        => 'boolean',
        'moderated'     => 'boolean',
        'settings'      => 'boolean',
        'extra'         => 'json-array',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];
}
