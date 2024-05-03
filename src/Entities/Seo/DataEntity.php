<?php

namespace AvegaCms\Entities\Seo;

use CodeIgniter\Entity\Entity;

/**
 * @property integer $id
 * @property integer $parent
 * @property integer $locale_id
 * @property integer $in_sitemap
 * @property string $use_url_pattern
 * @property string $title
 * @property string $slug
 * @property string $url
 * @property array $meta
 * @property array $extra_data
 * @property string $meta_type
 * @property string $publish_at
 */
class DataEntity extends Entity
{
    protected $casts = [
        'id'              => 'integer',
        'parent'          => 'integer',
        'locale_id'       => 'integer',
        'in_sitemap'      => 'integer',
        'use_url_pattern' => 'string',
        'title'           => 'string',
        'slug'            => 'string',
        'url'             => 'string',
        'meta'            => 'json-array',
        'extra_data'      => 'json-array',
        'meta_type'       => 'string',
        'publish_at'      => 'datetime'
    ];
}