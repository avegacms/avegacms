<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class UserAuthenticationEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
}
