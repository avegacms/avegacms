<@php

<?php
if ($strict): ?>
declare(strict_types=1);
<?php
endif; ?>

namespace {namespace};

use {useStatement};
<?php
if ($type === 'api'): ?>
use CodeIgniter\HTTP\ResponseInterface;
<?php
endif; ?>

class {class} extends {extends}
{
    public function __construct()
    {
        <?php if ($type !== 'api'): ?>
        parent::__construct();
        <?php endif;?>
    }
<?php
if ($type === 'api'): ?>
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
<?php
else: ?>
    public function index()
    {
    //
    }
<?php
endif ?>
}
