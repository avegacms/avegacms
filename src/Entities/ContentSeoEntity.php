<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;
use AvegaCms\Entities\Cast\ContentSeoMetaCast;

class ContentSeoEntity extends Entity
{
    protected $datamap = [
        'parent'        => null,
        'locale_id'     => null,
        'module_id'     => null,
        'module_slug'   => null,
        'creator_id'    => null,
        'item_id'       => null,
        'title'         => null,
        'sort'          => null,
        'url'           => null,
        'meta'          => null,
        'extra'         => null,
        'status'        => null,
        'in_sitemap'    => null,
        'created_by_id' => null,
        'updated_by_id' => null,
        'publish_at'    => null,
        'created_at'    => null,
        'updated_at'    => null
    ];
    protected $dates   = ['created_at', 'updated_at', 'publish_at'];
    protected $casts   = [
        'parent'        => 'integer',
        'locale_id'     => 'integer',
        'module_id'     => 'integer',
        'module_slug'   => 'string',
        'creator_id'    => 'integer',
        'item_id'       => 'integer',
        'title'         => 'string',
        'sort'          => 'integer',
        'url'           => 'string',
        'meta'          => 'seoMeta',
        'extra'         => 'json-array',
        'status'        => 'string',
        'in_sitemap'    => 'integer',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'publish_at'    => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];

    protected $castHandlers = [
        'seoMeta' => ContentSeoMetaCast::class,
    ];
}
