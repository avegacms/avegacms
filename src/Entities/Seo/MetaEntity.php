<?php

namespace AvegaCms\Entities\Seo;

use CodeIgniter\Entity\Entity;

/**
 * @property string $title
 * @property string $keywords
 * @property string $description
 * @property string $lang
 * @property boolean $useMultiLocales
 * @property string $canonical
 * @property string $robots
 * @property array $alternate
 * @property OpenGraphEntity $openGraph
 */
class MetaEntity extends Entity
{
    protected $casts = [
        'title'           => 'string',
        'keywords'        => 'string',
        'description'     => 'string',
        'lang'            => 'string',
        'useMultiLocales' => 'boolean',
        'canonical'       => 'string',
        'robots'          => 'string'
    ];
}
