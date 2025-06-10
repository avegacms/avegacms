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
        $this->apiData['id']     = 0;
        $this->apiData['status'] = UserStatuses::Active->value;

        if ($this->validateData($this->apiData, $this->_rules()) === false) {
            return $this->cmsRespondFail($this->validator->getErrors());
        }

        $data = $this->validator->getValidated();

        $data['created_by_id'] = $this->userData->userId;

        $roles = $data['role'];
        unset($data['role'], $data['id']);

        $this->UM->skipValidation();

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

        return $this->cmsRespond(
            (array) $data,
            [
                'roles'    => $this->RM->getRolesList(),
                'statuses' => UserStatuses::list(),
            ]
        );
    }

    /**
     * @param mixed|null $id
     *
     * @throws ReflectionException
     */
    public function update($id = null): ResponseInterface
    {
        if (($old = $this->UM->forEdit((int) $id)) === null) {
            return $this->failNotFound();
        }

        $roleName = (new RolesModel())->where(['id' => $old?->role])->first()?->role;

        $this->apiData['id'] = $id;

        if (in_array($roleName, ['staffer', 'client'])) {
            $this->apiData['email'] = $old->email;
            if ($this->validateData($this->apiData, $this->UM->getValidationRules(['only' => ['id', 'status', 'email']])) === false) {
                return $this->cmsRespondFail($this->validator->getErrors());
            }
        } else {
            if ($this->validateData($this->apiData, $this->_rules(false)) === false) {
                return $this->cmsRespondFail($this->validator->getErrors());
            }
        }

        $data                  = $this->validator->getValidated();
        $data['updated_by_id'] = $this->userData->userId;

        if (isset($data['password']) && trim($data['password']) === '') {
            unset($data['password']);
        }

        if ($old->email !== $data['email']) {
            if ($this->UM->select('email')->where(['email' => $data['email']])->first() !== null) {
                return $this->cmsRespondFail('Данная почта уже зарегистрирована');
            }
        }

        $this->UM->skipValidation();

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

    public function delete(?int $id = null): ResponseInterface
    {
        if ($this->UM->forEdit($id) === null) {
            return $this->failNotFound();
        }

        if (! $this->UM->delete($id)) {
            return $this->cmsRespondFail(lang('Api.errors.delete', ['Users']));
        }

        if (! $this->URM->where(['user_id' => $id])->delete()) {
            return $this->cmsRespondFail(lang('Api.errors.delete', ['UserRoles']));
        }

        return $this->respondNoContent();
    }

    private function _rules(bool $isReg = true): array
    {
        $isReg = ($isReg ? 'required' : 'permit_empty');

        return [
            'id' => [
                'label' => 'ID',
                'rules' => $isReg,
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'required|max_length[255]|valid_email|is_unique[users.email,id,{id}]',
            ],
            'password' => [
                'label' => 'Пароль',
                'rules' => $isReg . '|max_length[64]|verify_password',
            ],
            'password_conf' => [
                'label'  => 'Подтверждение пароля',
                'rules'  => $isReg . '|max_length[64]|matches[password]',
                'errors' => [
                    'matches' => 'Пароли не совпадают',
                ],
            ],
            'status' => [
                'label' => 'Статус',
                'rules' => 'required|in_list[' . implode(',', UserStatuses::get('value')) . ']',
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
    private function _setRoles(int $userId, array|int $roles): void
    {
        $this->URM->where(['user_id' => $userId])->delete();

        $roles = is_array($roles) ? $roles : [$roles];

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
