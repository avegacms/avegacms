<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\SettingsModel;
use AvegaCms\Entities\SettingsEntity;

class Configs extends AvegaCmsAdminAPI
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
        $settings = $this->SM->selectSettings()->filter($this->request->getGet() ?? [])->pagination();
        
        return $this->cmsRespond($settings['list'], $settings['pagination']);
    }

    /**
     * Return the properties of a resource object
     *
     * @param $id
     * @return ResponseInterface
     */
    public function show($id = null): ResponseInterface
    {
        //
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
        //
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @param $id
     * @return ResponseInterface
     */
    public function update($id = null): ResponseInterface
    {
        //
    }

    /**
     * Delete the designated resource object from the model
     *
     * @param $id
     * @return ResponseInterface
     */
    public function delete($id = null): ResponseInterface
    {
        //
    }
}