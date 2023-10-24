<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\{UserModel, UserRolesModel, RolesModel, UserTokensModel};
use AvegaCms\Entities\{UserEntity, UserRolesEntity};
use AvegaCms\Utils\{Cms, Uploader};
use AvegaCms\Utils\Exceptions\UploaderException;
use ReflectionException;

class Users extends AvegaCmsAdminAPI
{
    protected RolesModel     $RM;
    protected UserModel      $UM;
    protected UserRolesModel $URM;

    /**
     * @var array|string[]
     */
    protected array $userStatus = [
        'pre-registration',
        'active',
        'banned',
        'deleted',
        ''
    ];

    public function __construct()
    {
        parent::__construct();
        $this->UM  = model(UserModel::class);
        $this->RM  = model(RolesModel::class);
        $this->URM = model(UserRolesModel::class);
    }

    /**
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        return $this->cmsRespond($this->URM->getUsers()->filter($this->request->getGet() ?? [])->apiPagination());
    }

    /**
     * @return ResponseInterface
     */
    public function new(): ResponseInterface
    {
        return $this->cmsRespond(
            [
                'roles'    => $this->RM->getRolesList(),
                'statuses' => $this->userStatus
            ]
        );
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

        $roles = $data['roles'];
        unset($data['roles']);

        if ( ! $id = $this->UM->insert((new UserEntity($data)))) {
            return $this->failValidationErrors($this->UM->errors());
        }

        $this->_setRoles((int) $id, $roles);

        return $this->cmsRespondCreated($id);
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

        if (($data->roles = $this->URM->where(['user_id' => $id])->findColumn('role_id')) === null) {
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

        if (($user = $this->UM->find($id)) === null) {
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
            $this->_setRoles((int) $id, $roles);
        }

        if ($this->UM->save((new UserEntity($data))) === false) {
            return $this->failValidationErrors($this->UM->errors());
        }

        if ($reset && Cms::settings('core.auth.useJwt')) {
            (new UserTokensModel())->where(['user_id' => $id])->delete();
        }

        if (isset($data['avatar']) && empty($data['avatar'])) {
            $this->_removeAvatar($user->avatar);
        }

        return $this->respondNoContent();
    }

    /**
     * @param $id
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function upload($id = null): ResponseInterface
    {
        if ($this->UM->forEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        try {
            $avatar = Uploader::file(
                'file',
                'users',
                [
                    'is_image' => true,
                    'ext_in'   => 'png,jpg,jpeg,gif'
                ]
            );

            if ( ! $this->UM->save((new UserEntity(['id' => $id, 'avatar' => $avatar['fileName']])))) {
                return $this->failValidationErrors($this->UM->errors());
            }

            return $this->cmsRespond($avatar);
        } catch (UploaderException $e) {
            return $this->failValidationErrors(empty($e->getMessages()) ? $e->getMessage() : $e->getMessages());
        }
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function delete($id = null): ResponseInterface
    {
        if (($user = $this->UM->find($id)) === null) {
            return $this->failNotFound();
        }

        if ( ! $this->UM->delete($id)) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Users']));
        }

        if ( ! $this->URM->where(['user_id' => $id])->delete()) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['UserRoles']));
        }

        $this->_removeAvatar($user->avatar);

        return $this->respondNoContent();
    }

    /**
     * @param  string  $file
     * @return void
     */
    private function _removeAvatar(string $file): void
    {
        if ( ! empty($file) && file_exists($path = FCPATH . 'uploads/users' . $file)) {
            unlink($path);
        }
    }

    /**
     * @param  int  $userId
     * @param  array  $roles
     * @return void
     * @throws ReflectionException
     */
    private function _setRoles(int $userId, array $roles): void
    {
        $this->URM->where(['user_id' => $userId])->delete();
        $URE = new UserRolesEntity();
        foreach ($roles as $role) {
            $setRoles[] = $URE->fill([
                'role_id'       => $role,
                'user_id'       => $userId,
                'created_by_id' => $this->userData->userId,
            ]);
        }
        $this->URM->insertBatch($setRoles ?? null);
    }

}
