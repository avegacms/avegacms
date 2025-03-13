<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use AvegaCms\Entities\NavigationsEntity;
use AvegaCms\Enums\NavigationTypes;
use AvegaCms\Models\Admin\NavigationsModel;
use AvegaCms\Utilities\SeoUtils;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class Navigations extends AvegaCmsAdminAPI
{
    protected NavigationsModel $NM;

    public function __construct()
    {
        parent::__construct();
        $this->NM = model(NavigationsModel::class);
    }

    /**
     * Return a new resource object, with default properties
     */
    public function new(): ResponseInterface
    {
        return $this->cmsRespond(
            [
                'locales'  => array_column(SeoUtils::Locales(), 'locale_name', 'id'),
                'navTypes' => NavigationTypes::get('value'),
            ]
        );
    }

    /**
     * @throws ReflectionException
     */
    public function create(): ResponseInterface
    {
        $data = $this->apiData;

        $data['is_admin']      = 0;
        $data['icon']          = '';
        $data['created_by_id'] = $this->userData->userId;

        if (! $id = $this->NM->insert((new NavigationsEntity($data)))) {
            return $this->cmsRespondFail($this->NM->errors());
        }

        return $this->cmsRespondCreated($id);
    }

    /**
     * Return the editable properties of a resource object
     *
     * @param mixed|null $id
     */
    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->NM->forEdit((int) $id)) === null) {
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
        $data = $this->apiData;

        if ($this->NM->forEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        $data['updated_by_id'] = $this->userData->userId;

        if ($this->NM->save($data) === false) {
            return $this->cmsRespondFail($this->NM->errors());
        }

        return $this->respondNoContent();
    }

    /**
     * Delete the designated resource object from the model
     *
     * @param mixed|null $id
     */
    public function delete($id = null): ResponseInterface
    {
        if ($this->NM->where(['is_admin' => 0])->forEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        if (! $this->NM->where(['is_admin' => 0])->delete($id)) {
            return $this->cmsRespondFail(lang('Api.errors.delete', ['Navigations']));
        }

        return $this->respondNoContent();
    }
}
