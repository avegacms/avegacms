<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Content;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use AvegaCms\Enums\MetaDataTypes;
use AvegaCms\Enums\MetaStatuses;
use AvegaCms\Utilities\SeoUtilites;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\{ContentModel, MetaDataModel};
use AvegaCms\Entities\{MetaDataEntity, ContentEntity};
use ReflectionException;

class Pages extends AvegaCmsAdminAPI
{
    protected ContentModel  $CM;
    protected MetaDataModel $MDM;

    public function __construct()
    {
        parent::__construct();
        $this->CM = model(ContentModel::class);
        $this->MDM = model(MetaDataModel::class);
    }

    /**
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $meta = $this->MDM->selectPages()
            ->filter($this->request->getGet() ?? [])
            ->pagination();

        return $this->cmsRespond($meta['list'], $meta['pagination']);
    }

    /**
     * @return ResponseInterface
     */
    public function new(): ResponseInterface
    {
        return $this->cmsRespond(
            [
                'statuses'  => MetaStatuses::getValues(),
                'defStatus' => MetaStatuses::Draft->value,
                'locales'   => array_column(SeoUtilites::Locales(), 'locale_name', 'id')
            ]
        );
    }

    /**
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function create(): ResponseInterface
    {
        if (empty($data = $this->request->getJSON(true))) {
            return $this->failValidationErrors(lang('Api.errors.noData'));
        }

        $data['module_id'] = $data['item_id'] = 0;
        $data['creator_id'] = $data['created_by_id'] = $this->userData->userId;

        $content = [
            'anons'   => $data['anons'],
            'content' => $data['content'],
            'extra'   => $data['extra']
        ];

        unset($data['anons'], $data['content'], $data['extra']);

        if ( ! $id = $this->MDM->insert((new MetaDataEntity($data)))) {
            return $this->failValidationErrors($this->MDM->errors());
        }

        $content['meta_id'] = $id;

        if ($this->CM->insert((new ContentEntity($content))) === false) {
            return $this->failValidationErrors($this->CM->errors());
        }

        return $this->cmsRespondCreated($id);
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->MDM->pageEdit((int) $id)) === null) {
            return $this->failNotFound();
        }

        return $this->cmsRespond($data->toArray());
    }

    /**
     * @param $id
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function update($id = null): ResponseInterface
    {
        if (empty($data = $this->request->getJSON(true))) {
            return $this->failValidationErrors(lang('Api.errors.noData'));
        }

        if ($this->MDM->pageEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        $data['module_id'] = $data['item_id'] = 0;
        $data['updated_by_id'] = $this->userData->userId;

        $content = [
            'anons'   => $data['anons'],
            'content' => $data['content'],
            'extra'   => $data['extra']
        ];

        unset($data['creator_id'], $data['anons'], $data['content'], $data['extra']);

        if ($this->MDM->save((new MetaDataEntity($data))) === false) {
            return $this->failValidationErrors($this->MDM->errors());
        }

        if ($this->CM->where(['meta_id' => $id])->update(null, (new ContentEntity($content))) === false) {
            return $this->failValidationErrors($this->CM->errors());
        }

        return $this->respondNoContent();
    }

    /**
     * @param $id
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function patch($id = null): ResponseInterface
    {
        if (empty($data = $this->request->getJSON(true))) {
            return $this->failValidationErrors(lang('Api.errors.noData'));
        }

        if ($this->MDM->pageEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        $data['updated_by_id'] = $this->userData->userId;

        if ($this->MDM->save((new MetaDataEntity($data))) === false) {
            return $this->failValidationErrors($this->MDM->errors());
        }

        return $this->respondNoContent();
    }

    /**
     * @param $id
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function delete($id = null): ResponseInterface
    {
        if (($data = $this->MDM->pageEdit((int) $id)) === null) {
            return $this->failNotFound();
        }

        if ($data->meta_type === MetaDataTypes::Main->value) {
            return $this->failValidationErrors(lang('Content.errors.deleteIsDefault'));
        }

        if ($this->MDM->delete($id) === false) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Metadata']));
        }

        if ($this->CM->where(['meta_id' => $id])->delete() === false) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Content']));
        }

        if ($this->MDM->where(['parent' => $id])->update(null,
                ['parent' => $data->parent, 'status' => MetaStatuses::Draft->value]) === false) {
            return $this->failValidationErrors(lang('Api.errors.update', ['Metadata']));
        }

        return $this->respondNoContent();
    }
}
