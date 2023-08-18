<?php

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use AvegaCms\Models\Admin\LocalesModel;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;


class Locales extends AvegaCmsAdminAPI
{
    protected LocalesModel $LM;
    protected array        $patchFields = ['active'];

    public function __construct()
    {
        parent::__construct();
        $this->LM = model(LocalesModel::class);
    }

    /**
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $locales = $this->LM->filter($this->request->getGet() ?? [])->pagination();

        return $this->cmsRespond($locales['list'], $locales['pagination']);
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function show($id = null): ResponseInterface
    {
        return $this->_getLocale($id);
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function edit($id = null): ResponseInterface
    {
        return $this->_getLocale($id);
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

        if ( ! $this->validateData($data, $this->_rules())) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if ( ! $id = $this->LM->insert($data)) {
            return $this->failValidationErrors(lang('Api.errors.create'));
        }

        return $this->cmsRespondCreated($id);
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

        if ($this->LM->find($id) === null) {
            return $this->failNotFound();
        }

        $data['updated_by_id'] = $this->userData->userId;

        if ( ! $this->validateData($data, $this->_rules())) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if ($this->LM->save($data) === false) {
            return $this->failValidationErrors(lang('Api.errors.update'));
        }

        return $this->respondNoContent();
    }

    /**
     * @param $id
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function patch($id = null): ResponseInterface
    {
        if (empty($data = $this->request->getJSON(true))) {
            return $this->failValidationErrors(lang('Api.errors.noData'));
        }

        if ($this->LM->find($id) === null) {
            return $this->failNotFound();
        }

        if (in_array($key = key($data), $this->patchFields) && ! $this->validateData($data, $this->_rules($key))) {
            return $this->failValidationErrors(lang('Api.errors.save'));
        }

        $data['updated_by_id'] = $this->userData->userId;

        if ($this->LM->update($id, $data) === false) {
            return $this->failValidationErrors($this->LM->errors());
        }

        return $this->cmsRespond();
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function delete($id): ResponseInterface
    {
        if (($data = $this->LM->forEdit($id)) === null) {
            return $this->failNotFound(lang('Api.errors.noData'));
        }

        if ($data->is_default == 1) {
            return $this->failValidationErrors(lang('Locales.errors.deleteIsDefault'));
        }

        if ( ! $this->LM->delete($id)) {
            return $this->failValidationErrors(lang('Api.errors.delete'));
        }

        return $this->respondNoContent();
    }

    /**
     * @param  string|null  $field
     * @return array
     */
    private function _rules(?string $field = null): array
    {
        $rules = [
            'id'            => ['rules' => 'permit_empty'],
            'slug'          => ['rules' => 'required|alpha_dash|max_length[20]|is_unique[locales.slug,id,{id}]'],
            'locale'        => ['rules' => 'required|max_length[32]'],
            'locale_name'   => ['rules' => 'required|max_length[100]'],
            'home'          => ['rules' => 'required|max_length[255]'],
            'extra'         => ['rules' => 'permit_empty'],
            'is_default'    => ['rules' => 'permit_empty|is_natural|in_list[0,1]'],
            'active'        => ['rules' => 'permit_empty|is_natural|in_list[0,1]'],
            'created_by_id' => ['rules' => 'permit_empty'],
            'updated_by_id' => ['rules' => 'permit_empty']
        ];

        return is_null($field) ? $rules : [$rules[$field]] ?? [];
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    private function _getLocale($id = null): ResponseInterface
    {
        if (($data = $this->LM->forEdit($id)) === null) {
            return $this->failNotFound(lang('Api.errors.noData'));
        }

        return $this->cmsRespond($data->toArray());
    }
}