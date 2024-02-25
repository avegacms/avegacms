<?php

declare(strict_types = 1);

namespace AvegaCms\Libraries\Authorization;

use AvegaCms\Enums\UserConditions;
use AvegaCms\Libraries\Authorization\Exceptions\{AuthorizationException, AuthenticationException};
use AvegaCms\Entities\{LoginEntity, UserEntity, UserTokensEntity};
use AvegaCms\Models\Admin\{LoginModel, RolesModel, UserAuthenticationModel, UserRolesModel, UserTokensModel};
use AvegaCms\Utilities\Cms;
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
    protected RolesModel      $RM;
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

        $this->settings   = $settings;
        $this->LM         = model(LoginModel::class);
        $this->RM         = model(RolesModel::class);
        $this->UTM        = model(UserTokensModel::class);
        $this->URM        = model(UserRolesModel::class);
        $this->session    = Services::session();
        $this->validation = Services::validation();
    }

    /**
     * @param  array  $data
     * @param  string|null  $role
     * @return array
     * @throws AuthorizationException
     * @throws ReflectionException
     */
    public function auth(array $data, ?string $role = null): array
    {
        if (empty($data)) {
            throw AuthorizationException::forNoData();
        }

        if ( ! in_array($this->settings['auth']['loginType'] ?? '', $this->settings['auth']['loginTypeList'])) {
            throw AuthorizationException::forUnknownAuthType($this->settings['auth']['loginType']);
        }

        $loginType = $this->_checkType($data);

        if ( ! $this->validate($this->_validate('auth_by_' . array_keys($loginType)[0]), $data)) {
            throw new AuthorizationException($this->validation->getErrors());
        }

        $data = $this->validation->getValidated();

        if (($user = $this->LM->getUser($loginType, $role)) === null) {
            throw AuthorizationException::forUnknownUser();
        }

        if (isset($data['password']) && ! password_verify($data['password'], $user->password)) {
            throw AuthorizationException::forWrongPassword();
        }

        $authResult = [
            'status'    => true,
            'direct'    => 'set_user',
            'user_id'   => $user->id,
            'condition' => UserConditions::Auth->value
        ];

        $loginType = key($loginType);

        if ($this->settings['auth']['use2fa'] || $loginType === 'phone') {
            $authResult['code'] = $this->setSecretCode($user->id, UserConditions::Auth->value);
            if ($loginType === 'phone' || $this->settings['auth']['2faField'] === 'phone') {
                $authResult['phone'] = $user->phone;
            } elseif ($this->settings['auth']['2faField'] === 'email') {
                $authResult['email'] = $user->email;
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

        $type = $this->_checkType($data);

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
                'status'  => true,
                'direct'  => 'confirm',
                'user_id' => $user->id,
                'email'   => $user->email,
                'phone'   => $user->phone
            ],
            UserConditions::Auth->value       => [
                'status'  => true,
                'direct'  => 'set_user',
                'user_id' => $user->id
            ],
            UserConditions::Recovery->value   => [
                'status'  => true,
                'direct'  => 'password',
                'user_id' => $user->id,
                'hash'    => $hash ?? ''
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

        unset($user->password, $user->secret, $user->expires);

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

                'userId'    => $user->id,
                'timezone'  => $user->timezone,
                'login'     => $user->login,
                'status'    => $user->status,
                'condition' => $user->condition,
                'avatar'    => $user->avatar,
                'phone'     => $user->phone,
                'email'     => $user->email,
                'profile'   => $user->profile,
                'extra'     => $user->extra,
                'roleId'    => $role->roleId,
                'role'      => $role->role,
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

        return $userSession;
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

        if (empty($data) || $this->settings['auth']['recoveryField'] != array_keys($data)[0]) {
            throw AuthorizationException::forNoData();
        }

        if ( ! $this->validate($this->_validate('recovery'), $data)) {
            throw new AuthorizationException($this->validation->getErrors());
        }

        if (($user = $this->LM->getUser($data)) === null) {
            throw AuthorizationException::forUnknownUser();
        }

        $code = $this->setSecretCode($user->id, UserConditions::Recovery->value);

        $recoveryResult = [
            'status'    => true,
            'direct'    => 'send_code',
            'user_id'   => $user->id,
            'condition' => UserConditions::Recovery->value,
            'code'      => $code,
            'hash'      => $this->_hashCode($code)
        ];

        match ($this->settings['auth']['recoveryField']) {
            'phone' => ($recoveryResult['phone'] = $user->phone),
            'email',
            'login' => ($recoveryResult['email'] = $user->email),
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
            'condition' => UserConditions::Password->value
        ];

        if (($user = $this->LM->getUser($conditions)) === null) {
            throw AuthorizationException::forUnknownUser();
        }

        if ($user->expires < now($this->settings['env']['timezone'])) {
            throw AuthorizationException::forCodeExpired();
        }

        $request = Services::request();

        $update = $this->LM->save(
            [
                'id'         => $user->id,
                'secret'     => '',
                'expires'    => 0,
                'password'   => $data['password'],
                'condition'  => UserConditions::None->value,
                'last_ip'    => $request->getIPAddress(),
                'last_agent' => $request->getUserAgent()->getAgentString(),
                'active_at'  => now($user->timezone)
            ]
        );

        if ($update === false) {
            throw AuthorizationException::forFailPasswordUpdate();
        }

        if ($this->settings['auth']['useJwt']) {
            // Удаляем все записи токенов по пользователю
            $this->UTM->delete(['user_id' => $user->id]);
        }

        return [
            'status'  => true,
            'direct'  => 'set_user',
            'user_id' => $user->id
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
        $payload = $this->_getJwtPayload();

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
            if (hash_equals($item->refreshToken, $data['token'])) {
                if ($item->expires < now()) {
                    throw AuthorizationException::forFailUnauthorized('expiresToken');
                }

                if (empty($jwt = $this->_signatureTokenJWT((array) $payload->data))) {
                    throw AuthorizationException::forCreateToken();
                }

                $updated = $this->UTM->save(
                    [
                        'id'           => $item->id,
                        'access_token' => $jwt,
                        'user_ip'      => $request->getIPAddress(),
                        'user_agent'   => $request->getUserAgent()->getAgentString()
                    ]
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
     * @param  string|null  $role
     * @return void
     * @throws AuthorizationException
     * @throws ReflectionException
     */
    public function logout(?string $role = null): void
    {
        $userId = 0;

        if ($this->settings['auth']['useJwt']) {
            $payload = $this->_getJwtPayload();
            $this->UTM->where(['user_id' => ($userId = $payload->data->userId)])->delete();
        }

        if ($this->settings['auth']['useSession']) {
            $session = session('avegacms');
            if ($session[$role]['user'] ?? false) {
                $userId                 = $session[$role]['user']['user']['userId'];
                $session[$role]['user'] = null;
                session()->set('avegacms', $session);
            }
        }

        if ($userId > 0) {
            $this->LM->update($userId, ['secret' => '', 'expires' => 0, 'condition' => UserConditions::None->value]);
        }
    }

    /**
     * @param  bool  $isPublicAccess
     * @return void
     * @throws Exception
     */
    public function checkUserAccess(bool $isPublicAccess = false): void
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

                $existToken = false;
                $payload    = JWT::decode(
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
                        $existToken = true;
                        break;
                    }
                }

                if ($existToken === false) {
                    throw AuthenticationException::forTokenNotFound();
                }

                $userData = $payload->data;

            case 'token':
                // TODO реализовать в дальнейшем
                throw AuthenticationException::forTokenNotFound();
        }

        if ($isPublicAccess === false) {
            if (empty($segments = array_slice(array_slice($request->uri->getSegments(), 2), 0, 2))) {
                throw AuthenticationException::forUnknownPermission();
            }

            if (empty($map = $UAM->getRoleAccessMap($userData->user->role, $userData->user->roleId))) {
                throw AuthenticationException::forAccessDenied();
            }

            if (($permission = $this->_findPermission($map, $segments)) === null) {
                throw AuthenticationException::forForbiddenAccess();
            }

            $action = (bool) match ($request->getMethod()) {
                'get'    => $permission['read'],
                'post'   => $permission['create'],
                'put',
                'patch'  => $permission['update'],
                'delete' => $permission['delete'],
                default  => false
            };

            if ($action === false) {
                throw AuthenticationException::forForbiddenAccess();
            }

            Cms::setUser('permission', Cms::arrayToObject([
                'self'      => (bool) $permission['self'],
                'moderated' => (bool) $permission['moderated'],
                'settings'  => (bool) $permission['settings']
            ]));
        }

        Cms::setUser('user', $userData->user);
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
            [
                'id'        => $userId,
                'secret'    => $this->_hashCode($code),
                'expires'   => $this->_setExpiresTime($condition),
                'condition' => UserConditions::from($condition)->value
            ]
        );

        return $code;
    }

    protected function validate(array $rules, array $data): bool
    {
        return $this->validation->setRules($rules)->run($data);
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
            if ($actions['slug'] === strtolower($segments[$index]) && (int) $actions['parent'] === $parent) {
                if ((int) $actions['access'] === 0) {
                    throw AuthenticationException::forForbiddenAccess();
                }

                if (isset($actions['list']) && isset($segments[$index + 1])) {
                    return $this->_findPermission($actions['list'], $segments, $index + 1,
                        (int) $actions['module_id']) ?? $actions;
                }
                return $actions;
            }
        }

        return null;
    }

    /**
     * @return mixed
     * @throws AuthorizationException
     */
    private function _getJwtPayload(): mixed
    {
        if (empty($authHeader = explode(' ', Services::request()->getServer('HTTP_AUTHORIZATION') ?? '')) || count(
                $authHeader
            ) !== 2) {
            throw AuthorizationException::forFailUnauthorized();
        }

        if ( ! $this->settings['auth']['useJwt'] || $authHeader[0] !== 'Bearer' || count($token = explode('.',
                $authHeader[1])) !== 3) {
            throw AuthorizationException::forFailUnauthorized();
        }

        if (($payload = JWT::jsonDecode(JWT::urlsafeB64Decode($token[1]))) === null) {
            throw AuthorizationException::forFailUnauthorized();
        }

        return $payload;
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
     * @param  array  $data
     * @return array
     * @throws AuthorizationException
     */
    private function _checkType(array $data): array
    {
        $fields = explode(':', $this->settings['auth']['loginType']);

        foreach ($data as $key => $item) {
            if (in_array($key, $fields, true)) {
                return [$key => $item];
            }
        }

        throw AuthorizationException::forUnknownLoginField(implode(':', $fields));
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

        $roles = $this->RM->getActiveRoles();

        if ( ! isset($roles[$userdata['user']['role']])) {
            throw AuthorizationException::forUnknownRole();
        }

        $session = $this->session->get('avegacms');

        if ($roles[$userdata['user']['role']]['selfAuth']) {
            $session['client']['user'] = $userdata;
        } else {
            $session['admin'] = $userdata;
        }

        $this->session->set('avegacms', $session);
    }

    /**
     * @param  string  $type
     * @return array[]
     * @throws AuthorizationException
     */
    private function _validate(string $type): array
    {
        $phone    = 'mob_phone';
        $login    = 'required|max_length[36]';
        $email    = 'max_length[255]|valid_email';
        $password = 'required|verify_password';
        $token    = 'required|max_length[255]|alpha_numeric';

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
                    'rules' => 'required|in_list[' . implode(',', UserConditions::get('value')) . ']',
                ],
                'login'     => [
                    'label'  => lang('Authorization.fields.login'),
                    'rules'  => 'if_exist|required_without[phone,email]|max_length[36]|is_not_unique[users.login]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique')
                    ]
                ],
                'email'     => [
                    'label'  => lang('Authorization.fields.email'),
                    'rules'  => 'if_exist|required_without[phone,login]|' . $email . '|is_not_unique[users.email]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique')
                    ]
                ],
                'phone'     => [
                    'label'  => lang('Authorization.fields.phone'),
                    'rules'  => 'if_exist|required_without[email,login]|' . $phone . '|is_not_unique[users.phone]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique')
                    ]
                ],
                'code'      => [
                    'label' => lang('Authorization.fields.code'),
                    'rules' => 'required|numeric|exact_length[' . $this->settings['auth']['verifyCodeLength'] . ']'
                ]
            ],
            'recovery'      => [
                'email' => [
                    'label'  => lang('Authorization.fields.email'),
                    'rules'  => 'if_exist|required_without[login]|' . $email . '|is_not_unique[users.email]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique')
                    ]
                ],
                'login' => [
                    'label'  => lang('Authorization.fields.login'),
                    'rules'  => 'if_exist|required_without[email]|max_length[36]|is_not_unique[users.login]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique')
                    ]
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
