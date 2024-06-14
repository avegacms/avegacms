<?php

namespace AvegaCms\Controllers\Api\Admin;

use AvegaCms\Controllers\Api\CmsResourceController;
use AvegaCms\Utilities\Cms;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class AvegaCmsAdminAPI extends CmsResourceController
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
                $this->apiNoAdminData();
            }
        }
    }

    /**
     * @return ResponseInterface
     */
    public function apiNoAdminData(): ResponseInterface
    {
        return $this->failValidationErrors(lang('Api.errors.noData'));
    }

}
