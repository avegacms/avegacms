<?php

namespace AvegaCms\Entities;

use AvegaCms\Enums\FileTypes;

/**
 * @property int $id
 * @property int $userId
 * @property int $parent
 * @property int $moduleId
 * @property int $entityId
 * @property int $itemId
 * @property string $uid
 * @property array|null $type
 * @property boolean $active
 * @property int $createdById
 * @property int $updatedById
 */
class FilesLinksEntity extends AvegaCmsEntity
{
    protected $datamap = [
        'userId'      => 'user_id',
        'moduleId'    => 'module_id',
        'entityId'    => 'entity_id',
        'itemId'      => 'item_id',
        'createdById' => 'created_by_id',
        'updatedById' => 'updated_by_id',
    ];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'            => 'integer',
        'user_id'       => 'integer',
        'parent'        => 'integer',
        'module_id'     => 'integer',
        'entity_id'     => 'integer',
        'item_id'       => 'integer',
        'uid'           => 'string',
        'type'          => 'string',
        'active'        => 'int-bool',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',

        'provider' => 'integer',
    ];

    /**
     * @return array
     */
    public function getData(): array
    {
        $data             = json_decode($this->attributes['data'], true);
        $data['path']     = base_url($data['path']);
        $data['sizeText'] = $this->_getTextFileSize($data['size']);

        unset($data['provider']);

        switch ($data['type']) {
            case FileTypes::Directory->value:
                break;
            case FileTypes::File->value:
                break;
            case FileTypes::Image->value:
                $data['thumb'] = base_url($data['thumb']);
                break;
        }

        return $data;
    }

    /**
     * @param  int  $size
     * @return string
     */
    private function _getTextFileSize(int $size): string
    {
        if (($size = ($size / 1024)) < 1024) {
            return round($size, 1) . ' ' . lang('Uploader.sizes.kb');
        }
        if (($size = (($size / 1024) / 1024)) < 1024) {
            return round($size, 1) . ' ' . lang('Uploader.sizes.mb');
        }
        return round($size, 1) . ' ' . lang('Uploader.sizes.gb');
    }
}
