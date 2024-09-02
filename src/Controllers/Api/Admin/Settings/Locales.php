<?php

declare(strict_types=1);

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

    public function index(): ResponseInterface
    {
        return $this->cmsRespond($this->LM->filter($this->request->getGet() ?? [])->apiPagination());
    }

    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->LM->forEdit($id)) === null) {
            return $this->failNotFound();
        }

        return $this->cmsRespond($data->toArray());
    }

    /**
     * @throws ReflectionException
     */
    public function create(): ResponseInterface
    {
        $data = $this->apiData;

        $data['created_by_id'] = $this->userData->userId;

        if (! $id = $this->LM->insert($data)) {
            return $this->failValidationErrors($this->LM->errors());
        }

        return $this->cmsRespondCreated($id);
    }

    /**
     * @param mixed|null $id
     *
     * @throws ReflectionException
     */
    public function update($id = null): ResponseInterface
    {
        $data = $this->apiData;

        if ($this->LM->find($id) === null) {
            return $this->failNotFound();
        }

        $data['updated_by_id'] = $this->userData->userId;

        if ($this->LM->save($data) === false) {
            return $this->failValidationErrors($this->LM->errors());
        }

        return $this->respondNoContent();
    }

    public function delete($id): ResponseInterface
    {
        if (($data = $this->LM->forEdit($id)) === null) {
            return $this->failNotFound();
        }

        if ($data->is_default === 1) {
            return $this->failValidationErrors(lang('Locales.errors.deleteIsDefault'));
        }

        if (! $this->LM->delete($id)) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Locales']));
        }

        return $this->respondNoContent();
    }
}
