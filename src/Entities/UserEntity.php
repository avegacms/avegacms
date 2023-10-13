<?php

namespace AvegaCms\Entities;

use AvegaCms\Utils\Auth;
use CodeIgniter\Entity\Entity;

class UserEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['active_at', 'created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id'            => 'integer',
        'login'         => 'string',
        'avatar'        => 'string',
        'phone'         => 'string',
        'email'         => 'string',
        'timezone'      => 'string',
        'password'      => 'string',
        'secret'        => 'string',
        'path'          => 'string',
        'extra'         => 'json-array',
        'status'        => 'string',
        'condition'     => 'string',
        'last_ip'       => 'string',
        'last_agent'    => 'string',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'active_at'     => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime'
    ];

    /**
     * @param  string  $pass
     * @return Entity
     */
    public function setPassword(string $pass): Entity
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
