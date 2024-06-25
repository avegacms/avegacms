<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers\Api;

use AvegaCms\Config\Services;
use AvegaCms\Controllers\AvegaCmsController;
use AvegaCms\Traits\AvegaCmsApiResponseTrait;
use AvegaCms\Utilities\Cms;
use AvegaCms\Exceptions\AvegaCmsApiException;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;


class AvegaCmsAPI extends AvegaCmsController
{
    use AvegaCmsApiResponseTrait;

    protected object|null $userData       = null;
    protected object|null $userPermission = null;
    protected array|null  $apiData        = null;

    public function __construct()
    {
        helper(['date']);
        $this->userData       = Cms::userData();
        $this->userPermission = Cms::userPermission();
        $this->apiData        = $this->request->getJSON(true);
    }
    
    /**
     * @return ResponseInterface
     */
    public function apiMethodNotFound(): ResponseInterface
    {
        return $this->failNotFound();
    }
}
