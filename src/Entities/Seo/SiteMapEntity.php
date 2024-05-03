<?php

namespace AvegaCms\Entities\Seo;

use CodeIgniter\Entity\Entity;
use AvegaCms\Enums\MetaChangefreq;

/**
 * @property string $priority
 * @property string|MetaChangefreq $changefreq
 */
class SiteMapEntity extends Entity
{
    protected $casts = [
        'priority'   => 'float',
        'changefreq' => 'string'
    ];
}