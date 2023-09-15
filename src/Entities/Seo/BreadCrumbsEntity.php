<?php

namespace AvegaCms\Entities\Seo;

use CodeIgniter\Entity\Entity;

/**
 * @property string $url
 * @property string $title
 * @property boolean $active
 */
class BreadCrumbsEntity extends Entity
{
    protected $casts = [
        'url'    => 'string',
        'title'  => 'string',
        'active' => 'boolean'
    ];
}
