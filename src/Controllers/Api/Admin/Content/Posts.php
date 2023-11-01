<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Content;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use AvegaCms\Enums\MetaDataTypes;
use AvegaCms\Enums\MetaStatuses;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\{ContentModel, MetaDataModel};
use AvegaCms\Entities\{MetaDataEntity, ContentEntity};
use AvegaCms\Utils\SeoUtils;
use ReflectionException;

class Posts extends AvegaCmsAdminAPI
{
    protected ContentModel  $CM;
    protected MetaDataModel $MDM;

    public function __construct()
    {
        parent::__construct();
        $this->CM  = model(ContentModel::class);
        $this->MDM = model(MetaDataModel::class);
    }

    /**
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        return $this->cmsRespond($this->MDM->selectPosts()->filter($this->request->getGet() ?? [])->apiPagination());
    }

    /**
     * @return ResponseInterface
     */
    public function new(): ResponseInterface
    {
        return $this->cmsRespond(
            [
                'statuses'  => MetaStatuses::get('value'),
                'defStatus' => MetaStatuses::Draft->value,
                'locales'   => array_column(SeoUtils::Locales(), 'locale_name', 'id'),
                'rubrics'   => SeoUtils::rubricsList(key: 'id', value: 'title')
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

        $data['module_id']  = $data['parent'] = $data['item_id'] = 0;
        $data['meta_type']  = MetaDataTypes::Post->value;
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
        if (($data = $this->MDM->postEdit((int) $id)) === null) {
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

        if ($this->MDM->postEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        $data['module_id']     = $data['parent'] = $data['item_id'] = 0;
        $data['updated_by_id'] = $this->userData->userId;

        $content['anons']   = $data['anons'];
        $content['content'] = $data['content'];
        $content['extra']   = $data['extra'];

        unset($data['creator_id'], $data['anons'], $data['content'], $data['extra'], $data['rubrics']);

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

        if ($this->MDM->postEdit((int) $id) === null) {
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
     */
    public function delete($id = null): ResponseInterface
    {
        if ($this->MDM->postEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        if ($this->MDM->delete($id) === false) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Metadata']));
        }

        if ($this->CM->where(['meta_id' => $id])->delete() === false) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Content']));
        }

        return $this->respondNoContent();
    }
}
