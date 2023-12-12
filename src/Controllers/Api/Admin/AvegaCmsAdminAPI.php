<?php

namespace AvegaCms\Controllers\Api\Admin;

use AvegaCms\Controllers\Api\CmsResourceController;
use AvegaCms\Utilities\Cms;

class AvegaCmsAdminAPI extends CmsResourceController
{
    protected object|null $userData       = null;
    protected object|null $userPermission = null;

    public function __construct()
    {
        helper(['date']);
        $this->userData       = Cms::userData();
        $this->userPermission = Cms::userPermission();
    }
}
