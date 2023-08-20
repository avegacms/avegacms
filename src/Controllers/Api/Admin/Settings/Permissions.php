<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\PermissionsModel;
use AvegaCms\Entities\PermissionsEntity;

class Permissions extends AvegaCmsAdminAPI
{
    protected PermissionsModel $PM;

    public function __construct()
    {
        parent::__construct();
        $this->PM = model(PermissionsModel::class);
    }

    /**
     * @param  int  $roleId
     * @param  int  $moduleId
     * @return ResponseInterface
     */
    public function actions(int $roleId, int $moduleId): ResponseInterface
    {
        if (($permissions = $this->PM->getActions($roleId, $moduleId)) === null) {
            return $this->failNotFound(lang('Api.errors.noData'));
        }

        return $this->cmsRespond($permissions);
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
     * @return ResponseInterface
     */
    public function edit($id = null): ResponseInterface
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return ResponseInterface
     */
    public function update($id = null): ResponseInterface
    {
        //
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return ResponseInterface
     */
    public function delete($id = null): ResponseInterface
    {
        //
    }
}
