<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;
use AvegaCms\Entities\Cast\NavigationMetaCast;

class NavigationsEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'parent'        => 'integer',
        'is_admin'      => 'integer',
        'object_id'     => 'integer',
        'locale_id'     => 'integer',
        'nav_type'      => 'string',
        'meta'          => 'menuMeta',
        'title'         => 'string',
        'slug'          => 'string',
        'icon'          => 'string',
        'sort'          => 'integer',
        'active'        => 'integer',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];

    protected $castHandlers = [
        'menuMeta' => NavigationMetaCast::class,
    ];
}
