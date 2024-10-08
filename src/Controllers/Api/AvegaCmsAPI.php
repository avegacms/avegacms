<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api;

use AvegaCms\Controllers\AvegaCmsController;
use AvegaCms\Exceptions\AvegaCmsException;
use AvegaCms\Traits\AvegaCmsApiResponseTrait;
use AvegaCms\Utilities\Cms;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class AvegaCmsAPI extends AvegaCmsController
{
    use AvegaCmsApiResponseTrait;

    protected ?object $userData       = null;
    protected ?object $userPermission = null;
    protected ?array $apiData         = null;

    public function __construct()
    {
        helper(['date']);
        $this->userData       = Cms::userData();
        $this->userPermission = Cms::userPermission();
        $this->apiData        = $this->getApiData();
    }

    public function getApiData(): ?array
    {
        try {
            $request = request();

            switch ($request->getMethod()) {
                case 'POST':
                case 'PUT':
                    if ($request->getBody() === null) {
                        throw AvegaCmsException::forNoData();
                    }

                    if ($request->getBody() !== 'php://input') {
                        json_decode($request->getBody(), false);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw AvegaCmsException::forInvalidJSON(json_last_error_msg());
                        }

                        return $request->getJSON(true);
                    }
                    break;

                case 'PATCH':
                    if ($request->getBody() !== null) {
                        return $request->getJSON(true);
                    }
                    break;
            }
        } catch (AvegaCmsException $e) {
            response()->setStatusCode(400, $e->getMessage())->send();

            exit();
        }

        return null;
    }

    public function apiMethodNotFound(): ResponseInterface
    {
        return $this->failNotFound();
    }

    public function cmsException(Throwable $exception): ResponseInterface
    {
        return $this->cmsRespondFail(
            $exception instanceof AvegaCmsException ? $exception->getMessages() : $exception->getMessage()
        );
    }
}
