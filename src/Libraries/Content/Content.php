<?php

declare(strict_types = 1);

namespace AvegaCms\Libraries\Content;

use AvegaCms\Models\Admin\{MetaDataModel, ContentModel};

class Content
{
    protected int           $moduleId = 0;
    protected MetaDataModel $MDM;
    protected ContentModel  $CM;


    public function __construct(int $moduleId = 0)
    {
        $this->moduleId = $moduleId;
        $this->MDM      = new MetaDataModel();
        $this->CM       = new ContentModel();
    }

    /**
     * @param  int  $id
     * @return object|null
     */
    public function getMetaData(int $id): array|null
    {
        if (is_null($content['metadata'] = $this->MDM->getMetaData($id))) {
            return null;
        }

        $content['content'] = $this->CM->find($id);

        return $content;
    }
}