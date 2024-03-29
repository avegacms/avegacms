<?php

namespace AvegaCms\Controllers\Api;

use AvegaCms\Utilities\Cms;
use CodeIgniter\HTTP\ResponseInterface;

class AvegaCmsAPI extends CmsResourceController
{
    protected object|null $userData       = null;
    protected object|null $userPermission = null;

    public function __construct()
    {
        helper(['date']);
        $this->userData       = Cms::userData();
        $this->userPermission = Cms::userPermission();
    }

    /**
     * @return ResponseInterface
     */
    public function apiMethodNotFound(): ResponseInterface
    {
        return $this->failNotFound();
    }
}
