<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;
use AvegaCms\Entities\Cast\NavigationMetaCast;

/**
 * @property int|null $id
 * @property int|null $parent
 * @property int|null $isAdmin
 * @property int|null $objectId
 * @property int|null $localeId
 * @property int|null $navType
 * @property mixed|null $meta
 * @property string|null $title
 * @property string|null $slug
 * @property string|null $icon
 * @property int|null $sort
 * @property int|null $active
 * @property int|null $createdById
 * @property int|null $updatedById
 */
class NavigationsEntity extends Entity
{
    protected $datamap = [
        'isAdmin'     => 'is_admin',
        'objectId'    => 'object_id',
        'localeId'    => 'locale_id',
        'navType'     => 'nav_type',
        'createdById' => 'created_by_id',
        'updatedById' => 'updated_by_id',
    ];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id'            => 'integer',
        'parent'        => 'integer',
        'is_admin'      => 'integer',
        'object_id'     => 'integer',
        'locale_id'     => 'integer',
        'nav_type'      => 'string',
        'meta'          => 'menuMeta',
        'title'         => 'string',
        'slug'          => 'string',
        'icon'          => 'string',
        'sort'          => 'integer',
        'active'        => 'int-bool',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];

    protected $castHandlers = [
        'menuMeta' => NavigationMetaCast::class,
    ];
}
