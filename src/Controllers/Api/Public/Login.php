<?php

namespace AvegaCms\Controllers\Api\Public;

use AvegaCms\Controllers\Api\AvegaCmsAPI;
use AvegaCms\Enums\UserConditions;
use AvegaCms\Libraries\Authorization\Authorization;
use AvegaCms\Libraries\Authorization\Exceptions\AuthorizationException;
use AvegaCms\Utilities\Cms;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use ReflectionException;

class Login extends AvegaCmsAPI
{
    protected array $settings = [];
    protected Authorization $Authorization;

    /**
     * @throws AuthorizationException|ReflectionException
     */
    public function __construct()
    {
        parent::__construct();
        helper(['date']);
        $this->settings      = Cms::settings('core');
        $this->Authorization = new Authorization($this->settings);
    }

    public function index(?string $action = null): ResponseInterface
    {
        try {
            $result = [];

            switch ($action) {
                case 'authorization':
                    $result = $this->_authProcess($this->Authorization->auth($this->apiData));
                    break;

                case 'check':
                    $result = $this->_authProcess($this->Authorization->checkCode($this->apiData));
                    break;

                case 'refresh':
                    $result = $this->Authorization->refresh($this->apiData);
                    break;

                case 'recovery':
                    $result = $this->_authProcess($this->Authorization->recovery($this->apiData));
                    break;

                case 'password':
                    $result = $this->Authorization->setPassword($this->apiData);
                    unset($result['userdata']);
                    break;

                case 'logout':
                    $this->Authorization->logout();
                    $result['data']['status'] = 'logout';
                    break;

                default:
                    throw AuthorizationException::forUnknownAuthType();
            }

            return $this->cmsRespond($result);
        } catch (AuthorizationException|Exception $e) {
            return match ($e->getCode()) {
                403     => $this->failForbidden($e->getMessage()),
                401     => $this->failUnauthorized($e->getMessage()),
                default => $this->cmsRespondFail(class_basename($e) === 'AuthorizationException' ? $e->getMessages() : $e->getMessage())
            };
        }
    }

    public function logout(?string $slug = null): ResponseInterface
    {
        try {
            $slug ??= 'admin';

            $this->Authorization->logout(strtolower($slug));

            return $this->respondNoContent();
        } catch (AuthorizationException|Exception $e) {
            return match ($e->getCode()) {
                403     => $this->failForbidden($e->getMessage()),
                401     => $this->failUnauthorized($e->getMessage()),
                default => $this->cmsRespondFail(class_basename($e) === 'AuthorizationException' ? $e->getMessages() : $e->getMessage())
            };
        }
    }

    /**
     * @return array|list<array>
     *
     * @throws AuthorizationException|Exception
     */
    private function _authProcess(array $auth): array
    {
        $result = ['status' => 'unauthorized'];

        if ($auth['status'] === false) {
            throw AuthorizationException::forFailForbidden();
        }

        switch ($auth['direct']) {
            case 'set_user':
                Events::trigger('setAuthUserData', $auth['user_id']);
                $result = $this->Authorization->setUser($auth['user_id'], $auth['role']);
                break;

            case 'send_code':
                if (! empty($auth['phone'] ?? '')) {
                    // Отправляем смс с кодом пользователю
                    Events::trigger(
                        $auth['condition'] === UserConditions::Auth->value ? 'sendAuthSms' : 'sendRecoverySms',
                        [
                            'user_id' => $auth['user_id'],
                            'phone'   => $auth['phone'],
                            'code'    => $auth['code'],
                        ]
                    );
                } elseif (! empty($auth['email'] ?? '')) {
                    // Отправляем email с кодом пользователю
                    Events::trigger(
                        $auth['condition'] === UserConditions::Auth->value ? 'sendAuthEmail' : 'sendRecoveryEmail',
                        [
                            'user_id' => $auth['user_id'],
                            'email'   => $auth['email'],
                            'code'    => $auth['code'],
                        ]
                    );
                } else {
                    throw AuthorizationException::forNoData();
                }

                $result['status'] = 'send_code';

                if (ENVIRONMENT !== 'production') {
                    $result['code'] = $auth['code'];
                }

                if ($auth['condition'] === UserConditions::Recovery->value) {
                    unset($auth['condition'], $auth['user_id']/* , $auth['code'] */);
                    $result         = $auth;
                    $result['code'] = $auth['code']; // TODO удалить
                }

                break;

            case 'password':
                $result['status'] = 'password';
                $result['hash']   = $auth['hash'];
                break;

            default:
                throw AuthorizationException::forNoData();
        }

        return $result;
    }
}
