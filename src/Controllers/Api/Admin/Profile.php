<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers\Api\Admin;

use AvegaCms\Utilities\Auth;
use AvegaCms\Libraries\Authorization\Exceptions\AuthorizationException;
use CodeIgniter\HTTP\ResponseInterface;

class Profile extends AvegaCmsAdminAPI
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return ResponseInterface
     * @throws AuthorizationException
     */
    public function index(): ResponseInterface
    {
        if (($profile = Auth::getProfile($this->userData->userId, $this->userData->role)) === null) {
            Auth::setProfile($this->userData->userId, $this->userData->role);
            $profile = Auth::getProfile($this->userData->userId, $this->userData->role);
        }
        return $this->cmsRespond($profile);
    }
}
