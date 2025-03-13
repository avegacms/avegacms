<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use AvegaCms\Models\Admin\PermissionsModel;
use AvegaCms\Models\Admin\RolesModel;
use AvegaCms\Models\Admin\UserRolesModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class Roles extends AvegaCmsAdminAPI
{
    protected RolesModel $RM;
    protected PermissionsModel $PM;
    protected UserRolesModel $URM;

    public function __construct()
    {
        parent::__construct();
        $this->RM  = model(RolesModel::class);
        $this->PM  = model(PermissionsModel::class);
        $this->URM = model(UserRolesModel::class);
    }

    public function index(): ResponseInterface
    {
        return $this->cmsRespond($this->RM->filter($this->request->getGet() ?? [])->apiPagination());
    }

    /**
     * @throws ReflectionException
     */
    public function create(): ResponseInterface
    {
        $data = $this->apiData;

        $data['created_by_id'] = $this->userData->userId;

        if (($id = $this->RM->insert($data)) === false) {
            return $this->cmsRespondFail($this->RM->errors());
        }

        $defaultPermissions = $this->PM->getDefaultPermissions();
        $rolePermissions    = [];

        foreach ($defaultPermissions as $permission) {
            $permission->role_id       = $id;
            $permission->created_by_id = $this->userData->userId;
            $permission->extra         = json_encode($permission->extra);
            $rolePermissions[]         = $permission;
        }

        if (! $this->PM->insertBatch($rolePermissions)) {
            return $this->failNotFound(lang('Api.errors.create', ['Permissions']));
        }

        return $this->cmsRespondCreated($id);
    }

    public function edit(?int $id = null): ResponseInterface
    {
        if (($data = $this->RM->find($id)) === null) {
            return $this->failNotFound();
        }

        return $this->cmsRespond((array) $data);
    }

    public function permissions(?int $id = null): ResponseInterface
    {
        if ($this->RM->find($id) === null) {
            return $this->failNotFound();
        }

        return $this->cmsRespond((array) $this->PM->getDefaultPermissions($id));
    }

    /**
     * @throws ReflectionException
     */
    public function update(?int $id = null): ResponseInterface
    {
        $data = $this->apiData;

        if (($role = $this->RM->find($id)) === null) {
            return $this->failNotFound();
        }

        $data['id']            = $id;
        $data['updated_by_id'] = $this->userData->userId;

        if ($this->RM->save($data) === false) {
            return $this->cmsRespondFail($this->RM->errors());
        }

        cache()->delete('RAM_' . $role->role);

        return $this->respondNoContent();
    }

    /**
     * @param mixed|null $id
     *
     * @throws ReflectionException
     */
    public function delete(?int $id): ResponseInterface
    {
        if (($role = $this->RM->find($id)) === null) {
            return $this->failNotFound();
        }

        if (in_array($role->role, ['root', 'default'], true)) {
            return $this->cmsRespondFail(lang('Roles.errors.deleteIsDefault'));
        }

        if (! $this->RM->delete($id)) {
            return $this->cmsRespondFail(lang('Api.errors.delete', ['Roles']));
        }

        cache()->delete('RAM_' . $role->role);

        if (! $this->PM->where(['role_id' => $id])->delete()) {
            return $this->cmsRespondFail(lang('Api.errors.delete', ['Permissions']));
        }

        if (! $this->URM->where(['role_id' => $id])->update(null, ['role_id' => 4])) {
            return $this->cmsRespondFail(lang('Api.errors.update', ['UserRoles']));
        }

        return $this->respondNoContent();
    }
}
