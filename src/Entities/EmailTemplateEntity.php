<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class EmailTemplateEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'label'         => 'string',
        'slug'          => 'string',
        'locale_id'     => 'integer',
        'is_system'     => 'integer',
        'subject'       => 'string',
        'content'       => 'string',
        'variables'     => 'string',
        'template'      => 'string',
        'active'        => 'integer',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];

}
