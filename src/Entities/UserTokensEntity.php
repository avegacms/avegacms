<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property string|null $id
 * @property int|null $userId
 * @property string|null $accessToken
 * @property string|null $refreshToken
 * @property int|null $expires
 * @property int|null $userIp
 * @property string|null $userAgent
 */
class UserTokensEntity extends Entity
{
    protected $datamap = [
        'userId'       => 'user_id',
        'accessToken'  => 'access_token',
        'refreshToken' => 'refresh_token',
        'userIp'       => 'user_ip',
        'userAgent'    => 'user_agent'
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
