<?php

namespace AvegaCms\Controllers\Api\Admin;

use CodeIgniter\RESTful\ResourceController;
use AvegaCms\Libraries\Authentication\AvegaCmsUser;

class AvegaCmsAdminAPI extends ResourceController
{
    protected $userData       = null;
    protected $userPermission = null;

    public function __construct()
    {
        helper(['avegacms', 'date']);
        $this->userData = AvegaCmsUser::data();
        $this->userPermission = AvegaCmsUser::permission();
    }
}
