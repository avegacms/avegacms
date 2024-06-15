<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers\Api;

use AvegaCms\Config\Services;
use AvegaCms\Controllers\AvegaCmsController;
use AvegaCms\Traits\AvegaCmsApiResponseTrait;
use AvegaCms\Utilities\Cms;
use CodeIgniter\HTTP\ResponseInterface;

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
        $this->getApiData();
    }

    /**
     * @return void
     */
    protected function getApiData(): void
    {
        if (in_array($this->request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            if ($this->request->getBody() === null) {
                $this->response->setStatusCode(400, lang('Api.errors.noData'))->send();
                exit();
            }

            if ($this->request->getBody() !== 'php://input' && empty($this->apiData = $this->request->getJSON(true))) {
                $this->response->setStatusCode(400, lang('Api.errors.noData'))->send();
                exit();
            }
        }
    }

    /**
     * @return ResponseInterface
     */
    public function apiMethodNotFound(): ResponseInterface
    {
        return $this->failNotFound();
    }
}
