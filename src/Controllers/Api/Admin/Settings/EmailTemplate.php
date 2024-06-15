<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\EmailTemplateModel;
use ReflectionException;

class EmailTemplate extends AvegaCmsAdminAPI
{
    protected EmailTemplateModel $ETM;

    public function __construct()
    {
        parent::__construct();
        $this->ETM = model(EmailTemplateModel::class);
    }

    /**
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        return $this->cmsRespond($this->ETM->getTemplates()->filter($this->request->getGet() ?? [])->apiPagination());
    }

    /**
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function create(): ResponseInterface
    {
        $data = $this->apiData;

        $data['created_by_id'] = $this->userData->userId;

        if ( ! $id = $this->ETM->insert($data)) {
            return $this->failValidationErrors($this->ETM->errors());
        }

        return $this->cmsRespondCreated($id);
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->ETM->forEdit((int) $id)) === null) {
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

        if ($this->ETM->forEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        $data['updated_by_id'] = $this->userData->userId;

        if ($this->ETM->save($data) === false) {
            return $this->failValidationErrors($this->ETM->errors());
        }

        return $this->respondNoContent();
    }

    /**
     * Delete the designated resource object from the model
     *
     * @param $id
     * @return ResponseInterface
     */
    public function delete($id = null): ResponseInterface
    {
        if (($data = $this->ETM->forEdit((int) $id)) === null) {
            return $this->failNotFound();
        }

        if ($data->is_system === 1) {
            return $this->failValidationErrors(lang('EmailTemplate.errors.deleteSystem'));
        }

        if ( ! $this->ETM->delete($id)) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Locales']));
        }

        return $this->respondNoContent();
    }
}
