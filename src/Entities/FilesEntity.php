<?php

namespace AvegaCms\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int|null $id
 * @property int|null $userId
 * @property string|null $name
 * @property string|null $alternativeText
 * @property string|null $caption
 * @property int|null $width
 * @property int|null $height
 * @property string|null $formats
 * @property string|null $hash
 * @property string|null $ext
 * @property float|null $size
 * @property string|null $url
 * @property string|null $previewUrl
 * @property string|null $provider
 * @property string|null $providerMetaData
 * @property string|null $folderPath
 * @property int|null $isPersonal
 * @property string|null $fileType
 */
class FilesEntity extends Entity
{
    protected $datamap = [
        'userId'           => 'user_id',
        'alternativeText'  => 'alternative_text',
        'previewUrl'       => 'preview_url',
        'providerMetaData' => 'provider_metadata',
        'folderPath'       => 'folder_path',
        'isPersonal'       => 'is_personal',
        'fileType'         => 'file_type'
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
