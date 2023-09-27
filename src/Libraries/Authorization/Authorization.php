<?php

declare(strict_types=1);

namespace AvegaCms\Libraries\Authorization;

use AvegaCms\Enums\UserConditions;
use AvegaCms\Libraries\Authorization\Exceptions\{AuthorizationException, AuthenticationException};
use AvegaCms\Entities\{LoginEntity, UserEntity, UserTokensEntity};
use AvegaCms\Models\Admin\{LoginModel, UserAuthenticationModel, UserRolesModel, UserTokensModel};
use AvegaCms\Utils\Cms;
use CodeIgniter\Validation\ValidationInterface;
use CodeIgniter\Session\Session;
use CodeIgniter\Validation\Validation;
use Config\Services;
use Firebase\JWT\Key;
use Firebase\JWT\JWT;
use Exception;
use ReflectionException;

class Authorization
{
    protected array           $settings = [];
    protected LoginModel      $LM;
    protected UserTokensModel $UTM;

    protected UserRolesModel $URM;
    protected Session        $session;

    protected Validation $validation;

    /**
     * @param  array  $settings
     * @throws AuthorizationException
     */
    public function __construct(array $settings)
    {
        helper(['date']);

        if (empty($settings)) {
            throw AuthorizationException::forNoData();
        }

        $this->settings = $settings;
        $this->LM       = model(LoginModel::class);
        $this->UTM      = model(UserTokensModel::class);
        $this->URM      = model(UserRolesModel::class);

        $this->session    = Services::session();
        $this->validation = Services::validation();
    }

    /**
     * @param  array  $data
     * @return array
     * @throws AuthorizationException
     * @throws ReflectionException
     */
    public function auth(array $data): array
    {
        if (empty($data)) {
            throw AuthorizationException::forNoData();
        }

        if ( ! in_array($this->settings['auth']['loginType'] ?? '', $this->settings['auth']['loginTypeList'])) {
            throw AuthorizationException::forUnknownAuthType($this->settings['auth']['loginType']);
        }

        $loginType = $this->_checkType($data[$this->settings['auth']['loginType']]);

        if ( ! $this->validate($this->_validate('auth_by_' . $this->settings['auth']['loginType']), $data)) {
            throw new AuthorizationException($this->validation->getErrors());
        }

        $data = $this->validation->getValidated();

        if (($user = $this->LM->getUser($loginType)) === null) {
            throw AuthorizationException::forUnknownUser();
        }

        if (isset($data['password']) && ! password_verify($data['password'], $user->password)) {
            throw AuthorizationException::forWrongPassword();
        }

        $authResult = [
            'status'    => true,
            'direct'    => 'set_user',
            'userdata'  => ['user_id' => $user->id],
            'condition' => UserConditions::Auth->value
        ];

        $loginType = key($loginType);

        if ($this->settings['auth']['use2fa'] || $loginType === 'phone') {
            $authResult['userdata']['code'] = $this->setSecretCode($user->id, UserConditions::Auth->value);
            if ($loginType === 'phone' || $this->settings['auth']['2faField'] === 'phone') {
                $authResult['userdata']['phone'] = $user->phone;
            } elseif ($this->settings['auth']['2faField'] === 'email') {
                $authResult['userdata']['email'] = $user->email;
            } else {
                throw AuthorizationException::forFailSendAuthCode();
            }

            $authResult['direct'] = 'send_code';
        }

        return $authResult;
    }

