<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class PermissionsEntity extends Entity
{
    protected $datamap = [
        'id'            => null,
        'role_id'       => null,
        'parent'        => null,
        'module_id'     => null,
        'module_slug'   => null,
        'access'        => null,
        'show'          => null,
        'self'          => null,
        'create'        => null,
        'read'          => null,
        'update'        => null,
        'delete'        => null,
        'moderated'     => null,
        'settings'      => null,
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
        'module_slug'   => 'string',
        'access'        => 'integer',
        'show'          => 'integer',
        'self'          => 'integer',
        'create'        => 'integer',
        'read'          => 'integer',
        'update'        => 'integer',
        'delete'        => 'integer',
        'moderated'     => 'integer',
        'settings'      => 'integer',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];
}
