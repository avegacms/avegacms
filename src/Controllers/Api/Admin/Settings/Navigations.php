<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\NavigationsModel;
use AvegaCms\Entities\NavigationsEntity;

class Navigations extends AvegaCmsAdminAPI
{
    protected NavigationsModel $NM;

    public function __construct()
    {
        parent::__construct();
        $this->NM = model(NavigationsModel::class);
    }

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        //
    }

    /**
     * Return the properties of a resource object
     *
     * @param $id
     * @return ResponseInterface
     */
    public function show($id = null): ResponseInterface
    {
        if ($this->NM->forEdit($id) === null) {
            return $this->failNotFound();
        }
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return ResponseInterface
     */
    public function new(): ResponseInterface
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return ResponseInterface
     */
    public function create(): ResponseInterface
    {
        //
    }

    /**
     * Return the editable properties of a resource object
     *
     * @param $id
     * @return ResponseInterface
     */
    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->NM->forEdit($id)) === null) {
            return $this->failNotFound();
        }
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @param $id
     * @return ResponseInterface
     */
    public function update($id = null): ResponseInterface
    {
        if ($this->NM->forEdit($id) === null) {
            return $this->failNotFound();
        }
    }

    /**
     * Delete the designated resource object from the model
     *
     * @param $id
     * @return ResponseInterface
     */
    public function delete($id = null): ResponseInterface
    {
        if ($this->NM->forEdit($id) === null) {
            return $this->failNotFound();
        }

        if ( ! $this->NM->where(['is_admin' => 0])->delete($id)) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Locales']));
        }

        return $this->respondNoContent();
    }
}
