<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers\Api;

use AvegaCms\Controllers\AvegaCmsController;
use AvegaCms\Exceptions\AvegaCmsApiException;
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
        $this->apiData        = $this->getApiData();
    }

    /**
     * @return array|null
     */
    public function getApiData(): array|null
    {
        try {
            $request = request();
            if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
                if ($request->getBody() === null) {
                    throw AvegaCmsApiException::forNoData();
                }

                if ($request->getBody() !== 'php://input') {
                    json_decode($request->getBody(), false);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw AvegaCmsApiException::forInvalidJSON(json_last_error_msg());
                    }
                    return $request->getJSON(true);
                }
            }
        } catch (AvegaCmsApiException $e) {
            response()->setStatusCode(400, $e->getMessage())->send();
            exit();
        }

        return null;
    }

    /**
     * @return ResponseInterface
     */
    public function apiMethodNotFound(): ResponseInterface
    {
        return $this->failNotFound();
    }
}
