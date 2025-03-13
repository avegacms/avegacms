<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use AvegaCms\Enums\FieldsReturnTypes;
use AvegaCms\Models\Admin\ModulesModel;
use AvegaCms\Models\Admin\SettingsModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class Settings extends AvegaCmsAdminAPI
{
    protected SettingsModel $SM;

    public function __construct()
    {
        parent::__construct();
        $this->SM = model(SettingsModel::class);
    }

    public function index(): ResponseInterface
    {
        return $this->cmsRespond($this->SM->selectSettings()->filter($this->request->getGet() ?? [])->apiPagination());
    }

    public function new(): ResponseInterface
    {
        return $this->cmsRespond(
            [
                'modules' => [0 => 'AvegaCms Core', ...model(ModulesModel::class)->getModulesList()],
                'return'  => FieldsReturnTypes::get('value'),
            ]
        );
    }

    /**
     * @throws ReflectionException
     */
    public function create(): ResponseInterface
    {
        try {
            $data = $this->apiData;

            $data['created_by_id'] = $this->userData->userId;

            if (! $id = $this->SM->insert($data)) {
                return $this->cmsRespondFail($this->SM->errors());
            }

            return $this->cmsRespondCreated($id);
        } catch (DatabaseException $e) {
            return $this->cmsRespondFail($e->getMessage());
        }
    }

    /**
     * Return the editable properties of a resource object
     *
     * @param mixed|null $id
     */
    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->SM->forEdit((int) $id)) === null) {
            return $this->failNotFound();
        }

        return $this->cmsRespond($data->toArray());
    }

    /**
     * @param mixed|null $id
     *
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
                return $this->cmsRespondFail($this->SM->errors());
            }

            return $this->respondNoContent();
        } catch (DatabaseException $e) {
            return $this->cmsRespondFail($e->getMessage());
        }
    }

    public function delete($id = null): ResponseInterface
    {
        if (($data = $this->SM->forEdit((int) $id)) === null) {
            return $this->failNotFound();
        }

        if ($data->is_core === 1) {
            return $this->cmsRespondFail(lang('Settings.errors.deleteIsDefault'));
        }

        if (! $this->SM->delete($id)) {
            return $this->cmsRespondFail(lang('Api.errors.delete', ['Settings']));
        }

        return $this->respondNoContent();
    }

    /**
     * @return list<string>
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
            'json'      => 'json',
        ];
    }
}
