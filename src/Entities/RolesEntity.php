<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int|null $id
 * @property string|null $role
 * @property string|null $description
 * @property string|null $color
 * @property string|null $path
 * @property int|null $priority
 * @property int|bool|null $selfAuth
 * @property int $moduleId
 * @property string|bool|null $roleEntity
 * @property int|bool|null $active
 * @property int|null $createdById
 * @property int|null $updatedById
 */
class RolesEntity extends Entity
{
    protected $datamap = [
        'selfAuth'    => 'self_auth',
        'moduleId'    => 'module_id',
        'roleEntity'  => 'role_entity',
        'createdById' => 'created_by_id',
        'updatedById' => 'updated_by_id'
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'role'          => 'string',
        'description'   => 'string',
        'color'         => 'string',
        'path'          => 'string',
        'priority'      => 'integer',
        'self_auth'     => 'int-bool',
        'module_id'     => 'integer',
        'role_entity'   => 'string',
        'active'        => 'int-bool',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];
}
