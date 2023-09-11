<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

use BaseController;

class AvegaCmsFrontendController extends BaseController
{
    public function __construct()
    {
        helper(['avegacms']);
    }

    public function index()
    {
        //
    }
}
