<?php

namespace AvegaCms\Entities\Files;

use CodeIgniter\Entity\Entity;

/**
 * @property string $url
 * @property string|null $title
 */
class DirectoryEntity extends Entity
{
    protected $casts = [
        'url'   => 'string',
        'title' => 'string'
    ];
}
