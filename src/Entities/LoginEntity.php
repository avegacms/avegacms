<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class LoginEntity extends Entity
{
    protected $datamap = [
        'id'            => null,
        'login'         => null,
        'avatar'        => null,
        'phone'         => null,
        'email'         => null,
        'timezone'      => null,
        'password'      => null,
        'secret'        => null,
        'path'          => null,
        'expires'       => null,
        'reset'         => null,
        'extra'         => null,
        'status'        => null,
        'created_by_id' => null,
        'updated_by_id' => null,
        'active_at'     => null,
        'created_at'    => null,
        'updated_at'    => null,
        'deleted_at'    => null
    ];
    protected $dates   = ['active_at', 'created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id'            => 'integer',
        'login'         => 'string',
        'avatar'        => 'string',
        'phone'         => 'integer',
        'email'         => 'string',
        'timezone'      => 'string',
        'password'      => 'string',
        'secret'        => 'string',
        'path'          => 'string',
        'reset'         => 'integer',
        'extra'         => 'json-array',
        'status'        => 'string',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'active_at'     => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime'
    ];

    public function setPassword(string $pass)
    {
        $this->attributes['password'] = password_hash($pass, PASSWORD_BCRYPT);

        return $this;
    }
}
