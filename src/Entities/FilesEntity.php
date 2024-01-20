<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int $id
 * @property int $providerId
 * @property string $provider
 * @property string $data
 * @property string $type
 * @property boolean $active
 * @property int $createdById
 * @property int $updatedById
 */
class FilesEntity extends Entity
{
    protected $datamap = [
        'providerId'  => 'provider_id',
        'createdById' => 'created_by_id',
        'updatedById' => 'updated_by_id',
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'provider_id'   => 'integer',
        'provider'      => 'string',
        'data'          => 'json-array',
        'type'          => 'string',
        'active'        => 'int-bool',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];
}
