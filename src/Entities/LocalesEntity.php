<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class LocalesEntity extends Entity
{
    protected $datamap = [];
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
        'active'        => 'integer',
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
