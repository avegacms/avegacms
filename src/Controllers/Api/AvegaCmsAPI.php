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
        $request = Services::request();
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            if ($request->getBody() === null) {
                Services::response()->setStatusCode(400, lang('Api.errors.noData'))->send();
                exit();
            }

            json_decode($request->getBody(), false, 512, 0);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Services::response()->setStatusCode(400,
                    lang('Api.errors.invalidJSON', [json_last_error_msg()]))->send();
                exit();
            }

            if ($request->getBody() !== 'php://input' && empty($this->apiData = $request->getJSON(true))) {
                Services::response()->setStatusCode(400, lang('Api.errors.noData'))->send();
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
