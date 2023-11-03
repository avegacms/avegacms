<?php

namespace AvegaCms\Entities;

use AvegaCms\Utils\Auth;
use CodeIgniter\Entity\Entity;

/**
 * @property int|null $id
 * @property string|null $login
 * @property string|null $avatar
 * @property int|null $phone
 * @property string|null $email
 * @property string|null $timezone
 * @property string|null $password
 * @property string|null $secret
 * @property string|null $path
 * @property string|null $profile
 * @property string|null $extra
 * @property string|null $status
 * @property string|null $condition
 * @property string|null $lastIp
 * @property string|null $lastAgent
 * @property mixed|null $activeAt
 * @property int|null $createdById
 * @property int|null $updatedById
 */
class UserEntity extends Entity
{
    protected $datamap = [
        'lastIp'      => 'last_ip',
        'lastAgent'   => 'last_agent',
        'activeAt'    => 'active_at',
        'createdById' => 'created_by_id',
        'updatedById' => 'updated_by_id'
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
        'profile'       => 'json-array',
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
