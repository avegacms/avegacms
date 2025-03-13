<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use AvegaCms\Models\Admin\EmailTemplateModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class EmailTemplate extends AvegaCmsAdminAPI
{
    protected EmailTemplateModel $ETM;

    public function __construct()
    {
        parent::__construct();
        $this->ETM = model(EmailTemplateModel::class);
    }

    public function index(): ResponseInterface
    {
        return $this->cmsRespond($this->ETM->getTemplates()->filter($this->request->getGet() ?? [])->apiPagination());
    }

    /**
     * @throws ReflectionException
     */
    public function create(): ResponseInterface
    {
        $data = $this->apiData;

        $data['created_by_id'] = $this->userData->userId;

        if (! $id = $this->ETM->insert($data)) {
            return $this->cmsRespondFail($this->ETM->errors());
        }

        return $this->cmsRespondCreated($id);
    }

    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->ETM->forEdit((int) $id)) === null) {
            return $this->failNotFound();
        }

        return $this->cmsRespond($data->toArray());
    }

    /**
     * @param mixed|null $id
     *
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
            return $this->cmsRespondFail($this->ETM->errors());
        }

        return $this->respondNoContent();
    }

    /**
     * Delete the designated resource object from the model
     *
     * @param mixed|null $id
     */
    public function delete($id = null): ResponseInterface
    {
        if (($data = $this->ETM->forEdit((int) $id)) === null) {
            return $this->failNotFound();
        }

        if ($data->is_system === 1) {
            return $this->cmsRespondFail(lang('EmailTemplate.errors.deleteSystem'));
        }

        if (! $this->ETM->delete($id)) {
            return $this->cmsRespondFail(lang('Api.errors.delete', ['Locales']));
        }

        return $this->respondNoContent();
    }
}
