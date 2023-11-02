<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int|null $roleId
 * @property int|null $userId
 * @property int|null $createdById
 */
class UserRolesEntity extends Entity
{
    protected $datamap = [
        'roleId' => 'role_id',
        'userId' => 'user_id'
    ];
    protected $dates   = ['created_at'];
    protected $casts   = [
        'role_id'       => 'integer',
        'user_id'       => 'integer',
        'created_by_id' => 'integer',
        'created_at'    => 'datetime',

        'id'       => 'integer',
        'login'    => 'string',
        'avatar'   => 'string',
        'phone'    => 'integer',
        'email'    => 'string',
        'timezone' => 'string',
        'status'   => 'string'
    ];
    
    public function getAvatar(): string
    {
        return ( ! empty($this->attributes['avatar'])) ? base_url('/uploads/users/' . $this->attributes['avatar']) : 'no_photo';
    }
}
