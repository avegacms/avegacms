<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers\Api;

use AvegaCms\Utilities\Cms;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class AvegaCmsAPI extends CmsResourceController
{
    protected object|null                        $userData       = null;
    protected object|null                        $userPermission = null;
    protected array|bool|float|int|stdClass|null $apiData        = null;

    public function __construct()
    {
        helper(['date']);
        $this->userData       = Cms::userData();
        $this->userPermission = Cms::userPermission();

        $request = Request();

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            if (($this->apiData = $request->getJSON(true)) === null) {
                $this->apiNoData();
            }
        }
    }

    /**
     * @return ResponseInterface
     */
    public function apiNoData(): ResponseInterface
    {
        return $this->failValidationErrors(lang('Api.errors.noData'));
    }

    /**
     * @return ResponseInterface
     */
    public function apiMethodNotFound(): ResponseInterface
    {
        return $this->failNotFound();
    }
}
