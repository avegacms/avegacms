<?php

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use AvegaCms\Models\Admin\LocalesModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;


class Locales extends AvegaCmsAdminAPI
{
    protected LocalesModel $LM;

    public function __construct()
    {
        parent::__construct();
        $this->LM = model(LocalesModel::class);
    }

    /**
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $locales = $this->LM->filter($this->request->getGet() ?? [])->pagination();

        return $this->cmsRespond($locales['list'], $locales['pagination']);
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->LM->forEdit($id)) === null) {
            return $this->failNotFound(lang('Api.errors.noData'));
        }

        return $this->cmsRespond($data->toArray());
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

        $data['created_by_id'] = $this->userData->userId;

        if ( ! $id = $this->LM->insert($data)) {
            return $this->failValidationErrors($this->LM->errors());
        }

        return $this->cmsRespondCreated($id);
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

        if ($this->LM->find($id) === null) {
            return $this->failNotFound();
        }

        $data['updated_by_id'] = $this->userData->userId;

        if ($this->LM->save($data) === false) {
            return $this->failValidationErrors($this->LM->errors());
        }

        return $this->respondNoContent();
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function delete($id): ResponseInterface
    {
        if (($data = $this->LM->forEdit($id)) === null) {
            return $this->failNotFound(lang('Api.errors.noData'));
        }

        if ($data->is_default == 1) {
            return $this->failValidationErrors(lang('Locales.errors.deleteIsDefault'));
        }

        if ( ! $this->LM->delete($id)) {
            return $this->failValidationErrors(lang('Api.errors.delete'));
        }

        return $this->respondNoContent();
    }
}