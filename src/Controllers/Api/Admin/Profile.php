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
        $userId = $this->userData->userId;
        $role   = $this->userData->role;

        if (($profile = Auth::getProfile($userId, $role)) === null) {
            Auth::setProfile($userId, $role);
            $profile = Auth::getProfile($userId, $role);
        }
        return $this->cmsRespond($profile);
    }
}
