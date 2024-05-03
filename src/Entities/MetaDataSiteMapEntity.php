<?php

declare(strict_types = 1);

namespace AvegaCms\Entities;

class MetaDataSiteMapEntity extends AvegaCmsEntity
{
    protected $datamap = [
        'localeId'      => 'locale_id',
        'moduleId'      => 'module_id',
        'useUrlPattern' => 'use_url_pattern',
        'metaSitemap'   => 'meta_sitemap',
        'publishAt'     => 'publish_at'
    ];
    protected $dates   = ['publish_at'];
    protected $casts   = [
        'id'              => 'integer',
        'parent'          => 'integer',
        'locale_id'       => 'integer',
        'module_id'       => 'integer',
        'url'             => 'string',
        'use_url_pattern' => 'string',
        'meta_sitemap'    => 'json-array',
        'publish_at'      => 'datetime'
    ];
}
