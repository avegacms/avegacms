<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\{SettingsModel, ModulesModel};
use ReflectionException;
use AvegaCms\Enums\FieldsReturnTypes;

class Settings extends AvegaCmsAdminAPI
{
    protected SettingsModel $SM;

    public function __construct()
    {
        parent::__construct();
        $this->SM = model(SettingsModel::class);
    }

    /**
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        return $this->cmsRespond($this->SM->selectSettings()->filter($this->request->getGet() ?? [])->apiPagination());
    }

    /**
     * @return ResponseInterface
     */
    public function new(): ResponseInterface
    {
        return $this->cmsRespond(
            [
                'modules' => [0 => 'AvegaCms Core', ...model(ModulesModel::class)->getModulesList()],
                'return'  => FieldsReturnTypes::get('value')
            ]
        );
    }

    /**
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function create(): ResponseInterface
    {
        try {
            $data = $this->apiData;

            $data['created_by_id'] = $this->userData->userId;

            if ( ! $id = $this->SM->insert($data)) {
                return $this->failValidationErrors($this->SM->errors());
            }

            return $this->cmsRespondCreated($id);
        } catch (DatabaseException $e) {
            return $this->failValidationErrors($e->getMessage());
        }
    }

    /**
     * Return the editable properties of a resource object
     *
     * @param $id
     * @return ResponseInterface
     */
    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->SM->forEdit((int) $id)) === null) {
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
        try {
            $data = $this->apiData;

            if (($settings = $this->SM->find($id)) === null) {
                return $this->failNotFound();
            }

            $data['entity']        = $settings->entity;
            $data['updated_by_id'] = $this->userData->userId;

            if ($this->SM->save($data) === false) {
                return $this->failValidationErrors($this->SM->errors());
            }

            return $this->respondNoContent();
        } catch (DatabaseException $e) {
            return $this->failValidationErrors($e->getMessage());
        }
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function delete($id = null): ResponseInterface
    {
        if (($data = $this->SM->forEdit((int) $id)) === null) {
            return $this->failNotFound();
        }

        if ($data->is_core == 1) {
            return $this->failValidationErrors(lang('Settings.errors.deleteIsDefault'));
        }

        if ( ! $this->SM->delete($id)) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Settings']));
        }

        return $this->respondNoContent();
    }

    /**
     * @return string[]
     */
    private function _returnTypes(): array
    {
        return [
            'integer'   => 'integer',
            'float'     => 'float',
            'string'    => 'string',
            'boolean'   => 'boolean',
            'array'     => 'array',
            'datetime'  => 'datetime',
            'timestamp' => 'timestamp',
            'json'      => 'json'
        ];
    }
}
