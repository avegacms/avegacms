<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class SessionsEntity extends Entity
{
    protected $datamap = [
        'userId'    => 'user_id',
        'ipAddress' => 'ip_address',
    ];
    protected $dates   = [];
    protected $casts   = [
        'id'         => 'string',
        'user_id'    => 'integer',
        'ip_address' => 'string',
        'timestamp'  => 'datetime',
        'data'       => 'string',
    ];
}
