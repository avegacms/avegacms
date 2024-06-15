<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers\Api\Public;

use AvegaCms\Controllers\Api\AvegaCmsAPI;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class Content extends AvegaCmsAPI
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        return $this->cmsRespond(['hello' => 'world']);
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
