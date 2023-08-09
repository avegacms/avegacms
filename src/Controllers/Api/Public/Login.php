<?php

namespace AvegaCms\Controllers\Api\Public;

use AvegaCms\Controllers\Api\AvegaCmsAPI;
use AvegaCms\Libraries\Authorization\Authorization;
use AvegaCms\Libraries\Authorization\Exceptions\{AuthorizationExceptions, ValidationException};
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;


class Login extends AvegaCmsAPI
{
    protected array         $settings = [];
    protected Authorization $Authorization;

    public function __construct()
    {
        parent::__construct();
        $this->settings = settings('core');
        $this->Authorization = new Authorization($this->settings);
    }

    /**
     * @param string|null $action
     * @return ResponseInterface
     * @throws AuthorizationExceptions|ValidationException|Exception
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

                case 'registration':
                    break;
                default:
                    throw AuthorizationExceptions::forUnknownAuthType();
            }
            return $this->respondUpdated($result);
        } catch (ValidationException $e) {
            return $this->failValidationErrors($e->getMessages());
        } catch (AuthorizationExceptions|Exception $e) {
            return match ($e->getCode()) {
                403     => $this->failForbidden($e->getMessage()),
                401     => $this->failUnauthorized($e->getMessage()),
                default => $this->failValidationErrors($e->getMessage()),
            };
        }
    }

    /**
     * @param array $auth
     * @return array|array[]
     * @throws AuthorizationExceptions|Exception
     */
    private function _authProcess(array $auth): array
    {
        $result = ['data' => ['status' => 'unauthorized']];

        if ($auth['status'] === false) {
            throw AuthorizationExceptions::forFailForbidden();
        }

        switch ($auth['direct']) {
            case 'set_user':
                Events::trigger('setAuthUserData', $auth['userdata']['user_id']);
                $user = $this->Authorization->setUser($auth['userdata']['user_id']);
                $result = ['data' => ['status' => 'authorized', 'userdata' => $user]];
                break;
            case 'send_code':
                if (!empty($auth['userdata']['phone'] ?? '')) {
                    // Отправляем смс с кодом пользователю
                    Events::trigger($auth['userdata']['condition'] === 'auth' ? 'sendAuthSms' : 'sendRecoverySms', [
                        'user_id' => $auth['userdata']['user_id'],
                        'phone'   => $auth['userdata']['phone'],
                        'code'    => $auth['userdata']['code']
                    ]);
                } elseif (!empty($auth['userdata']['email'] ?? '')) {
                    // Отправляем email с кодом пользователю
                    Events::trigger($auth['userdata']['condition'] === 'auth' ? 'sendAuthEmail' : 'sendRecoveryEmail', [
                        'user_id' => $auth['userdata']['user_id'],
                        'email'   => $auth['userdata']['email'],
                        'code'    => $auth['userdata']['code']
                    ]);
                } else {
                    throw AuthorizationExceptions::forNoData();
                }
                $result['data']['status'] = 'send_code';

                if ($auth['userdata']['condition'] === 'recovery') {
                    unset($auth['userdata']['condition'], $auth['userdata']['user_id']/*, $auth['userdata']['code']*/);
                    $result['data']['userdata'] = $auth['userdata'];
                    $result['data']['userdata']['code'] = $auth['userdata']['code']; // TODO удалить
                }

                break;
            case 'password':
                $result['data']['status'] = 'password';
                $result['data']['userdata']['hash'] = $auth['userdata']['hash'];
                break;
            default:
                throw AuthorizationExceptions::forNoData();
        }

        return $result;
    }
}