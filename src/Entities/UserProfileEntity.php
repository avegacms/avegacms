<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int|null $userId
 * @property string|null $timezone
 * @property string|null $login
 * @property string|null $status
 * @property string|null $condition
 * @property string|null $avatar
 * @property int|null $phone
 * @property string|null $email
 * @property string|array|null $profile
 * @property string|array|null $extra
 * @property int|null $roleId
 * @property string|null $role
 */
class UserProfileEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'userId'    => 'integer',
        'timezone'  => 'string',
        'login'     => 'string',
        'status'    => 'string',
        'condition' => 'string',
        'avatar'    => '?string',
        'phone'     => 'integer',
        'email'     => 'string',
        'profile'   => 'json-array',
        'extra'     => 'json-array',
        'roleId'    => 'integer',
        'role'      => 'string'
    ];

    /**
     * @return string|null
     */
    public function getAvatar(): string|null
    {
        return ( ! empty($this->attributes['avatar'])) ? base_url('/uploads/users/' . $this->attributes['avatar']) : null;
    }
}
