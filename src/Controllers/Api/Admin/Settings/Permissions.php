<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\{PermissionsModel, RolesModel};
use ReflectionException;

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
            return $this->failNotFound();
        }

        return $this->cmsRespond($permissions);
    }

    /**
     * @param  int  $id
     * @return ResponseInterface
     */
    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->PM->forEdit((int) $id)) === null) {
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
     * @param $id
     * @return ResponseInterface
     */
    public function delete($id = null): ResponseInterface
    {
        if (($permissions = $this->PM->forEdit((int) $id)) === null) {
            return $this->failNotFound();
        }

        if (($role = $this->RM->find($permissions->role_id)) === null) {
            return $this->failNotFound();
        }

        if (in_array($role->role, ['root', 'default'])) {
            return $this->failValidationErrors(lang('Permissions.errors.notDelete'));
        }

        if ( ! $this->PM->delete($id)) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Permissions']));
        }

        cache()->delete('RAM_' . $role->role);

        return $this->respondNoContent();
    }
}
