<?php

namespace AvegaCms\Controllers\Api\Admin\Content;

use AvegaCms\Controllers\Api\AvegaCmsAPI;

class Pages extends AvegaCmsAPI
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