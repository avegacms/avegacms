<?php

namespace AvegaCms\Controllers\Api\Admin;

use AvegaCms\Controllers\Api\CmsResourceController;
use AvegaCms\Libraries\Authorization\AvegaCmsUser;

class AvegaCmsAdminAPI extends CmsResourceController
{
    protected object|null $userData       = null;
    protected object|null $userPermission = null;

    public function __construct()
    {
        helper(['avegacms', 'date']);
        $this->userData = AvegaCmsUser::data();
        $this->userPermission = AvegaCmsUser::permission();
    }
}
