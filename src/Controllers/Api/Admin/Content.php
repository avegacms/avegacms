<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers\Api\Admin;

use AvegaCms\Enums\{MetaDataTypes, MetaStatuses, SitemapChangefreqs};
use AvegaCms\Libraries\Content\Exceptions\ContentExceptions;
use AvegaCms\Models\Admin\MetaDataModel;
use AvegaCms\Libraries\Content\Content as ContentLib;
use AvegaCms\Utilities\CmsModule;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class Content extends AvegaCmsAdminAPI
{
    protected MetaDataModel $MDM;
    protected int           $moduleId;

    public function __construct()
    {
        parent::__construct();

        $this->MDM      = new MetaDataModel();
        $this->moduleId = CmsModule::meta('content')['id'];
    }

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        // TODO добавить данные для фильтра
        return $this->cmsRespond(
            $this->MDM->selectPages($this->request->getGet() ?? [])
        );
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return ResponseInterface
     */
    public function new(): ResponseInterface
    {
        //
    }

    /**
     * @return ResponseInterface
     */
    public function create(): ResponseInterface
    {
        try {
            $id = (new ContentLib(MetaDataTypes::Page->name, $this->moduleId))->createMetaData($this->apiData);
            return $this->cmsRespondCreated($id);
        } catch (ReflectionException|ContentExceptions $e) {
            return $this->cmsRespondFail($e->getMessages() ?? $e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param  int|null  $id
     * @return ResponseInterface
     */
    public function edit(?int $id = null): ResponseInterface
    {
        if (($data = $this->MDM->editPageMetaData($id)) === null) {
            return $this->failNotFound();
        }
        return $this->cmsRespond(
            (array) $data,
            [
                'parent_pages' => $this->MDM->getParentPages(),
                'statuses'     => MetaStatuses::list(),
                'changefreq'   => SitemapChangefreqs::list()
            ]
        );
    }

    /**
     * @param  int|null  $id
     * @return ResponseInterface
     */
    public function update(?int $id = null): ResponseInterface
    {
        try {
            if ($this->MDM->editPageMetaData($id) === null) {
                return $this->failNotFound();
            }

            if ( ! in_array($this->apiData['meta_type'], [
                MetaDataTypes::Main->name,
                MetaDataTypes::Page->name,
                MetaDataTypes::Page404->name
            ])) {
                throw ContentExceptions::forUnknownType();
            }

            (new ContentLib($this->apiData['meta_type'], $this->moduleId))->updateMetaData($id, $this->apiData);
            return $this->respondNoContent();
        } catch (ReflectionException|ContentExceptions $e) {
            return $this->cmsRespondFail($e->getMessages() ?? $e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param  int|null  $id
     * @return ResponseInterface
     */
    public function delete(?int $id = null): ResponseInterface
    {
        try {
            if ($this->MDM->editPageMetaData($id) === null) {
                return $this->failNotFound();
            }

            (new ContentLib(MetaDataTypes::Page->name))->deleteMetaData($id);

            return $this->respondDeleted();
        } catch (ContentExceptions $e) {
            return $this->cmsRespondFail($e->getMessages() ?? $e->getMessage(), $e->getCode());
        }
    }
}
