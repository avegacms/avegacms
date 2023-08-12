<?php

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

        $this->settings = $settings;
    }

    /**
     * @return boolean
     * @throws AuthenticationException|Exception
     */
    public function checkUserAccess(): bool
    {
        $session = Services::session();
        $request = Services::request();
        $response = Services::response();

        $UTM = model(UserTokensModel::class);
        $UAM = model(UserAuthenticationModel::class);

        if ($this->settings['useWhiteIpList'] && ! empty($this->settings['whiteIpList']) && in_array(
                $request->getIPAddress(),
                $this->settings['whiteIpList']
            )) {
            throw AuthenticationException::forAccessDenied();
        }

        if (empty($authHeader = $request->getServer('HTTP_AUTHORIZATION'))) {
            throw AuthenticationException::forNoHeaderAuthorize();
        }

        $authHeader = explode(' ', $authHeader);
        $cont = count($authHeader);

        $authType = match ($authHeader[0]) {
            'Session' => ($cont === 1 && $this->settings['useSession']) ? ['type' => 'session'] : false,
            'Token'   => ($cont === 2 && $this->settings['useToken']) ? [
                'type' => 'token', 'token' => $authHeader[1]
            ] : false,
            'Bearer'  => ($cont === 2 && $this->settings['useJwt']) ? [
                'type' => 'jwt', 'token' => $authHeader[1]
            ] : false,
            default   => false
        };

        if ($authType === false) {
            throw AuthenticationException::forAccessDenied();
        }

        switch ($authType['type']) {
            case 'session':
                if ($session->has('avegacms') === false) {
                    throw AuthenticationException::forAccessDenied();
                }

                if ($session->get('avegacms.admin.isAuth') ?? false) {
                    throw AuthenticationException::forNotAuthorized();
                }

                $userData = (object) $session->get('avegacms.admin');

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

        if (empty($roleActionMap = $UAM->where(['role_id' => $userData->user->roleId])->findAll())) {
            throw AuthenticationException::forAccessDenied();
        }

        return false;
    }
}