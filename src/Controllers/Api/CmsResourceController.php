<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api;

use AvegaCms\Traits\CmsResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\BaseResource;


class CmsResourceController extends BaseResource
{
    use CmsResponseTrait;

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        return $this->fail(lang('RESTful.notImplemented', ['index']), 501);
    }

    /**
     * Return the properties of a resource object
     *
     * @param  int|null|string  $id
     *
     * @return ResponseInterface
     */
    public function show(int|null|string $id): ResponseInterface
    {
        return $this->fail(lang('RESTful.notImplemented', ['show']), 501);
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return ResponseInterface
     */
    public function new(): ResponseInterface
    {
        return $this->fail(lang('RESTful.notImplemented', ['new']), 501);
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return ResponseInterface
     */
    public function create(): ResponseInterface
    {
        return $this->fail(lang('RESTful.notImplemented', ['create']), 501);
    }

    /**
     * Return the editable properties of a resource object
     *
     * @param  int|null|string  $id
     *
     * @return ResponseInterface
     */
    public function edit(int|null|string $id = null): ResponseInterface
    {
        return $this->fail(lang('RESTful.notImplemented', ['edit']), 501);
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @param  int|null|string  $id
     *
     * @return ResponseInterface
     */
    public function update(int|null|string $id = null): ResponseInterface
    {
        return $this->fail(lang('RESTful.notImplemented', ['update']), 501);
    }

    /**
     * Delete the designated resource object from the model
     * @param  int|string|null  $id
     * @return ResponseInterface
     */
    public function delete(int|null|string $id): ResponseInterface
    {
        return $this->fail(lang('RESTful.notImplemented', ['delete']), 501);
    }
}