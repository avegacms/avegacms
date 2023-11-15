<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int|null $id
 * @property int|null $parent
 * @property int|null $metaId
 * @property int|null $isCore
 * @property int|null $isPlugin
 * @property int|null $isSystem
 * @property string|null $key
 * @property string|null $slug
 * @property string|null $name
 * @property string|null $version
 * @property string|null $description
 * @property string|null $extra
 * @property string|null $urlPattern
 * @property int|null $inSitemap
 * @property int|null $active
 * @property int|null $createdById
 * @property int|null $updatedById
 */
class ModulesEntity extends Entity
{
    protected $datamap = [
        'metaId'      => 'meta_id',
        'isCore'      => 'is_core',
        'isPlugin'    => 'is_plugin',
        'isSystem'    => 'is_system',
        'urlPattern'  => 'url_pattern',
        'inSitemap'   => 'in_sitemap',
        'createdById' => 'created_by_id',
        'updatedById' => 'updated_by_id'
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'meta_id'       => '?integer',
        'parent'        => 'integer',
        'is_core'       => 'integer',
        'is_plugin'     => 'integer',
        'is_system'     => 'integer',
        'key'           => 'string',
        'slug'          => 'string',
        'name'          => 'string',
        'version'       => 'string',
        'description'   => 'string',
        'extra'         => 'json-array',
        'url_pattern'   => 'string',
        'in_sitemap'    => 'integer',
        'active'        => 'integer',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'num'           => 'integer'
    ];
}
