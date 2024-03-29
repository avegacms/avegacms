<?php

namespace AvegaCms\Controllers\Api\Public;

use AvegaCms\Controllers\Api\CmsResourceController;
use AvegaCms\Enums\UserConditions;
use AvegaCms\Libraries\Authorization\Authorization;
use AvegaCms\Libraries\Authorization\Exceptions\AuthorizationException;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Utilities\Cms;
use Exception;
use ReflectionException;


class Login extends CmsResourceController
{
    protected array         $settings = [];
    protected Authorization $Authorization;

    /**
     * @throws AuthorizationException|ReflectionException
     */
    public function __construct()
    {
        helper(['date']);
        $this->settings      = Cms::settings('core');
        $this->Authorization = new Authorization($this->settings);
    }

    /**
     * @param  string|null  $action
     * @return ResponseInterface
     */
    public function index(?string $action = null): ResponseInterface
    {
        if (empty($data = $this->request->getJSON(true))) {
            return $this->failValidationErrors(lang('Authorization.errors.noData'));
        }

        try {
            $result = [];
            switch ($action) {
                case 'authorization':
                    $result = $this->_authProcess($this->Authorization->auth($data));
                    break;

                case 'check':
                    $result = $this->_authProcess($this->Authorization->checkCode($data));
                    break;

                case 'refresh':
                    $result = $this->Authorization->refresh($data);
                    break;

                case 'recovery':
                    $result = $this->_authProcess($this->Authorization->recovery($data));
                    break;

                case 'password':
                    $result = $this->Authorization->setPassword($data);
                    unset($result['userdata']);
                    break;

                case 'logout':
                    $this->Authorization->logout();
                    $result['data']['status'] = 'logout';
                    break;
                default:
                    throw AuthorizationException::forUnknownAuthType();
            }
            return $this->respondUpdated($result);
        } catch (AuthorizationException|Exception $e) {
            return match ($e->getCode()) {
                403     => $this->failForbidden($e->getMessage()),
                401     => $this->failUnauthorized($e->getMessage()),
                default => $this->failValidationErrors(class_basename($e) === 'AuthorizationException' ? $e->getMessages() : $e->getMessage())
            };
        }
    }

    public function logout():ResponseInterface
    {
        try {
            $this->Authorization->logout();
            return $this->respondNoContent();

        } catch (AuthorizationException|Exception $e) {
            return match ($e->getCode()) {
                403     => $this->failForbidden($e->getMessage()),
                401     => $this->failUnauthorized($e->getMessage()),
                default => $this->failValidationErrors(class_basename($e) === 'AuthorizationException' ? $e->getMessages() : $e->getMessage())
            };
        }
    }

    /**
     * @param  array  $auth
     * @return array|array[]
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
                $result = ['status' => 'authorized'];
                break;
            case 'send_code':
                if ( ! empty($auth['phone'] ?? '')) {
                    // Отправляем смс с кодом пользователю
                    Events::trigger($auth['condition'] === UserConditions::Auth->value ? 'sendAuthSms' : 'sendRecoverySms',
                        [
                            'user_id' => $auth['user_id'],
                            'phone'   => $auth['phone'],
                            'code'    => $auth['code']
                        ]);
                } elseif ( ! empty($auth['email'] ?? '')) {
                    // Отправляем email с кодом пользователю
                    Events::trigger($auth['condition'] === UserConditions::Auth->value ? 'sendAuthEmail' : 'sendRecoveryEmail',
                        [
                            'user_id' => $auth['user_id'],
                            'email'   => $auth['email'],
                            'code'    => $auth['code']
                        ]);
                } else {
                    throw AuthorizationException::forNoData();
                }

                $result['status'] = 'send_code';

                if (ENVIRONMENT !== 'production') {
                    $result['code'] = $auth['code'];
                }

                if ($auth['condition'] === UserConditions::Recovery->value) {
                    unset($auth['condition'], $auth['user_id']/*, $auth['code']*/);
                    $result         = $auth;
                    $result['code'] = $auth['code']; // TODO удалить
                }

                break;
            case 'password':
                $result['status']           = 'password';
                $result['hash'] = $auth['hash'];
                break;
            default:
                throw AuthorizationException::forNoData();
        }

        return $result;
    }
}