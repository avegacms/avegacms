<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers\Api\Admin\Content;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use AvegaCms\Enums\MetaStatuses;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\{ContentModel, MetaDataModel};
use AvegaCms\Utilities\SeoUtils;
use ReflectionException;

class Rubrics extends AvegaCmsAdminAPI
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
        return $this->cmsRespond($this->MDM->selectRubrics()
            ->filter($this->request->getGet() ?? [])
            ->apiPagination()
        );
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
                'locales'   => array_column(SeoUtils::Locales(), 'locale_name', 'id')
            ]
        );
    }

    /**
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function create(): ResponseInterface
    {
        $data = $this->apiData;

        $data['parent']     = SeoUtils::mainPages($data['locale_id']);
        $data['creator_id'] = $data['created_by_id'] = $this->userData->userId;

        $content = [
            'anons'   => $data['anons'],
            'content' => $data['content'],
            'extra'   => $data['extra']
        ];

        unset($data['anons'], $data['content'], $data['extra']);

        if ( ! $id = $this->MDM->insert($data)) {
            return $this->failValidationErrors($this->MDM->errors());
        }

        $content['id'] = $id;

        if ($this->CM->insert($content) === false) {
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
        if (($data = $this->MDM->rubricEdit((int) $id)) === null) {
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
        $data = $this->apiData;

        if ($this->MDM->rubricEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        $data['parent']        = SeoUtils::mainPages($data['locale_id']);
        $data['updated_by_id'] = $this->userData->userId;

        $content['anons']   = $data['anons'];
        $content['content'] = $data['content'];
        $content['extra']   = $data['extra'];

        unset($data['creator_id'], $data['anons'], $data['content'], $data['extra']);

        if ($this->MDM->save($data) === false) {
            return $this->failValidationErrors($this->MDM->errors());
        }

        if ($this->CM->where(['id' => $id])->update(null, $content) === false) {
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
        $data = $this->apiData;

        if ($this->MDM->rubricEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        $data['updated_by_id'] = $this->userData->userId;

        if ($this->MDM->save($data) === false) {
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
        if ($this->MDM->rubricEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        if ($this->MDM->delete($id) === false) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Metadata']));
        }

        if ($this->CM->where(['id' => $id])->delete() === false) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Content']));
        }

        return $this->respondNoContent();
    }
}
