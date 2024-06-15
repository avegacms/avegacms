<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers\Api\Admin;

use AvegaCms\Config\Services;
use AvegaCms\Controllers\Api\CmsResourceController;
use AvegaCms\Traits\CmsResponseTrait;
use AvegaCms\Utilities\Cms;

class AvegaCmsAdminAPI extends CmsResourceController
{
    use CmsResponseTrait;

    protected object|null $userData       = null;
    protected object|null $userPermission = null;
    protected array|null  $apiData        = null;

    public function __construct()
    {
        helper(['date']);
        $this->userData       = Cms::userData();
        $this->userPermission = Cms::userPermission();
    }
}
