<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class PostCategoriesEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = [];
    protected $casts   = [
        'post_id'       => 'integer',
        'category_id'   => 'integer',
        'created_by_id' => 'integer'
    ];
}
