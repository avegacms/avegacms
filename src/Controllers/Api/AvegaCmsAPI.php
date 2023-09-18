<?php

namespace AvegaCms\Controllers\Api;

use AvegaCms\Libraries\Authorization\AvegaCmsUser;

class AvegaCmsAPI extends CmsResourceController
{
    protected object|null $userData       = null;
    protected object|null $userPermission = null;

    public function __construct()
    {
        helper(['date']);
        $this->userData = AvegaCmsUser::data();
        $this->userPermission = AvegaCmsUser::permission();
    }
}
