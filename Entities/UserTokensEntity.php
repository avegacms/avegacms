<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class UserTokensEntity extends Entity
{
    protected $datamap = [
        'id'            => null,
        'user_id'       => null,
        'access_token'  => null,
        'refresh_token' => null,
        'expires'       => null,
        'user_ip'       => null,
        'user_agent'    => null,
        'created_at'    => null,
        'updated_at'    => null
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'string',
        'user_id'       => 'integer',
        'access_token'  => 'string',
        'refresh_token' => 'string',
        'expires'       => 'integer',
        'user_ip'       => 'string',
        'user_agent'    => 'string',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];
}
