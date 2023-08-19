<?php

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\RolesModel;
use ReflectionException;

class Roles extends AvegaCmsAdminAPI
{
    protected RolesModel $RM;

    public function __construct()
    {
        parent::__construct();
        $this->RM = model(RolesModel::class);
    }

    /**
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $locales = $this->RM->filter($this->request->getGet() ?? [])->pagination();

        return $this->cmsRespond($locales['list'], $locales['pagination']);
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

        if ( ! $id = $this->RM->insert($data)) {
            return $this->failValidationErrors($this->RM->errors());
        }

        return $this->cmsRespondCreated($id);
    }

    /**
     * Return the editable properties of a resource object
     *
     * @return ResponseInterface
     */
    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->RM->find($id)) === null) {
            return $this->failNotFound(lang('Api.errors.noData'));
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

        if ($this->RM->find($id) === null) {
            return $this->failNotFound();
        }

        $data['updated_by_id'] = $this->userData->userId;

        if ($this->RM->save($data) === false) {
            return $this->failValidationErrors($this->RM->errors());
        }

        return $this->respondNoContent();
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function delete($id = null): ResponseInterface
    {
        if (($data = $this->RM->find($id)) === null) {
            return $this->failNotFound(lang('Api.errors.noData'));
        }

        if (in_array($data->role, ['root', 'default'])) {
            return $this->failValidationErrors(lang('Roles.errors.deleteIsDefault'));
        }

        if ( ! $this->RM->delete($id)) {
            return $this->failValidationErrors(lang('Api.errors.delete'));
        }

        return $this->respondNoContent();
    }
}
