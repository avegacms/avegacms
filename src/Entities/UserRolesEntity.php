<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int|null $roleId
 * @property int|null $userId
 * @property int|null $createdById
 *
 * @property int|null $id
 * @property string|null $login
 * @property string|null $avatar
 * @property int|null $phone
 * @property string|null $email
 * @property string|null $timezone
 * @property string|null $status
 * @property array|null $profile
 * @property array|null $extra
 */
class UserRolesEntity extends Entity
{
    protected $datamap = [
        'roleId'      => 'role_id',
        'userId'      => 'user_id',
        'createdById' => 'created_by_id'
    ];
    protected $dates   = ['created_at'];
    protected $casts   = [
        'role_id'       => 'integer',
        'user_id'       => 'integer',
        'created_by_id' => 'integer',
        'created_at'    => 'datetime',

        'id'       => 'integer',
        'login'    => 'string',
        'avatar'   => '?string',
        'phone'    => 'integer',
        'email'    => 'string',
        'timezone' => 'string',
        'status'   => 'string',
        'profile'  => 'json-array',
        'extra'    => 'json-array',
    ];

    /**
     * @return string|null
     */
    public function getAvatar(): string|null
    {
        return ( ! empty($this->attributes['avatar'])) ? base_url('/uploads/users/' . $this->attributes['avatar']) : null;
    }
}
