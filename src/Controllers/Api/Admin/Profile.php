<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers\Api\Admin;

use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class Profile extends AvegaCmsAdminAPI
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
        dd('!!!');
    }
}
