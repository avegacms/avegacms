<?php

declare(strict_types = 1);

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int|null $id
 * @property int|null $parent
 * @property string|null $slug
 * @property string|null $locale
 * @property string|null $localeName
 * @property string|null $home
 * @property string|null $extra
 * @property int|null $isDefault
 * @property int|null $active
 */
class LocalesEntity extends Entity
{
    protected $datamap = [
        'localeName' => 'locale_name',
        'isDefault'  => 'is_default'
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'parent'        => 'integer',
        'slug'          => 'string',
        'locale'        => 'string',
        'locale_name'   => 'string',
        'home'          => 'string',
        'extra'         => 'json-array',
        'is_default'    => 'integer',
        'active'        => 'int-bool',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime'
    ];

    /**
     * @return string
     */
    public function getExtra(): string
    {
        $extra = json_decode($this->attributes['extra'], true);

        $extra['og:image'] = base_url('uploads/locales/' . $extra['og:image']);

        return json_encode($extra);
    }
}
