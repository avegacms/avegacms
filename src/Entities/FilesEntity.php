<?php

namespace AvegaCms\Entities;

/**
 * @property int $id
 * @property string $provider
 * @property string $data
 * @property string $type
 * @property boolean $active
 * @property int $createdById
 * @property int $updatedById
 */
class FilesEntity extends AvegaCmsEntity
{
    protected $datamap = [
        'createdById' => 'created_by_id',
        'updatedById' => 'updated_by_id',
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'provider'      => 'integer',
        'data'          => 'json-array',
        'type'          => 'string',
        'active'        => 'int-bool',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];
}
