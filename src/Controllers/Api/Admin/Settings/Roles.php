<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\{RolesModel, PermissionsModel, UserRolesModel};
use AvegaCms\Entities\PermissionsEntity;
use ReflectionException;

class Roles extends AvegaCmsAdminAPI
{
    protected RolesModel       $RM;
    protected PermissionsModel $PM;
    protected UserRolesModel   $URM;

    public function __construct()
    {
        parent::__construct();
        $this->RM = model(RolesModel::class);
        $this->PM = model(PermissionsModel::class);
        $this->URM = model(UserRolesModel::class);
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

        $defaultPermissions = $this->PM->getDefaultPermissions();
        $rolePermissions = [];
        foreach ($defaultPermissions as $permission) {
            $permission['role_id'] = $id;
            $permission['created_by_id'] = $this->userData->userId;
            $rolePermissions[] = (new PermissionsEntity($permission));
        }

        if ( ! $this->PM->insertBatch($rolePermissions)) {
            return $this->failNotFound(lang('Api.errors.create', ['Permissions']));
        }

        return $this->cmsRespondCreated($id);
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->RM->find($id)) === null) {
            return $this->failNotFound(lang('Api.errors.noData'));
        }

        return $this->cmsRespond(
            [
                'role'        => $data->toArray(),
                'permissions' => $this->PM->getDefaultPermissions($id)
            ]
        );
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

        if (($role = $this->RM->find($id)) === null) {
            return $this->failNotFound();
        }

        $data['updated_by_id'] = $this->userData->userId;

        if ($this->RM->save($data) === false) {
            return $this->failValidationErrors($this->RM->errors());
        }

        cache()->delete('RAM_' . $role->role);

        return $this->respondNoContent();
    }

    /**
     * @param $id
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function delete($id = null): ResponseInterface
    {
        if (($role = $this->RM->find($id)) === null) {
            return $this->failNotFound();
        }

        if (in_array($role->role, ['root', 'default'])) {
            return $this->failValidationErrors(lang('Roles.errors.deleteIsDefault'));
        }

        if ( ! $this->RM->delete($id)) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Roles']));
        }

        cache()->delete('RAM_' . $role->role);

        if ( ! $this->PM->where(['role_id' => $id])->delete()) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Permissions']));
        }

        if ( ! $this->URM->update(['role_id' => 4], ['role_id' => $id])) {
            return $this->failValidationErrors(lang('Api.errors.update', ['UserRoles']));
        }

        return $this->respondNoContent();
    }
}
