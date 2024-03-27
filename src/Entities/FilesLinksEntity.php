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

        'provider' => 'integer'
    ];

    /**
     * @return array
     */
    public function getData(): array
    {
        $data = json_decode($this->attributes['data'], true);

        switch ($data['type']) {
            case FileTypes::Directory->value:
                break;
            case FileTypes::File->value:
                $data['path']     = base_url($data['path']);
                $data['sizeText'] = $this->_getTextFileSize($data['size']);
                break;
            case FileTypes::Image->value:
                $data['sizeText']         = $this->_getTextFileSize($data['size']);
                $data['path']['original'] = base_url($data['path']['original']);
                if ( ! empty($data['path']['webp'])) {
                    $data['path']['webp'] = base_url($data['path']['webp']);
                }

                $data['thumb']['original'] = base_url($data['thumb']['original']);
                if ( ! empty($data['thumb']['webp'])) {
                    $data['thumb']['webp'] = base_url($data['thumb']['webp']);
                }

                if ( ! empty($data['variants'] ?? '')) {
                    foreach ($data['variants'] as $k => $variants) {
                        foreach ($variants as $pointer => $variant) {
                            $data['variants'][$k][$pointer] = base_url($variant);
                        }
                    }
                }

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
