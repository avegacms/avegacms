<?php

declare(strict_types=1);

namespace AvegaCms\Libraries\Authorization;

use AvegaCms\Config\Services;
use AvegaCms\Enums\UserConditions;
use AvegaCms\Libraries\Authorization\Exceptions\AuthenticationException;
use AvegaCms\Libraries\Authorization\Exceptions\AuthorizationException;
use AvegaCms\Models\Admin\AttemptsEntranceModel;
use AvegaCms\Models\Admin\LoginModel;
use AvegaCms\Models\Admin\RolesModel;
use AvegaCms\Models\Admin\SessionsModel;
use AvegaCms\Models\Admin\UserAuthenticationModel;
use AvegaCms\Models\Admin\UserRolesModel;
use AvegaCms\Models\Admin\UserTokensModel;
use AvegaCms\Utilities\Auth;
use AvegaCms\Utilities\Cms;
use CodeIgniter\Session\Session;
use CodeIgniter\Validation\Validation;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use ReflectionException;

class Authorization
{
    protected array $settings = [];
    protected LoginModel $LM;
    protected RolesModel $RM;
    protected UserTokensModel $UTM;
    protected UserRolesModel $URM;
    protected AttemptsEntranceModel $AEM;
    protected Session $session;
    protected Validation $validation;

