<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin;

use AvegaCms\Enums\MetaDataTypes;
use AvegaCms\Enums\MetaStatuses;
use AvegaCms\Enums\SitemapChangefreqs;
use AvegaCms\Exceptions\AvegaCmsException;
use AvegaCms\Libraries\Content\Content as ContentLib;
use AvegaCms\Libraries\Content\Exceptions\ContentExceptions;
use AvegaCms\Models\Admin\MetaDataModel;
use AvegaCms\Utilities\CmsFileManager;
use AvegaCms\Utilities\CmsModule;
use AvegaCms\Utilities\Exceptions\UploaderException;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class Pages extends AvegaCmsAdminAPI
{
    protected MetaDataModel $MDM;
    protected int $moduleId;

    public function __construct()
    {
        parent::__construct();

        $this->MDM      = new MetaDataModel();
        $this->moduleId = CmsModule::meta('pages')['id'];
    }

    /**
     * Return an array of resource objects, themselves in array format
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
     */
    public function new(): ResponseInterface
    {
    }

    public function create(): ResponseInterface
    {
        try {
            $id = (new ContentLib(MetaDataTypes::Page->name, $this->moduleId))->createMetaData($this->apiData);

            return $this->cmsRespondCreated($id);
        } catch (ContentExceptions|ReflectionException $e) {
            return $this->cmsRespondFail($e->getMessages() ?? $e->getMessage(), $e->getCode());
        }
    }

    public function edit(?int $id = null): ResponseInterface
    {
        if (($data = $this->MDM->editPageMetaData($id)) === null) {
            return $this->failNotFound();
        }

        unset($data->meta_type);

        return $this->cmsRespond(
            (array) $data,
            [
                'parent_pages' => $this->MDM->getParentPages(),
                'statuses'     => MetaStatuses::list(),
                'changefreq'   => SitemapChangefreqs::list(),
            ]
        );
    }

    public function update(?int $id = null): ResponseInterface
    {
        try {
            if (($data = $this->MDM->editPageMetaData($id)) === null) {
                return $this->failNotFound();
            }

            if (! in_array($data->meta_type, [
                MetaDataTypes::Main->name,
                MetaDataTypes::Page->name,
                MetaDataTypes::Page404->name,
            ], true)) {
                throw ContentExceptions::forUnknownType();
            }

            (new ContentLib($data->meta_type, $this->moduleId))->updateMetaData($id, $this->apiData);
            unset($data);

            return $this->respondNoContent();
        } catch (ContentExceptions|ReflectionException $e) {
            return $this->cmsRespondFail($e->getMessages() ?? $e->getMessage(), $e->getCode());
        }
    }

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

    public function upload(): ResponseInterface
    {
        try {
            $file = CmsFileManager::upload(
                [
                    'entity_id' => 0,
                    'item_id'   => 0,
                    'user_id'   => $this->userData->userId,
                ],
                [
                    'field'      => 'file',
                    'directory'  => 'modules/stockmarket/bid',
                    'maxSize'    => 8192,
                    'extInFiles' => [
                        0 => 'png',
                        1 => 'jpg',
                        2 => 'jpeg',
                        3 => 'gif',
                        4 => 'pdf',
                        5 => 'txt',
                        6 => 'doc',
                        7 => 'docx',
                        8 => 'xlsx',
                        9 => 'xls',
                    ],
                ],
            )[0] ?? null;

            return $this->respond(['file' => $file]);
        } catch (UploaderException|AvegaCmsException $e) {
            log_message(
                'error',
                json_encode(
                    method_exists($e, 'getMessages')
                        ? $e->getMessages() : $e->getMessage(),
                    JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR
                )
            );

            return $this->cmsException($e);
        }
    }
}
