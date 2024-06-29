<?php

declare(strict_types = 1);

namespace AvegaCms\Libraries\Content;

use AvegaCms\Libraries\Content\Exceptions\ContentExceptions;
use AvegaCms\Models\Admin\{MetaDataModel, ContentModel};
use AvegaCms\Enums\MetaDataTypes;
use ReflectionException;

class Content
{
    protected int           $moduleId = 0;
    protected string|null   $type     = null;
    protected MetaDataModel $MDM;
    protected ContentModel  $CM;

    public function __construct(string $type, int $moduleId = 0)
    {
        $this->moduleId = $moduleId;
        $this->type     = $type;
        $this->MDM      = new MetaDataModel();
        $this->CM       = new ContentModel();
    }

    /**
     * @param  array  $filter
     * @param  bool  $all
     * @return array
     */
    public function getMetaDataList(array $filter, bool $all = false): array
    {
        $list = $this->MDM->getMetaDataList($filter);

        return ($all) ? $list->findAll() : $list->apiPagination();
    }

    /**
     * @param  array  $data
     * @return int
     * @throws ContentExceptions|ReflectionException
     */
    public function createMetaData(array $data): int
    {
        if (empty($data)) {
            throw ContentExceptions::forNoData();
        }

        $content = [
            'anons'   => $data['anons'] ?? '',
            'content' => $data['content'] ?? '',
            'extra'   => $data['extra'] ?? null
        ];

        unset($data['anons'], $data['content'], $data['extra']);

        $data['use_url_pattern'] = boolval($data['use_url_pattern'] ?? 0);
        $data['meta_type']       = match (ucfirst($this->type)) {
            MetaDataTypes::Main->name,
            MetaDataTypes::Page->name,
            MetaDataTypes::Page404->name => ucfirst($this->type),
            MetaDataTypes::Module->name  => ($this->moduleId > 0) ? MetaDataTypes::Module->name : throw ContentExceptions::forNoModuleId(),
            default                      => throw ContentExceptions::forUnknownType()
        };

        $data['item_id']   ??= 0;
        $data['module_id'] = $this->moduleId;

        if ( ! ($content['id'] = $this->MDM->insert($data))) {
            throw new ContentExceptions($this->MDM->errors());
        }

        if ($this->CM->insert($content) === false) {
            throw new ContentExceptions($this->CM->errors());
        }

        return $content['id'];
    }

    /**
     * @param  int  $id
     * @return object|null
     */
    public function getMetaData(int $id): object|null
    {
        return $this->MDM->getMetaData($id, $this->moduleId);
    }

    /**
     * @param  int  $id
     * @param  array  $data
     * @return bool
     * @throws ContentExceptions|ReflectionException
     */
    public function updateMetaData(int $id, array $data): bool
    {
        if ($id < 0 || empty($data)) {
            throw ContentExceptions::forNoData();
        }

        $data['id']              = $id;
        $data['use_url_pattern'] = boolval($data['use_url_pattern'] ?? 0);

        $content = [
            'anons'   => $data['anons'] ?? '',
            'content' => $data['content'] ?? '',
            'extra'   => $data['extra'] ?? null
        ];

        unset($data['anons'], $data['content'], $data['extra']);

        if ($this->MDM->update($id, $data) === false) {
            throw new ContentExceptions($this->MDM->errors());
        }

        if ($this->CM->update($id, $content) === false) {
            throw new ContentExceptions($this->CM->errors());
        }

        return true;
    }

    /**
     * @throws ReflectionException
     */
    public function setMetaDataPreview(int $id, int $previewId): bool
    {
        if ($id <= 0 || $previewId <= 0) {
            return false;
        }
        return $this->MDM->save(['id' => $id, 'preview_id' => $previewId]);
    }

    /**
     * @param  int  $id
     * @param  array  $data
     * @return bool
     * @throws ContentExceptions|ReflectionException
     */
    public function patchMetaData(int $id, array $data): bool
    {
        if ($id < 0 || empty($data)) {
            throw ContentExceptions::forNoData();
        }

        if (key($data) != 'status') {
            return throw ContentExceptions::unknownPatchMethod();
        }
        if ($this->MDM->update($id, $data) === false) {
            throw new ContentExceptions($this->MDM->errors());
        }

        return true;
    }

    /**
     * @param  int  $id
     * @return bool
     * @throws ContentExceptions
     */
    public function deleteMetaData(int $id): bool
    {
        if ($id < 0 || $this->MDM->delete($id) === false) {
            throw new ContentExceptions($this->MDM->errors());
        }

        return true;
    }
}