    /**
     * @param  array  $data
     * @return array[]
     * @throws AuthorizationException|Exception
     */
    public function checkCode(array $data): array
    {
        if (empty($data)) {
            throw AuthorizationException::forNoData();
        }

        if ( ! $this->validate($this->_validate('check_code'), $data)) {
            throw new AuthorizationException($this->validation->getErrors());
        }

        $type = $this->_checkType($data['pointer']);

        $conditions = [
            'expires >' => 0,
            'secret !=' => '',
            ...$type
        ];

        if (($user = $this->LM->getUser($conditions)) === null) {
            throw AuthorizationException::forUnknownUser();
        }

        if ( ! in_array($data['condition'], [UserConditions::CheckPhone->value, UserConditions::CheckEmail->value])) {
            if ($user->expires < now($this->settings['env']['timezone'])) {
                throw AuthorizationException::forCodeExpired();
            }
        }

        if ($user->secret !== $this->_hashCode((int) $data['code'])) {
            throw AuthorizationException::forWrongCode();
        }

        if ($data['condition'] === UserConditions::Recovery->value) {
            $hash = $this->_hashCode($this->setSecretCode($user->id, UserConditions::Password->value));
        }

        return match ($data['condition']) {
            UserConditions::CheckPhone->value,
            UserConditions::CheckEmail->value => [
                'status'   => true,
                'direct'   => 'confirm',
                'userdata' => [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                    'phone'   => $user->phone
                ]
            ],
            UserConditions::Auth->value       => [
                'status'   => true,
                'direct'   => 'set_user',
                'userdata' => ['user_id' => $user->id]
            ],
            UserConditions::Recovery->value   => [
                'status'   => true,
                'direct'   => 'password',
                'userdata' => ['user_id' => $user->id, 'hash' => $hash ?? '']
            ],
            default                           => throw AuthorizationException::forWrongCode()
        };
    }

    /**
     * @param  int  $userId
     * @param  string  $userRole
     * @param  array  $userData
     * @return array[]
     * @throws ReflectionException|Exception
     */
    public function setUser(int $userId, string $userRole = '', array $userData = []): array
    {
        if (($user = $this->LM->getUser(['id' => $userId])) === null) {
            throw AuthorizationException::forUnknownUser();
        }

        unset($user->password, $user->secret, $user->expires, $user->reset);

        if (($role = $this->URM->getUserRoles($user->id, $userRole)->first()) === null) {
            throw AuthorizationException::forUnknownRole($userRole);
        }

        $userSession = [
            'isAuth'       => true,
            'sessionId'    => '',
            'accessToken'  => '',
            'refreshToken' => '',
            'redirect'     => '',
            'user'         => [

                'userId'   => $user->id,
                'timezone' => $user->timezone,
                'login'    => $user->login,
                'status'   => $user->status,
                'avatar'   => $user->avatar,
                'phone'    => $user->phone,
                'email'    => $user->email,
                'extra'    => $user->extra,
                'roleId'   => $role->role_id,
                'role'     => $role->role,
                ...$userData
            ]
        ];

        $request = Services::request();

        $userAgent = $request->getUserAgent()->getAgentString();
        $userIp    = $request->getIPAddress();

        if ($this->settings['auth']['useJwt']) {
            if (empty($token = $this->_signatureTokenJWT($userSession['user']))) {
                throw AuthorizationException::forCreateToken();
            }

            if (count(
                    $sessions = ($this->UTM->getUserTokens($user->id)->findColumn('id') ?? [])
                ) >= $this->settings['auth']['jwtSessionsLimit']) {
                $this->UTM->delete($sessions[0]);
            }

            $refreshTokenTime = now() + ($this->settings['auth']['jwtRefreshTime'] * DAY);

            $newUserSession = [
                'id'            => $userSession['sessionId'] = sha1($user->id . $userAgent . bin2hex(random_bytes(32))),
                'user_id'       => $user->id,
                'access_token'  => $userSession['accessToken'] = $token,
                'refresh_token' => $userSession['refreshToken'] = sha1(
                    $userSession['user']['phone'] .
                    $refreshTokenTime .
                    $this->settings['auth']['jwtSecretKey'] .
                    $userAgent
                ),
                'expires'       => $refreshTokenTime,
                'user_ip'       => $userIp,
                'user_agent'    => $userAgent
            ];

            if ( ! $this->UTM->insert((new UserTokensEntity($newUserSession)))) {
                throw AuthorizationException::forCreateToken();
            }
        }

        if ($this->settings['auth']['useSession']) {
            Cms::initClientSession();
            $this->_setClientSession($userSession);
        }

        $this->LM->save(
            (new UserEntity([
                'id'         => $user->id,
                'secret'     => '',
                'expires'    => 0,
                'condition'  => UserConditions::None->value,
                'last_ip'    => $userIp,
                'last_agent' => $userAgent,
                'active_at'  => now($user->timezone)
            ]))
        );

        return ['data' => $userSession];
    }

