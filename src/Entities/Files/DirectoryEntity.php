<?php

namespace AvegaCms\Entities\Files;

use CodeIgniter\Entity\Entity;

/**
 * @property string $url
 */
class DirectoryEntity extends Entity
{
    protected $casts = [
        'url' => 'string'
    ];
}
