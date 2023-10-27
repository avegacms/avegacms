<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int|null $id
 * @property string|null $title
 * @property string|null $anons
 * @property string|null $content
 * @property array|null $extra
 */
class ContentEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = [];
    protected $casts   = [
        'id'      => 'integer',
        'title'   => 'string',
        'anons'   => 'string',
        'content' => 'string',
        'extra'   => 'json-array'
    ];
}
