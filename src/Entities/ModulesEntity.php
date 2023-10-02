<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class ModulesEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'parent'        => 'integer',
        'is_core'       => 'integer',
        'is_plugin'     => 'integer',
        'is_system'     => 'integer',
        'slug'          => 'string',
        'name'          => 'string',
        'version'       => 'string',
        'description'   => 'string',
        'extra'         => 'json-array',
        'url_pattern'   => 'string',
        'in_sitemap'    => 'integer',
        'active'        => 'integer',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'num'           => 'integer'
    ];
}
