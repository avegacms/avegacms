<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class AttributesEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
}
