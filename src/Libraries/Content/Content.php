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

    public function __construct(int $moduleId = 0, ?string $type = null)
    {
        $this->moduleId = $moduleId;
        $this->type     = $type;
        $this->MDM      = new MetaDataModel();
        $this->CM       = new ContentModel();
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
            'extra'   => $data['extra'] ?? ''
        ];

        unset($data['anons'], $data['content'], $data['extra']);

        $data['use_url_pattern'] = boolval($data['use_url_pattern'] ?? 0);

        $data['meta_type'] = match (ucfirst($this->type)) {
            MetaDataTypes::Main->name,
            MetaDataTypes::Page->name,
            MetaDataTypes::Page404->name,
            MetaDataTypes::Rubric->name,
            MetaDataTypes::Post->name   => $this->type,
            MetaDataTypes::Module->name => ($this->moduleId > 0) ? MetaDataTypes::Module->name : throw ContentExceptions::forNoModuleId(),
            default                     => throw ContentExceptions::forUnknownType()
        };

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
    public function getMetaData(int $id): array|null
    {
        if (is_null($content = $this->MDM->getMetaData($id, $this->moduleId))) {
            return null;
        }

        $content['content'] = $this->CM->find($id);

        return $content;
    }
}