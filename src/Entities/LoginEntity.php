<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;
use AvegaCms\Utils\Auth;

class LoginEntity extends Entity
{
    protected $datamap = [];
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
        'extra'         => 'json-array',
        'status'        => 'string',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'active_at'     => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime'
    ];

    /**
     * @param  string  $pass
     * @return $this
     */
    public function setPassword(string $pass): LoginEntity
    {
        $this->attributes['password'] = Auth::setPassword($pass);

        return $this;
    }

    /**
     * @return string
     */
    public function getAvatar(): string
    {
        return ( ! empty($this->attributes['avatar'])) ? base_url('/uploads/users/' . $this->attributes['avatar']) : 'no_photo';
    }
}
