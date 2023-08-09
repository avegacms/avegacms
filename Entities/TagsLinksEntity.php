<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class TagsLinksEntity extends Entity
{
    protected $datamap = [
        'tag_id'        => null,
        'seo_id'        => null,
        'created_by_id' => null,
        'created_at'    => null
    ];
    protected $dates   = ['created_at'];
    protected $casts   = [
        'tag_id'        => 'integer',
        'seo_id'        => 'integer',
        'created_by_id' => 'integer',
        'created_at'    => 'datetime'
    ];
}
