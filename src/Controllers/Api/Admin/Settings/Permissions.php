<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\{PermissionsModel, RolesModel};
use AvegaCms\Entities\PermissionsEntity;

class Permissions extends AvegaCmsAdminAPI
{
    protected PermissionsModel $PM;
    protected RolesModel       $RM;

    public function __construct()
    {
        parent::__construct();
        $this->PM = model(PermissionsModel::class);
        $this->RM = model(RolesModel::class);
    }

    /**
     * @param  int  $roleId
     * @param  int  $moduleId
     * @return ResponseInterface
     */
    public function actions(int $roleId, int $moduleId): ResponseInterface
    {
        if (($permissions = $this->PM->getActions($roleId, $moduleId)) === null) {
            return $this->failNotFound(lang('Api.errors.noData'));
        }

        return $this->cmsRespond($permissions);
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
     * Create a new resource object, from "posted" parameters
     *
     * @return ResponseInterface
     */
    public function create(): ResponseInterface
    {
        //
    }

    /**
     * @param  int  $id
     * @return ResponseInterface
     */
    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->PM->forEdit((int) $id)) === null) {
            return $this->failNotFound(lang('Api.errors.noData'));
        }

        return $this->cmsRespond($data->toArray());
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return ResponseInterface
     */
    public function update($id = null): ResponseInterface
    {
        if (empty($data = $this->request->getJSON(true))) {
            return $this->failValidationErrors(lang('Api.errors.noData'));
        }

        if (($permissions = $this->PM->forEdit((int) $id)) === null) {
            return $this->failNotFound();
        }

        $data['updated_by_id'] = $this->userData->userId;

        if ($this->PM->save($data) === false) {
            return $this->failValidationErrors($this->PM->errors());
        }

        if (($role = $this->RM->find($permissions->role_id)) === null) {
            return $this->failNotFound();
        }

        cache()->delete('RAM_' . $role->role);

        return $this->respondNoContent();
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return ResponseInterface
     */
    public function delete($id = null): ResponseInterface
    {
        //
    }
}
