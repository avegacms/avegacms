<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

class FilesEntity extends Entity
{
    protected $datamap = [
        'id'                => null,
        'user_id'           => null,
        'name'              => null,
        'alternative_text'  => null,
        'caption'           => null,
        'width'             => null,
        'height'            => null,
        'formats'           => null,
        'hash'              => null,
        'ext'               => null,
        'size'              => null,
        'url'               => null,
        'preview_url'       => null,
        'provider'          => null,
        'provider_metadata' => null,
        'folder_path'       => null,
        'is_personal'       => null,
        'file_type'         => null,
        'created_by_id'     => null,
        'updated_by_id'     => null,
        'created_at'        => null,
        'updated_at'        => null
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'                => 'integer',
        'user_id'           => 'integer',
        'name'              => 'string',
        'alternative_text'  => 'string',
        'caption'           => 'string',
        'width'             => 'integer',
        'height'            => 'integer',
        'formats'           => 'json-array',
        'hash'              => 'string',
        'ext'               => 'string',
        'size'              => 'float',
        'url'               => 'string',
        'preview_url'       => 'string',
        'provider'          => 'string',
        'provider_metadata' => 'json-array',
        'folder_path'       => 'string',
        'is_personal'       => 'integer',
        'file_type'         => 'string',
        'created_by_id'     => 'integer',
        'updated_by_id'     => 'integer',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];
}
