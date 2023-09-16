<?php

namespace AvegaCms\Entities\Seo;

use CodeIgniter\Entity\Entity;

/**
 * @property string $locale
 * @property string $site_name
 * @property string $title
 * @property string $type
 * @property string $url
 * @property string $image
 */
class OpenGraphEntity extends Entity
{
    protected $casts = [
        'locale'    => 'string',
        'site_name' => 'string',
        'title'     => 'string',
        'type'      => 'string',
        'url'       => 'string',
        'image'     => 'string'
    ];
}
