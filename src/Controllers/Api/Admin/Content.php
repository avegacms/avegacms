<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers\Api\Admin;

use AvegaCms\Models\Admin\{ContentModel, MetaDataModel};
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class Content extends AvegaCmsAdminAPI
{
    protected MetaDataModel $MDM;
    protected ContentModel  $CM;

    public function __construct()
    {
        parent::__construct();
        $this->CM  = new ContentModel();
        $this->MDM = new MetaDataModel();
    }

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        // TODO добавить данные для фильтра
        return $this->cmsRespond($this->MDM->selectPages($this->request->getGet() ?? []));
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
     * @param  int|null  $id
     * @return ResponseInterface
     */
    public function edit(?int $id = null): ResponseInterface
    {
        if (($data = $this->MDM->editPageMetaData($id)) === null) {
            return $this->failNotFound();
        }
        return $this->cmsRespond((array) $data);
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

    protected function guide(): array
    {
        return [
            'locales'    => [],
            'statuses'   => [],
            'changefreq' => []
        ];
    }
}