    /**
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
        $this->RM       = model(RolesModel::class);
        $this->UTM      = model(UserTokensModel::class);
        $this->URM      = model(UserRolesModel::class);
        $this->AEM      = model(AttemptsEntranceModel::class);
    }

    /**
     * @throws AuthorizationException
     * @throws ReflectionException
     */
    public function auth(array $data, ?string $role = null): array
    {
        if (empty($data)) {
            throw AuthorizationException::forNoData();
        }

        if (! in_array($this->settings['auth']['loginType'] ?? '', $this->settings['auth']['loginTypeList'], true)) {
            throw AuthorizationException::forUnknownAuthType($this->settings['auth']['loginType']);
        }

        $loginType = $this->_checkType($data);

        if (! $this->validate($this->_validate('auth_by_' . array_keys($loginType)[0]), $data)) {
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
            'role'      => $user->role,
            'condition' => UserConditions::Auth->value,
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
     * @throws AuthorizationException|Exception
     */
    public function checkCode(string $login, int $code): bool
    {
        if (($data = $this->AEM->getCode($login)) === null) {
            throw AuthorizationException::forUnknownCode();
        }

        if ($data->expires < now($this->settings['env']['timezone'])) {
            throw AuthorizationException::forCodeExpired();
        }

        if ($data->code !== $code) {
            throw AuthorizationException::forWrongCode();
        }

        // Удаляем запись попытки
        $this->AEM->clear($login);

        return true;
    }

    /**
     * @return list<array>
     *
     * @throws AuthorizationException|Exception
     */
    public function checkCodeOld(array $data): array
    {
        if (empty($data)) {
            throw AuthorizationException::forNoData();
        }

        if (! $this->validate($this->_validate('check_code'), $data)) {
            throw new AuthorizationException($this->validation->getErrors());
        }

        $type = $this->_checkType($data);

        $conditions = [
            'expires >' => 0,
            'secret !=' => '',
            ...$type,
        ];

        if (($user = $this->LM->getUser($conditions)) === null) {
            throw AuthorizationException::forUnknownUser();
        }

        if (! in_array($data['condition'], [UserConditions::CheckPhone->value, UserConditions::CheckEmail->value], true)) {
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
                'phone'   => $user->phone,
            ],
            UserConditions::Auth->value => [
                'status'  => true,
                'direct'  => 'set_user',
                'user_id' => $user->id,
                'role'    => $user->role,
            ],
            UserConditions::Recovery->value => [
                'status'  => true,
                'direct'  => 'password',
                'user_id' => $user->id,
                'hash'    => $hash ?? '',
            ],
            default => throw AuthorizationException::forWrongCode()
        };
    }

    /**
     * @throws AuthorizationException|Exception|ReflectionException
     */
    public function setUser(int $userId, string $userRole = '', array $userData = []): array
    {
        if (($user = $this->LM->getUser(['id' => $userId, 'role' => $userRole])) === null) {
            throw AuthorizationException::forUnknownUser();
        }

        unset($user->password, $user->secret, $user->expires);

        $request     = Services::request();
        $userAgent   = $request->getUserAgent()->getAgentString();
        $userIp      = $request->getIPAddress();
        $userSession = [
            'isAuth'   => true,
            'selfAuth' => true,
            'module'   => $user->module,
            'userId'   => $user->id,
            'roleId'   => $user->roleId,
            'role'     => $user->role,
            'status'   => $user->status,
        ];

        if ($this->settings['auth']['useSession']) {
            Cms::initClientSession();
            $session = session('avegacms');

            if ($user->selfAuth) {
                $session['modules'][$user->module ?? $user->role] = $userSession;
            } else {
                $session['admin'] = $userSession;
            }

            session()->set('avegacms', $session);
        } elseif ($this->settings['auth']['useJwt']) {
            $jwt = $this->_signatureTokenJWT($userSession);

            if (empty($jwt['token'])) {
                throw AuthorizationException::forCreateToken();
            }

            $sessions = ($this->UTM->getUserTokens($user->id)->findColumn('id') ?? []);

            if (count($sessions) >= $this->settings['auth']['jwtSessionsLimit']) {
                $this->UTM->delete($sessions[0]);
            }

            $userSession['sessionId']    = sha1($user->id . $userAgent . bin2hex(random_bytes(32)));
            $userSession['accessToken']  = $jwt['token'];
            $userSession['refreshToken'] = sha1(
                $user->phone .
                $jwt['expired'] .
                $this->settings['auth']['jwtSecretKey'] .
                $userAgent
            );

            $newUserSession = [
                'id'            => $userSession['sessionId'],
                'user_id'       => $user->id,
                'role_id'       => $user->roleId,
                'access_token'  => $userSession['accessToken'],
                'refresh_token' => $userSession['refreshToken'],
                'expires'       => $jwt['expired'],
                'user_ip'       => $userIp,
                'user_agent'    => $userAgent,
            ];

            if (! $this->UTM->insert($newUserSession)) {
                throw new AuthorizationException($this->UTM->errors());
            }
        } else {
            throw AuthorizationException::forUserSessionNotExist();
        }

        $this->LM->save(
            [
                'id'         => $user->id,
                'secret'     => '',
                'expires'    => 0,
                'last_ip'    => $userIp,
                'last_agent' => $userAgent,
                'active_at'  => date('Y-m-d H:i:s', now($user->timezone)),
            ]
        );

        $userSession['profile'] = Auth::setProfile($user->id, $user->role, $userData);

        return $userSession;
    }

    /**
     * @throws AuthorizationException|Exception
     */
    public function recovery(array $data): array
    {
        if ($this->settings['auth']['useRecovery'] === false) {
            throw AuthorizationException::forFailForbidden();
        }

        if (empty($data) || $this->settings['auth']['recoveryField'] !== array_keys($data)[0]) {
            throw AuthorizationException::forNoData();
        }

        if (! $this->validate($this->_validate('recovery'), $data)) {
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
            'hash'      => $this->_hashCode($code),
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
     * @throws Exception|ReflectionException
     */
    public function setPassword(array $data): array
    {
        if ($this->settings['auth']['useRecovery'] === false) {
            throw AuthorizationException::forFailForbidden();
        }

        if (empty($data)) {
            throw AuthorizationException::forNoData();
        }

        if (! $this->validate($this->_validate('password'), $data)) {
            throw new AuthorizationException($this->validation->getErrors());
        }

        $conditions = [
            'expires >' => 0,
            'secret'    => $data['hash'],
            'condition' => UserConditions::Password->value,
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
                'active_at'  => date('Y-m-d H:i:s', now($user->timezone)),
            ]
        );

        if ($update === false) {
            throw AuthorizationException::forFailPasswordUpdate();
        }

        if ($this->settings['auth']['useJwt']) {
            // Удаляем все записи токенов по пользователю
            $this->UTM->where(['user_id' => $user->id])->delete();
        }

        return [
            'status'  => true,
            'direct'  => 'set_user',
            'user_id' => $user->id,
        ];
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function refresh(array $data): array
    {
        $request = Services::request();
        $payload = $this->_getJwtPayload();

        if (empty($data)) {
            throw AuthorizationException::forNoData();
        }

        if (! $this->validate($this->_validate('refresh_token'), $data)) {
            throw new AuthorizationException($this->validation->getErrors());
        }

        if (empty($tokens = $this->UTM->getUserTokens($payload->data->userId)->findAll())) {
            throw AuthorizationException::forFailUnauthorized();
        }

        foreach ($tokens as $item) {
            if (hash_equals($item->refresh_token, $data['token'])) {
                if ($item->expires > now()) {
                    throw AuthorizationException::forFailUnauthorized('expiresToken');
                }

                $jwt = $this->_signatureTokenJWT((array) $payload->data);

                if (empty($jwt['token'])) {
                    throw AuthorizationException::forCreateToken();
                }

                $updated = $this->UTM->save(
                    [
                        'id'           => $item->id,
                        'access_token' => $jwt['token'],
                        'expires'      => $jwt['expired'],
                        'user_ip'      => $request->getIPAddress(),
                        'user_agent'   => $request->getUserAgent()->getAgentString(),
                    ]
                );

                if (! $updated) {
                    throw new AuthorizationException($this->UTM->errors());
                }

                return ['data' => ['access_token' => $jwt['token']]];
            }
        }

        throw AuthorizationException::forFailUnauthorized('tokenNotFound');
    }

    /**
     * @throws AuthorizationException
     * @throws ReflectionException
     */
    public function logout(string $slug): void
    {
        $userId = 0;

        if ($this->settings['auth']['useJwt']) {
            $payload = $this->_getJwtPayload();
            $this->UTM->where(['user_id' => ($userId = $payload->data->userId)])->delete();
        }

        if ($this->settings['auth']['useSession']) {
            $session = session('avegacms');

            if (in_array($slug, ['admin', 'client'], true)) {
                if (array_key_exists($slug, $session)) {
                    $userId ??= $session[$slug]['userId'];
                    $session[$slug] = null;
                }
            }

            if (null !== $session['modules']) {
                if (array_key_exists($slug, $session['modules'])) {
                    $userId ??= $session['modules'][$slug]['userId'];
                    $session['modules'][$slug] = null;
                }
            }

            session()->set('avegacms', $session);
        }

        if ($userId > 0) {
            $this->LM->update($userId, ['secret' => '', 'expires' => 0, 'condition' => UserConditions::None->value]);
        }
    }

    /**
     * @throws Exception
     */
    public function checkUserAccess(bool $isPublicAccess = false): void
    {
        $request  = Services::request();
        $userData = null;
        $UTM      = model(UserTokensModel::class);
        $UAM      = model(UserAuthenticationModel::class);

        if ($this->settings['auth']['useWhiteIpList']
            && ! empty($this->settings['auth']['whiteIpList'])
            && in_array($request->getIPAddress(), $this->settings['auth']['whiteIpList'], true)
        ) {
            throw AuthenticationException::forAccessDenied();
        }

        if (empty($authHeader = $request->getServer('HTTP_AUTHORIZATION') ?? '')) {
            throw AuthenticationException::forNoHeaderAuthorize();
        }

        $authHeader = explode(' ', $authHeader);

        $authType = match ($authHeader[0]) {
            'Token'  => ($this->settings['auth']['useToken']) ? ['type' => 'token', 'token' => $authHeader[1]] : false,
            'Bearer' => (strtolower($authHeader[1]) === 'session' && $this->settings['auth']['useSession']) ?
                ['type' => 'session'] :
                (
                    $this->settings['auth']['useJwt'] ?
                    [
                        'type' => 'jwt', 'token' => $authHeader[1],
                    ] : false
                ),
            default => false
        };

        if ($authType === false) {
            throw AuthenticationException::forAccessDenied();
        }

        switch ($authType['type']) {
            case 'session':
                $session = session();

                if ($session->has('avegacms') === false) {
                    throw AuthenticationException::forUserSessionNotExist();
                }

                if ($session->get('avegacms.admin.isAuth') !== true) {
                    throw AuthenticationException::forNotAuthorized();
                }

                $userData = (object) $session->get('avegacms.admin');

                break;

            case 'jwt':
                $existToken = false;

                try {
                    $payload = JWT::decode(
                        $authType['token'],
                        new Key(
                            $this->settings['auth']['jwtSecretKey'],
                            $this->settings['auth']['jwtAlg']
                        )
                    );
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }

                if (empty($tokens = $UTM->getUserTokens($payload->data->userId)->findAll())) {
                    throw AuthenticationException::forNotAuthorized();
                }

                foreach ($tokens as $item) {
                    if (hash_equals($item->accessToken, $authType['token'])) {
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
                break;

            case 'token':
                // TODO реализовать в дальнейшем
                throw AuthenticationException::forTokenNotFound();
        }

        if ($isPublicAccess === false) {
            if (empty($segments = array_slice(array_slice($request->getUri()->getSegments(), 2), 0, 2))) {
                throw AuthenticationException::forUnknownPermission();
            }

            if ($segments[0] !== 'profile') {
                if (empty($map = $UAM->getRoleAccessMap($userData->role, $userData->roleId))) {
                    throw AuthenticationException::forAccessDenied();
                }
                if (($permission = $this->_findPermission($map, $segments)) === null) {
                    throw AuthenticationException::forForbiddenAccess();
                }
            } else {
                $permission['read'] = 1;
            }

            $action = (bool) match ($request->getMethod()) {
                'GET'  => ($permission['read'] ?? 0),
                'POST' => ($permission['create'] ?? 0),
                'PUT',
                'PATCH'  => ($permission['update'] ?? 0),
                'DELETE' => ($permission['delete'] ?? 0),
                default  => false
            };

            if ($action === false) {
                throw AuthenticationException::forForbiddenAccess();
            }

            Cms::setUser('permission', Cms::arrayToObject([
                'self'      => (bool) ($permission['self'] ?? 0),
                'moderated' => (bool) ($permission['moderated'] ?? 0),
                'settings'  => (bool) ($permission['settings'] ?? 0),
            ]));
        }

        Cms::setUser('user', $userData);
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function setSecretCode(int $userId, string $condition): int
    {
        $code = $this->_getCode();

        $this->LM->save(
            [
                'id'        => $userId,
                'secret'    => $this->_hashCode($code),
                'expires'   => $this->_setExpiresTime($condition),
                'condition' => UserConditions::from($condition)->value,
            ]
        );

        return $code;
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function setCode(string $login): array
    {
        $attempts    = 1;
        $delayPeriod = $this->settings['auth']['codeSmsDelayPeriods'];
        $time        = $delayPeriod[0];

        if (filter_var($login, FILTER_VALIDATE_EMAIL) === true) {
            $delayPeriod = $this->settings['auth']['codeEmailDelayPeriods'];
        }

        if (($data = $this->AEM->getCode($login)) !== null) {
            if ($data->expires > now($this->settings['env']['timezone'])) {
                throw AuthorizationException::forCodeNotExpired();
            }

            // увеличиваем период действия кода
            $time = current(array_filter($delayPeriod, static fn ($value) => $value > ($data->delay / MINUTE))) ?: max($delayPeriod);
            $attempts++; // увеличиваем количество попыток
            cache()->delete('AttemptsEntrance_' . $data->id);
        }

        $code = $this->_getCode();

        $request = Services::request();
        $delay   = $time * MINUTE;

        $data = [
            'id'         => md5($login),
            'login'      => $login,
            'code'       => $code,
            'attempts'   => $attempts,
            'delay'      => $delay,
            'expires'    => now($this->settings['env']['timezone']) + $delay,
            'user_id'    => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
        ];

        if ($this->AEM->save($data) === false) {
            throw AuthorizationException::forCreateCode();
        }

        return [
            'code'  => $code,
            'delay' => $delay,
        ];
    }

    public function destroyUserSessions(int $userId): bool
    {
        if ($this->settings['auth']['useJwt']) {
            if (! $this->UTM->where(['user_id' => $userId])->delete()) {
                throw AuthenticationException::forDestroyUserSessionError();
            }

            return true;
        }
        if ($this->settings['auth']['useSession']) {
            if (! model(SessionsModel::class)->where(['user_id' => $userId])->delete()) {
                throw AuthenticationException::forDestroyUserSessionError();
            }

            return true;
        }

        return false;
    }

    protected function validate(array $rules, array $data): bool
    {
        $this->validation = Services::validation();

        return $this->validation->setRules($rules)->run($data);
    }

    /**
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

                if (isset($actions['list'], $segments[$index + 1])) {
                    return $this->_findPermission(
                        $actions['list'],
                        $segments,
                        $index + 1,
                        (int) $actions['module_id']
                    ) ?? $actions;
                }

                return $actions;
            }
        }

        return null;
    }

    /**
     * @throws AuthorizationException
     */
    private function _getJwtPayload(): mixed
    {
        if (empty($authHeader = explode(' ', Services::request()->getServer('HTTP_AUTHORIZATION') ?? '')) || count(
            $authHeader
        ) !== 2) {
            throw AuthorizationException::forFailUnauthorized();
        }

        if (! $this->settings['auth']['useJwt'] || $authHeader[0] !== 'Bearer' || count($token = explode(
            '.',
            $authHeader[1]
        )) !== 3) {
            throw AuthorizationException::forFailUnauthorized();
        }

        if (($payload = JWT::jsonDecode(JWT::urlsafeB64Decode($token[1]))) === null) {
            throw AuthorizationException::forFailUnauthorized();
        }

        return $payload;
    }

    /**
     * @throws Exception
     */
    private function _getCode(): int
    {
        return random_int(
            1000,
            (10 ** $this->settings['auth']['verifyCodeLength']) - 1
        );
    }

    private function _hashCode(int $code): string
    {
        return sha1($code . $this->settings['env']['secretKey']);
    }

    /**
     * @throws Exception
     */
    private function _setExpiresTime(string $condition): int
    {
        return match (strtolower($condition)) {
            'auth',
            'check_phone',
            'check_email' => $this->settings['auth']['verifyCodeTime'],
            'recovery',
            'password' => $this->settings['auth']['recoveryCodeTime'],
        } * (now($this->settings['env']['timezone']) * MINUTE);
    }

    public function _signatureTokenJWT(array $userData): array
    {
        $issuedAtTime    = time();
        $tokenExpiration = $issuedAtTime + ($this->settings['auth']['jwtLiveTime'] * MINUTE);

        return [
            'expired' => $tokenExpiration,
            'token'   => JWT::encode(
                [
                    'iss'  => base_url(),
                    'aud'  => 'API',
                    'sub'  => 'AvegaCMS API',
                    'nbf'  => $issuedAtTime,
                    'iat'  => $issuedAtTime, // Время выпуска JWT
                    'exp'  => $tokenExpiration, // Время действия JWT-токена
                    'data' => $userData,
                ],
                $this->settings['auth']['jwtSecretKey'],
                $this->settings['auth']['jwtAlg']
            ),
        ];
    }

    /**
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
     * @return list<array>
     *
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
                'login' => [
                    'label'  => lang('Authorization.fields.login'),
                    'rules'  => $login . '|is_not_unique[users.login]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique'),
                    ],
                ],
                'password' => [
                    'label' => lang('Authorization.fields.password'),
                    'rules' => $password,
                ],
            ],
            'auth_by_email' => [
                'email' => [
                    'label'  => lang('Authorization.fields.email'),
                    'rules'  => 'required|' . $email . '|is_not_unique[users.email]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique'),
                    ],
                ],
                'password' => [
                    'label' => lang('Authorization.fields.password'),
                    'rules' => $password,
                ],
            ],
            'auth_by_phone' => [
                'phone' => [
                    'label'  => lang('Authorization.fields.phone'),
                    'rules'  => 'required|' . $phone . '|is_not_unique[users.phone]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique'),
                    ],
                ],
            ],
            'check_code' => [
                'condition' => [
                    'label' => lang('Authorization.fields.condition'),
                    'rules' => 'required|in_list[' . implode(',', UserConditions::get('value')) . ']',
                ],
                'login' => [
                    'label'  => lang('Authorization.fields.login'),
                    'rules'  => 'if_exist|required_without[phone,email]|max_length[36]|is_not_unique[users.login]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique'),
                    ],
                ],
                'email' => [
                    'label'  => lang('Authorization.fields.email'),
                    'rules'  => 'if_exist|required_without[phone,login]|' . $email . '|is_not_unique[users.email]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique'),
                    ],
                ],
                'phone' => [
                    'label'  => lang('Authorization.fields.phone'),
                    'rules'  => 'if_exist|required_without[email,login]|' . $phone . '|is_not_unique[users.phone]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique'),
                    ],
                ],
                'code' => [
                    'label' => lang('Authorization.fields.code'),
                    'rules' => 'required|numeric|exact_length[' . $this->settings['auth']['verifyCodeLength'] . ']',
                ],
            ],
            'recovery' => [
                'email' => [
                    'label'  => lang('Authorization.fields.email'),
                    'rules'  => 'if_exist|required_without[login]|' . $email . '|is_not_unique[users.email]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique'),
                    ],
                ],
                'login' => [
                    'label'  => lang('Authorization.fields.login'),
                    'rules'  => 'if_exist|required_without[email]|max_length[36]|is_not_unique[users.login]',
                    'errors' => [
                        'is_not_unique' => lang('Authorization.errors.isNotUnique'),
                    ],
                ],
            ],
            'password' => [
                'password' => [
                    'label' => lang('Authorization.fields.password'),
                    'rules' => $password,
                ],
                'passconf' => [
                    'label' => lang('Authorization.fields.passconf'),
                    'rules' => 'required|matches[password]',
                ],
                'hash' => [
                    'label' => lang('Authorization.fields.hash'),
                    'rules' => 'required|max_length[255]|alpha_numeric',
                ],
            ],
            'refresh_token' => [
                'token' => [
                    'label'  => lang('Authorization.fields.token'),
                    'rules'  => $token,
                    'errors' => [
                        'max_length'    => lang('Authorization.errors.wrongToken'),
                        'alpha_numeric' => lang('Authorization.errors.wrongToken'),
                    ],
                ],
            ],
            default => throw AuthorizationException::forRulesNotFound()
        };
    }
}
