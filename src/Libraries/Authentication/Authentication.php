<?php

declare(strict_types=1);

namespace AvegaCms\Libraries\Authentication;

use AvegaCms\Libraries\Authentication\Exceptions\AuthenticationException;
use Config\Services;
use AvegaCms\Models\Admin\{UserAuthenticationModel, UserTokensModel};
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Authentication
{
    protected array $settings = [];

    /**
     * @param  array  $settings
     * @throws AuthenticationException|Exception
     */
    public function __construct(array $settings)
    {
        if (empty($settings)) {
            throw AuthenticationException::forNoSettings();
        }

        helper(['avegacms']);

        $this->settings = $settings;
    }

    /**
     * @return void
     * @throws AuthenticationException|Exception
     */
    public function checkUserAccess(): void
    {
        $session = Services::session();
        $request = Services::request();
        $userData = null;

        $UTM = model(UserTokensModel::class);
        $UAM = model(UserAuthenticationModel::class);

        if ($this->settings['useWhiteIpList'] && ! empty($this->settings['whiteIpList']) && in_array(
                $request->getIPAddress(),
                $this->settings['whiteIpList']
            )) {
            throw AuthenticationException::forAccessDenied();
        }

        if (empty($authHeader = $request->getServer('HTTP_AUTHORIZATION') ?? '')) {
            throw AuthenticationException::forNoHeaderAuthorize();
        }

        $authHeader = explode(' ', $authHeader);

        $authType = match ($authHeader[0]) {
            'Token'  => ($this->settings['useToken']) ? ['type' => 'token', 'token' => $authHeader[1]] : false,
            'Bearer' => (strtolower($authHeader[1]) === 'session' && $this->settings['useSession']) ?
                ['type' => 'session'] :
                (
                $this->settings['useJwt'] ?
                    [
                        'type' => 'jwt', 'token' => $authHeader[1]
                    ] : false
                ),
            default  => false
        };

        if ($authType === false) {
            throw AuthenticationException::forAccessDenied();
        }

        switch ($authType['type']) {
            case 'session':

                if ($session->has('avegacms') === false) {
                    throw AuthenticationException::forUserSessionNotExist();
                }

                if ($session->get('avegacms.admin.isAuth') !== true) {
                    throw AuthenticationException::forNotAuthorized();
                }

                $userData = arrayToObject($session->get('avegacms.admin'));

                break;
            case 'jwt':

                $payload = JWT::decode(
                    $authType['token'],
                    new Key(
                        $this->settings['auth']['jwtSecretKey'],
                        $this->settings['auth']['jwtAlg']
                    )
                );

                if (empty($tokens = $UTM->getUserTokens($payload->data->userId)->findAll())) {
                    throw AuthenticationException::forNotAuthorized();
                }

                foreach ($tokens as $item) {
                    if (hash_equals($item->access_token, $authType['token'])) {
                        if ($item->expires < now()) {
                            throw AuthenticationException::forExpiredToken();
                        }
                        $userData = $payload->data;
                        break;
                    }
                }
                throw AuthenticationException::forTokenNotFound();
            case 'token':
                // TODO реализовать в дальнейшем
                throw AuthenticationException::forTokenNotFound();
        }

        if (empty($segments = array_slice(array_slice($request->uri->getSegments(), 2), 0, 2))) {
            throw AuthenticationException::forUnknownPermission();
        }

        if (($map = $UAM->getRoleAccessMap($userData->user->roleId)->findAll()) === null) {
            throw AuthenticationException::forAccessDenied();
        }

        if (($permission = $this->_findPermission($map, $segments)) === null) {
            throw AuthenticationException::forForbiddenAccess();
        }

        $method = $request->getMethod();

        $action = match ($method) {
            'get'    => $permission->read,
            'post'   => $permission->create,
            'put',
            'patch'  => $permission->update,
            'delete' => $permission->delete,
            default  => throw AuthenticationException::forForbiddenAccess()
        };

        if ($action === false) {
            throw AuthenticationException::forForbiddenAccess();
        }

        $cmsUser = service('AvegaCmsUser');

        $cmsUser::set('user', $userData->user);
        $cmsUser::set('permission', arrayToObject([
            'self'      => $permission->self,
            'moderated' => $permission->moderated,
            'settings'  => $permission->settings
        ]));
    }

    /**
     * @param $map
     * @param  array  $segments
     * @param  int  $index
     * @param  int  $parent
     * @return mixed
     * @throws AuthenticationException
     */
    private function _findPermission($map, array $segments, int $index = 0, int $parent = 0): mixed
    {
        if ($index >= count($segments)) {
            return null;
        }

        foreach ($map as $actions) {
            if ($actions->slug === $segments[$index] && $actions->parent === $parent) {
                if ($actions->access === false) {
                    throw AuthenticationException::forForbiddenAccess();
                }

                if (isset($segments[$index + 1])) {
                    return $this->_findPermission($map, $segments, $index + 1, $actions->module_id);
                }
                return $actions;
            }
        }

        return null;
    }
}