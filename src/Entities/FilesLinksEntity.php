<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int $id
 * @property int $userId
 * @property int $parent
 * @property int $moduleId
 * @property int $entityId
 * @property int $itemId
 * @property string $uid
 * @property boolean $active
 * @property int $createdById
 * @property int $updatedById
 */
class FilesLinksEntity extends Entity
{
    protected $datamap = [
        'userId'      => 'user_id',
        'moduleId'    => 'module_id',
        'entityId'    => 'entity_id',
        'itemId'      => 'item_id',
        'createdById' => 'created_by_id',
        'updatedById' => 'updated_by_id',
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'user_id'       => 'integer',
        'parent'        => 'integer',
        'module_id'     => 'integer',
        'entity_id'     => 'integer',
        'item_id'       => 'integer',
        'uid'           => 'string',
        'active'        => 'int-bool',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];
}
