<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers\Api;

use AvegaCms\Config\Services;
use AvegaCms\Controllers\AvegaCmsController;
use AvegaCms\Traits\AvegaCmsApiResponseTrait;
use AvegaCms\Utilities\Cms;
use AvegaCms\Exceptions\AvegaCmsApiException;
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
        try {
            $request = Services::request();

            if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
                if ($request->getBody() === null) {
                    throw AvegaCmsApiException::forNoData();
                }

                json_decode($request->getBody(), false);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw AvegaCmsApiException::forInvalidJSON(json_last_error_msg());
                }

                if ($request->getBody() !== 'php://input' && empty($this->apiData = $request->getJSON(true))) {
                    throw AvegaCmsApiException::forUndefinedData();
                }
            }
        } catch (AvegaCmsApiException $e) {
            Services::response()->setStatusCode(400, $e->getMessage())->send();
            exit();
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
