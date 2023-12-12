<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;
use AvegaCms\Utilities\Auth;

/**
 * @property int|null $id
 * @property string|null $login
 * @property string|null $avatar
 * @property int|null $phone
 * @property string|null $email
 * @property string|null $timezone
 * @property string|null $secret
 * @property string|null $password
 * @property string|null $path
 * @property array|null $profile
 * @property array|null $extra
 * @property string|null $status
 * @property string|null $condition
 * @property int|null $expires
 * @property int|null $createdById
 * @property int|null $updatedById
 * @property mixed|null $active_at
 */
class LoginEntity extends Entity
{
    protected $datamap = [
        'createdById' => 'created_by_id',
        'updatedById' => 'updated_by_id',
        'activeAt'    => 'active_at'
    ];
    protected $dates   = ['active_at', 'created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id'            => 'integer',
        'login'         => 'string',
        'avatar'        => '?string',
        'phone'         => 'string',
        'email'         => 'string',
        'timezone'      => 'string',
        'password'      => 'string',
        'secret'        => 'string',
        'path'          => 'string',
        'profile'       => 'json-array',
        'extra'         => 'json-array',
        'status'        => 'string',
        'expires'       => 'integer',
        'condition'     => 'string',
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
        $this->attributes['password'] = ! empty($pass) ? Auth::setPassword($pass) : '';

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAvatar(): string|null
    {
        return ( ! empty($this->attributes['avatar'])) ? base_url('/uploads/users/' . $this->attributes['avatar']) : null;
    }
}
