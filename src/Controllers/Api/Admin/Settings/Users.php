<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\{UserModel, UserRolesModel, RolesModel, UserTokensModel};
use AvegaCms\Entities\UserRolesEntity;
use ReflectionException;

class Users extends AvegaCmsAdminAPI
{
    protected RolesModel      $RM;
    protected UserModel       $UM;
    protected UserRolesModel  $URM;
    protected UserTokensModel $UTM;

    public function __construct()
    {
        parent::__construct();
        $this->UM = model(UserModel::class);
        $this->RM = model(RolesModel::class);
        $this->URM = model(UserRolesModel::class);
    }

    /**
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $users = $this->URM->getUsers()->filter($this->request->getGet() ?? [])->pagination();

        return $this->cmsRespond($users['list'], $users['pagination']);
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function show($id = null): ResponseInterface
    {
        //
    }

    /**
     * @return ResponseInterface
     */
    public function new(): ResponseInterface
    {
        //
    }

    /**
     * @return ResponseInterface
     */
    public function create(): ResponseInterface
    {
        //
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->UM->forEdit((int) $id)) === null) {
            return $this->failNotFound();
        }

        if (($data->role = $this->URM->where(['user_id' => $id])->findColumn('role_id')) === null) {
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
        if (empty($data = $this->request->getJSON(true))) {
            return $this->failValidationErrors(lang('Api.errors.noData'));
        }

        if ($this->UM->forEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        $data['updated_by_id'] = $this->userData->userId;

        $reset = false;
        if (isset($data['reset'])) {
            $reset = boolval($data['reset']);
            unset($data['reset']);
        }

        if ($data['roles'] ?? false) {
            $roles = $data['roles'];
            unset($data['roles']);
            $this->URM->where(['user_id' => $id])->delete();
            $URE = new UserRolesEntity();
            foreach ($roles as $role) {
                $setRoles[] = $URE->fill([
                    'role_id'       => $role,
                    'user_id'       => $id,
                    'created_by_id' => $this->userData->userId,
                ]);
            }
            $this->URM->insertBatch($setRoles ?? null);
        }

        if ($this->UM->save($data) === false) {
            return $this->failValidationErrors($this->UM->errors());
        }

        if ($reset && settings('core.auth.useJwt')) {
            (new UserTokensModel())->where(['user_id' => $id])->delete();
        }

        return $this->respondNoContent();
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function delete($id = null): ResponseInterface
    {
        //
    }

    /**
     * @return array
     */
    private function _getRoles(): array
    {
        if (is_null($roles = cache($fileCacheName = 'UserRolesList'))) {
            $rolesData = $this->RM->select(['id', 'role'])->orderBy('role', 'ASC')->findAll();
            foreach ($rolesData as $role) {
                $roles[$role->id] = $role->role;
            }
            cache()->save($fileCacheName, $roles, DAY * 30);
            unset($rolesData);
        }

        return $roles;
    }
}
