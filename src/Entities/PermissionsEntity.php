<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int|null $id
 * @property int|null $roleId
 * @property int|null $parent
 * @property int|null $moduleId
 * @property int|null $isModule
 * @property int|null $isSystem
 * @property int|null $isPlugin
 * @property string|null $slug
 * @property int|null $access
 * @property int|null $self
 * @property int|null $create
 * @property int|null $read
 * @property int|null $update
 * @property int|null $delete
 * @property int|null $moderated
 * @property int|null $settings
 * @property string|null $extra
 * @property int|null $createdById
 * @property int|null $updatedById
 */
class PermissionsEntity extends Entity
{
    protected $datamap = [
        'roleId'      => 'role_id',
        'moduleId'    => 'module_id',
        'isModule'    => 'is_module',
        'isSystem'    => 'is_system',
        'isPlugin'    => 'is_plugin',
        'createdById' => 'created_by_id',
        'updatedById' => 'updated_by_id'
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'role_id'       => 'integer',
        'parent'        => 'integer',
        'module_id'     => 'integer',
        'is_module'     => 'integer',
        'is_system'     => 'integer',
        'is_plugin'     => 'integer',
        'slug'          => 'string',
        'access'        => 'integer',
        'self'          => 'integer',
        'create'        => 'integer',
        'read'          => 'integer',
        'update'        => 'integer',
        'delete'        => 'integer',
        'moderated'     => 'integer',
        'settings'      => 'integer',
        'extra'         => 'json-array',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];
}
