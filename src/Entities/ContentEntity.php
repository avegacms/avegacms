<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class ContentEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = [];
    protected $casts   = [
        'id'      => 'integer',
        'anons'   => 'string',
        'content' => 'string',
        'extra'   => 'json-array'
    ];
}