    /**
     * @param  array  $data
     * @return array
     * @throws AuthorizationException|Exception
     */
    public function recovery(array $data): array
    {
        if ($this->settings['auth']['useRecovery'] === false) {
            throw AuthorizationException::forFailForbidden();
        }

        if (empty($data)) {
            throw AuthorizationException::forNoData();
        }

        if ( ! $this->validate($this->_validate('recovery'), $data)) {
            throw new AuthorizationException($this->validation->getErrors());
        }

        $field = $this->settings['auth']['recoveryField'];

        if (($user = $this->LM->getUser([$field => $data['recovery_field']])) === null) {
            throw AuthorizationException::forUnknownUser();
        }

        $code = $this->setSecretCode($user->id, UserConditions::Recovery->value);

        $recoveryResult = [
            'status'   => true,
            'direct'   => 'send_code',
            'userdata' => [
                'user_id'   => $user->id,
                'code'      => $code,
                'condition' => UserConditions::Recovery->value,
                'hash'      => $this->_hashCode($code)
            ],
        ];

        match ($field) {
            'phone' => ($recoveryResult['userdata']['phone'] = $user->phone),
            'email',
            'login' => ($recoveryResult['userdata']['email'] = $user->email),
            default => throw AuthorizationException::forFailSendAuthCode()
        };

        return $recoveryResult;
    }

    /**
     * @param  array  $data
     * @return array
     * @throws ReflectionException|Exception
     */
    public function setPassword(array $data): array
    {
        if ($this->settings['auth']['useRecovery'] === false) {
            throw AuthorizationException::forFailForbidden();
        }

        if (empty($data)) {
            throw AuthorizationException::forNoData();
        }

        if ( ! $this->validate($this->_validate('password'), $data)) {
            throw new AuthorizationException($this->validation->getErrors());
        }

        $conditions = [
            'expires >' => 0,
            'secret'    => $data['hash'],
            'condition' => 'recovery'
        ];

        if (($user = $this->LM->getUser($conditions)) === null) {
            throw AuthorizationException::forUnknownUser();
        }

        if ($user->expires < now($this->settings['env']['timezone'])) {
            throw AuthorizationException::forCodeExpired();
        }

        $request = Services::request();

        $update = $this->LM->save(
            (new LoginEntity([
                'id'         => $user->id,
                'secret'     => '',
                'expires'    => 0,
                'password'   => $data['password'],
                'condition'  => UserConditions::None->value,
                'last_ip'    => $request->getIPAddress(),
                'last_agent' => $request->getUserAgent()->getAgentString(),
                'active_at'  => now($user->timezone)
            ]))
        );

        if ($update === false) {
            throw AuthorizationException::forFailPasswordUpdate();
        }

        if ($this->settings['auth']['useJwt']) {
            // Удаляем все записи токенов по пользователю
            $this->UTM->delete(['user_id' => $user->id]);
        }

        return [
            'status'   => true,
            'direct'   => 'updated',
            'userdata' => [
                'user_id' => $user->id
            ]
        ];
    }

    /**
     * @param  array  $data
     * @return array
     * @throws ReflectionException
     * @throws Exception
     */
    public function refresh(array $data): array
    {
        $request = Services::request();

        if (empty($authHeader = explode(' ', $request->getServer('HTTP_AUTHORIZATION') ?? '')) || count(
                $authHeader
            ) !== 2) {
            throw AuthorizationException::forFailUnauthorized();
        }

        if ($this->settings['auth']['useJwt'] || $authHeader[0] !== 'Bearer' || count($token = explode('.',
                $authHeader[1])) !== 3) {
            throw AuthorizationException::forFailUnauthorized();
        }

        if (($payload = JWT::jsonDecode(JWT::urlsafeB64Decode($token[1]))) === null) {
            throw AuthorizationException::forFailUnauthorized();
        }

        if (empty($data)) {
            throw AuthorizationException::forNoData();
        }

        if ( ! $this->validate($this->_validate('refresh_token'), $data)) {
            throw new AuthorizationException($this->validation->getErrors());
        }

        if (empty($tokens = $this->UTM->getUserTokens($payload->data->userId)->findAll())) {
            throw AuthorizationException::forFailUnauthorized();
        }

        foreach ($tokens as $item) {
            if (hash_equals($item->refresh_token, $data['token'])) {
                if ($item->expires < now()) {
                    throw AuthorizationException::forFailUnauthorized('expiresToken');
                }

                if (empty($jwt = $this->_signatureTokenJWT((array) $payload->data))) {
                    throw AuthorizationException::forCreateToken();
                }

                $updated = $this->UTM->save(
                    (new UserTokensEntity(
                        [
                            'id'           => $item->id,
                            'access_token' => $jwt,
                            'user_ip'      => $request->getIPAddress(),
                            'user_agent'   => $request->getUserAgent()->getAgentString()
                        ]
                    ))
                );

                if ($updated) {
                    return ['data' => ['access_token' => $jwt]];
                }

                break;
            }
        }

        throw AuthorizationException::forFailUnauthorized('tokenNotFound');
    }

