<?php

namespace AvegaCms\Controllers\Api\Admin\Content;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;

class Pages extends AvegaCmsAdminAPI
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $user = service('AvegaCmsUser');

        return $this->respond([
            'Content/Pages',
            $user::data(),
            $user::permission()
        ]);
    }
}