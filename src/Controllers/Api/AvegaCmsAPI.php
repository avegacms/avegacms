<?php

namespace AvegaCms\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

class AvegaCmsAPI extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        helper(['avegacms', 'date']);
    }
}
