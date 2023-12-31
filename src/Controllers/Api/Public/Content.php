<?php

namespace AvegaCms\Controllers\Api\Public;

use CodeIgniter\RESTful\ResourceController;

class Content extends ResourceController
{
    /**
     * @return \CodeIgniter\HTTP\ResponseInterface|string|void
     */
    public function index()
    {
        return $this->respond(
            [
                'data' => '!!!'
            ]
        );
    }

    /**
     * Return the properties of a resource object
     *
     * @return void
     */
    public function show($id = null)
    {
        //
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return void
     */
    public function new()
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Return the editable properties of a resource object
     *
     * @return void
     */
    public function edit($id = null)
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return void
     */
    public function update($id = null)
    {
        //
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return void
     */
    public function delete($id = null)
    {
        //
    }
}