    /**
     * @return void
     */
    public function logout(): void
    {
        $this->session->push('avegacms.admin', []);
    }

    /**
     * @return void
     * @throws AuthenticationException|Exception
     */
    public function checkUserAccess(): void
    {
        $request  = Services::request();
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

                if ($this->session->has('avegacms') === false) {
                    throw AuthenticationException::forUserSessionNotExist();
                }

                if ($this->session->get('avegacms.admin.isAuth') !== true) {
                    throw AuthenticationException::forNotAuthorized();
                }

                $userData = Cms::arrayToObject($this->session->get('avegacms.admin'));

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

        if (is_null($map = cache($fileCacheName = 'RAM_' . $userData->user->role))) {
            if (($map = $UAM->getRoleAccessMap($userData->user->roleId)) === null) {
                throw AuthenticationException::forAccessDenied();
            }
            cache()->save($fileCacheName, $map, DAY * 30);
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

        Cms::setUser('user', $userData->user);
        Cms::setUser('permission', Cms::arrayToObject([
            'self'      => $permission->self,
            'moderated' => $permission->moderated,
            'settings'  => $permission->settings
        ]));
    }

    /**
     * @param  int  $userId
     * @param  string  $condition
     * @return int
     * @throws ReflectionException|Exception
     */
    public function setSecretCode(int $userId, string $condition): int
    {
        $code = $this->_getCode();

        $this->LM->save(
            (new LoginEntity(
                [
                    'id'        => $userId,
                    'secret'    => $this->_hashCode($code),
                    'expires'   => $this->_setExpiresTime($condition),
                    'condition' => UserConditions::from($condition)->value
                ]
            ))
        );

        return $code;
    }

    /**
     * @param  array  $map
     * @param  array  $segments
     * @param  int  $index
     * @param  int  $parent
     * @return mixed
     * @throws AuthenticationException
     */
    private function _findPermission(array $map, array $segments, int $index = 0, int $parent = 0): mixed
    {
        if ($index >= count($segments)) {
            return null;
        }

        foreach ($map as $actions) {
            if ($actions['slug'] === $segments[$index] && $actions['parent'] == $parent) {
                if ($actions['access'] == 0) {
                    throw AuthenticationException::forForbiddenAccess();
                }

                if (isset($segments[$index + 1])) {
                    return $this->_findPermission($map, $segments, $index + 1, $actions['module_id']);
                }
                return $actions;
            }
        }

        return null;
    }

    protected function validate(array $rules, array $data): bool
    {
        return $this->validation->setRules($rules)->run($data);
    }

    /**
     * @return int
     * @throws Exception
     */
    private function _getCode(): int
    {
        return random_int(
            1000,
            (10 ** $this->settings['auth']['verifyCodeLength']) - 1
        );
    }

    /**
     * @param  int  $code
     * @return string
     */
    private function _hashCode(int $code): string
    {
        return sha1($code . $this->settings['env']['secretKey']);
    }

    /**
     * @param  string  $condition
     * @return int
     * @throws Exception
     */
    private function _setExpiresTime(string $condition): int
    {
        return match (strtolower($condition)) {
                'auth',
                'check_phone',
                'check_email' => $this->settings['auth']['verifyCodeTime'],
                'recovery',
                'password'    => $this->settings['auth']['recoveryCodeTime'],
            } * (now($this->settings['env']['timezone']) * MINUTE);
    }

    /**
     * @param  array  $userData
     * @return string
     */
    public function _signatureTokenJWT(array $userData): string
    {
        $issuedAtTime    = time();
        $tokenExpiration = $issuedAtTime + ($this->settings['auth']['jwtLiveTime'] * MINUTE);

        return JWT::encode(
            [
                'iss'  => base_url(),
                'aud'  => 'API',
                'sub'  => 'AvegaCMS API',
                'nbf'  => $issuedAtTime,
                'iat'  => $issuedAtTime, // Время выпуска JWT
                'exp'  => $tokenExpiration,
                'data' => $userData
            ],
            $this->settings['auth']['jwtSecretKey'],
            $this->settings['auth']['jwtAlg']
        );
    }

    /**
     * @param  string  $field
     * @return string[]
     * @throws AuthorizationException
     */
    private function _checkType(string $field): array
    {
        if (preg_match('/^79\d{9}$/', $field)) {
            return ['phone' => $field];
        }

        if (filter_var($field, FILTER_VALIDATE_EMAIL)) {
            return ['email' => $field];
        }

        if (preg_match('/^[a-zA-Z0-9_-]+$/', $field)) {
            return ['login' => $field];
        }

        throw AuthorizationException::forUnknownLoginField($field);
    }

    /**
     * @param  array  $userdata
     * @return void
     * @throws AuthorizationException
     */
    private function _setClientSession(array $userdata = []): void
    {
        if ($this->session->has('avegacms') === false) {
            throw AuthorizationException::forUserSessionNotExist();
        }
        $session = $this->session->get('avegacms');

        $session['admin'] = $userdata;

        $this->session->set('avegacms', $session);
    }

    /**
     * @param  string  $type
     * @return array[]
     * @throws AuthorizationException
     */
    private function _validate(string $type): array
    {
        $phone         = 'exact_length[11]|regex_match[/^79\d{9}/]';
        $login         = 'required|max_length[36]';
        $email         = 'max_length[255]|valid_email';
        $password      = 'required|verify_password';
        $condition     = 'required|in_list[' . implode(',', UserConditions::getValues()) . ']';
        $code          = 'required|numeric|exact_length[' . $this->settings['auth']['verifyCodeLength'] . ']';
        $token         = 'required|max_length[255]|alpha_numeric';
        $recoveryField = $this->settings['auth']['recoveryField'];

        return match ($type) {
            'auth_by_login' => [
                'login'    => [
                    'label'  => lang('Authorization.fields.login'),
                    'rules'  => $login . '|is_not_unique[users.login]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique')
                    ]
                ],
                'password' => [
                    'label' => lang('Authorization.fields.password'),
                    'rules' => $password
                ]
            ],
            'auth_by_email' => [
                'email'    => [
                    'label'  => lang('Authorization.fields.email'),
                    'rules'  => 'required|' . $email . '|is_not_unique[users.email]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique')
                    ]
                ],
                'password' => [
                    'label' => lang('Authorization.fields.password'),
                    'rules' => $password
                ]
            ],
            'auth_by_phone' => [
                'phone' => [
                    'label'  => lang('Authorization.fields.phone'),
                    'rules'  => 'required|' . $phone . '|is_not_unique[users.phone]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique')
                    ]
                ]
            ],
            'check_code'    => [
                'condition' => [
                    'label' => lang('Authorization.fields.condition'),
                    'rules' => $condition,
                ],
                'pointer'   => [
                    'label' => lang('Authorization.fields.pointer'),
                    'rules' => 'required|max_length[255]'
                ],
                'code'      => [
                    'label' => lang('Authorization.fields.code'),
                    'rules' => $code
                ]
            ],
            'recovery'      => [
                'recovery_field' => [
                    'label' => lang('Authorization.fields.' . $recoveryField),
                    'rules' => 'required|' . (($recoveryField == 'login') ? $login : $email)
                ]
            ],
            'password'      => [
                'password' => [
                    'label' => lang('Authorization.fields.password'),
                    'rules' => $password
                ],
                'passconf' => [
                    'label' => lang('Authorization.fields.passconf'),
                    'rules' => 'required|matches[password]'
                ],
                'hash'     => [
                    'label' => lang('Authorization.fields.hash'),
                    'rules' => 'required|max_length[255]|alpha_numeric'
                ]
            ],
            'refresh_token' => [
                'token' => [
                    'label'  => lang('Authorization.fields.token'),
                    'rules'  => $token,
                    'errors' => [
                        'max_length'    => lang('Authorization.errors.wrongToken'),
                        'alpha_numeric' => lang('Authorization.errors.wrongToken'),
                    ]
                ]
            ],
            default         => throw AuthorizationException::forRulesNotFound()
        };
    }
}
