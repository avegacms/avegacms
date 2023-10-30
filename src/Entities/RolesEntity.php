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
 * @property int|null $active
 * @property int|null $createdById
 * @property int|null $updatedById
 */
class RolesEntity extends Entity
{
    protected $datamap = [
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
        'active'        => 'integer',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];
}
