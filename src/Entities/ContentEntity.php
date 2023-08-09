<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class ContentEntity extends Entity
{
    protected $datamap = [
        'id'            => null,
        'anons'         => null,
        'content'       => null,
        'extra'         => null,
        'created_by_id' => null,
        'updated_by_id' => null,
        'created_at'    => null,
        'updated_at'    => null
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'anons'         => 'string',
        'content'       => 'string',
        'extra'         => 'json-array',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];
}
