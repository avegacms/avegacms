<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class ModulesEntity extends Entity
{
    protected $datamap = [
        'id'            => null,
        'parent'        => null,
        'is_plugin'     => null,
        'is_system'     => null,
        'slug'          => null,
        'name'          => null,
        'version'       => null,
        'description'   => null,
        'extra'         => null,
        'in_sitemap'    => null,
        'active'        => null,
        'created_by_id' => null,
        'updated_by_id' => null,
        'created_at'    => null,
        'updated_at'    => null
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'parent'        => 'integer',
        'is_plugin'     => 'integer',
        'is_system'     => 'integer',
        'slug'          => 'string',
        'name'          => 'string',
        'version'       => 'string',
        'description'   => 'string',
        'extra'         => 'json-array',
        'in_sitemap'    => 'integer',
        'active'        => 'integer',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',

        'num' => 'integer'
    ];
}
