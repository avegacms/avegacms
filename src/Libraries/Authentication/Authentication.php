<?php

namespace AvegaCms\Libraries\Authentication;

use AvegaCms\Entities\UserTokensEntity;
use AvegaCms\Libraries\Authentication\Exceptions\AuthenticationException;
use AvegaCms\Libraries\Authorization\Exceptions\AuthorizationException;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;
use Config\Services;
use CodeIgniter\Session\Session;
use AvegaCms\Models\Admin\{UserAuthenticationModel, UserTokensModel};
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Authentication
{
    protected array $settings = [];

    protected Session $session;

    protected Request  $request;
    protected Response $response;

    protected UserAuthenticationModel $UAM;
    protected UserTokensModel         $UTM;

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
        $this->session = Services::session();
        $this->request = Services::request();
        $this->response = Services::response();

        if ($this->settings['useWhiteIpList'] && ! empty($this->settings['whiteIpList']) && in_array(
                $this->request->getIPAddress(),
                $this->settings['whiteIpList']
            )) {
            throw AuthenticationException::forAccessDenied();
        }

        $this->UAM = model(UserAuthenticationModel::class);
        $this->UTM = model(UserTokensModel::class);
    }

    /**
     * @return boolean
     * @throws AuthenticationException|AuthorizationException|Exception
     */
    public function checkUserAuth(): bool
    {
        if (empty($authHeader = $this->request->getServer('HTTP_AUTHORIZATION'))) {
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
                if ($this->session->has('avegacms') === false) {
                    throw AuthenticationException::forAccessDenied();
                }

                if ($this->session->get('avegacms.admin.isAuth') ?? false) {
                    throw AuthenticationException::forNotAuthorized();
                }

                return true;
            case 'jwt':

                $payload = JWT::decode(
                    $authType['token'],
                    new Key(
                        $this->settings['auth']['jwtSecretKey'],
                        $this->settings['auth']['jwtAlg']
                    )
                );

                if (empty($tokens = $this->UTM->getUserTokens($payload->data->userId)->findAll())) {
                    throw AuthorizationException::forFailUnauthorized();
                }

                foreach ($tokens as $item) {
                    if (hash_equals($item->refresh_token, $authType['token'])) {
                        if ($item->expires < now()) {
                            throw AuthorizationException::forFailUnauthorized('expiresToken');
                        }
                        return true;
                    }
                }

                throw AuthorizationException::forFailUnauthorized('tokenNotFound');
            case 'token':
                return false;
        }

        return false;
    }

    public function checkUserAccess(): array
    {
        return [];
    }
}