<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use AvegaCms\Enums\UserStatuses;
use AvegaCms\Models\Admin\RolesModel;
use AvegaCms\Models\Admin\UserModel;
use AvegaCms\Models\Admin\UserRolesModel;
use AvegaCms\Utilities\Exceptions\UploaderException;
use AvegaCms\Utilities\Uploader;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class Users extends AvegaCmsAdminAPI
{
    protected RolesModel $RM;
    protected UserModel $UM;
    protected UserRolesModel $URM;

    public function __construct()
    {
        parent::__construct();
        $this->UM  = model(UserModel::class);
        $this->RM  = model(RolesModel::class);
        $this->URM = model(UserRolesModel::class);
    }

    public function index(): ResponseInterface
    {
        return $this->cmsRespond($this->URM->getUsers()->filter($this->request->getGet() ?? [])->apiPagination());
    }

    public function new(): ResponseInterface
    {
        return $this->cmsRespond(
            [
                'email'    => '',
                'password' => '',
            ],
            [
                'roles'    => $this->RM->getRolesList(),
                'statuses' => UserStatuses::list(),
            ]
        );
    }

    /**
     * @throws ReflectionException
     */
    public function create(): ResponseInterface
    {
        if ($this->validateData($this->apiData, $this->_rules()) === false) {
            return $this->cmsRespondFail($this->validator->getErrors());
        }

        $data = $this->validator->getValidated();

        $data['created_by_id'] = $this->userData->userId;

        $roles = $data['role'];
        unset($data['role']);

        if (! $id = $this->UM->insert($data)) {
            return $this->cmsRespondFail($this->UM->errors());
        }

        $this->_setRoles((int) $id, $roles);

        return $this->cmsRespondCreated($id);
    }

    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->UM->forEdit((int) $id)) === null) {
            return $this->failNotFound();
        }

        if (($data->roles = $this->URM->where(['user_id' => $id])->findColumn('role_id')) === null) {
            return $this->failNotFound();
        }

        return $this->cmsRespond((array) $data);
    }

    /**
     * @param mixed|null $id
     *
     * @throws ReflectionException
     */
    public function update($id = null): ResponseInterface
    {
        if ($this->UM->forEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        if ($this->validateData($this->apiData, $this->_rules()) === false) {
            return $this->cmsRespondFail($this->validator->getErrors());
        }

        $data                  = $this->validator->getValidated();
        $data['updated_by_id'] = $this->userData->userId;

        if ($this->UM->save($data) === false) {
            return $this->cmsRespondFail($this->UM->errors());
        }

        return $this->respondNoContent();
    }

    /**
     * @param mixed|null $id
     *
     * @throws ReflectionException
     */
    public function upload(?int $id = null): ResponseInterface
    {
        if ($this->UM->forEdit($id) === null) {
            return $this->failNotFound();
        }

        try {
            $avatar = Uploader::file(
                'file',
                'users',
                [
                    'is_image' => true,
                    'ext_in'   => 'png,jpg,jpeg,gif',
                ]
            );

            if (! $this->UM->save(['id' => $id, 'avatar' => $avatar['fileName']])) {
                return $this->cmsRespondFail($this->UM->errors());
            }

            return $this->cmsRespond($avatar);
        } catch (UploaderException $e) {
            return $this->cmsRespondFail(empty($e->getMessages()) ? $e->getMessage() : $e->getMessages());
        }
    }

    public function delete($id = null): ResponseInterface
    {
        if (($user = $this->UM->find($id)) === null) {
            return $this->failNotFound();
        }

        if (! $this->UM->delete($id)) {
            return $this->cmsRespondFail(lang('Api.errors.delete', ['Users']));
        }

        if (! $this->URM->where(['user_id' => $id])->delete()) {
            return $this->cmsRespondFail(lang('Api.errors.delete', ['UserRoles']));
        }

        $this->_removeAvatar($user->avatar);

        return $this->respondNoContent();
    }

    private function _rules(): array
    {
        return [
            'login' => [
                'label' => 'Логин',
                'rules' => 'permit_empty|alpha_dash|max_length[36]',
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'required|max_length[255]|valid_email|is_unique[users.email,id,{id}]',
            ],
            'password' => [
                'label' => 'Пароль',
                'rules' => 'required|max_length[64]|verify_password',
            ],
            'password_conf' => [
                'label'  => 'Подтверждение пароля',
                'rules'  => 'required|max_length[64]|matches[password]',
                'errors' => [
                    'matches' => 'Пароли не совпадают',
                ],
            ],
            'role' => [
                'label' => 'Роль',
                'rules' => 'required|is_natural_no_zero',
            ],
        ];
    }

    private function _removeAvatar(string $file): void
    {
        if (! empty($file) && file_exists($path = FCPATH . 'uploads/users' . $file)) {
            unlink($path);
        }
    }

    /**
     * @throws ReflectionException
     */
    private function _setRoles(int $userId, array $roles): void
    {
        $this->URM->where(['user_id' => $userId])->delete();

        foreach ($roles as $role) {
            $setRoles[] = [
                'role_id'       => $role,
                'user_id'       => $userId,
                'created_by_id' => $this->userData->userId,
            ];
        }
        $this->URM->insertBatch($setRoles ?? null);
    }
}
