<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;
use AvegaCms\Entities\Cast\ContentSeoMetaCast;

class MetaDataEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'publish_at'];
    protected $casts   = [
        'id'            => 'integer',
        'post_id'       => 'integer',
        'rubric_id'     => 'integer',
        'parent'        => 'integer',
        'locale_id'     => 'integer',
        'module_id'     => 'integer',
        'slug'          => 'string',
        'creator_id'    => 'integer',
        'item_id'       => 'integer',
        'title'         => 'string',
        'sort'          => 'integer',
        'url'           => 'string',
        'meta'          => 'json-array',
        'extra_data'    => 'json-array',
        'status'        => 'string',
        'meta_type'     => 'string',
        'in_sitemap'    => 'integer',
        'rubrics'       => 'array',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'publish_at'    => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * @return array
     */
    public function getRubrics(): array
    {
        return array_map(function ($k) {
            return intval($k);
        }, unserialize($this->attributes['rubrics']));
    }

    protected $castHandlers = [
        'seoMeta' => ContentSeoMetaCast::class,
    ];
}
