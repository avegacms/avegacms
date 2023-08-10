<?php

declare(strict_types=1);

namespace AvegaCms\Libraries\Authorization;

use AvegaCms\Libraries\Authorization\Exceptions\{AuthorizationExceptions, ValidationException};
use AvegaCms\Entities\{LoginEntity, UserEntity, UserTokensEntity};
use AvegaCms\Models\Admin\{LoginModel, UserRolesModel, UserTokensModel};
use CodeIgniter\Validation\ValidationInterface;
use CodeIgniter\Session\Session;
use Config\Services;
use Firebase\JWT\JWT;
use Exception;
use ReflectionException;

class Authorization
{
    protected array           $settings = [];
    protected LoginModel      $LM;
    protected UserTokensModel $UTM;

    protected Session $session;

    protected ValidationInterface $validator;

    public function __construct(array $settings)
    {
        helper(['date', 'avegacms']);

        if (empty($settings)) {
            throw AuthorizationExceptions::forNoData();
        }

        $this->settings = $settings;
        $this->LM = model(LoginModel::class);
        $this->UTM = model(UserTokensModel::class);

        $this->session = Services::session();
    }

    /**
     * @param  array  $data
     * @extension AuthorizationExceptions
     * @extension
     * @return array
     * @throws ValidationException|ReflectionException
     */
    public function auth(array $data): array
    {
        if (empty($data)) {
            throw AuthorizationExceptions::forNoData();
        }

        if ( ! in_array($this->settings['auth']['loginType'] ?? '', $this->settings['auth']['loginTypeList'])) {
            throw AuthorizationExceptions::forUnknownAuthType($this->settings['auth']['loginType']);
        }

        $loginType = $this->_checkType($data[$this->settings['auth']['loginType']]);

        if ( ! $this->validate($this->_validate('auth_by_' . $this->settings['auth']['loginType']), $data)) {
            throw new ValidationException($this->validator->getErrors());
        }

        if (($user = $this->LM->getUser($loginType)) === null) {
            throw AuthorizationExceptions::forUnknownUser();
        }

        if (isset($data['password']) && ! password_verify($data['password'], $user->password)) {
            throw AuthorizationExceptions::forWrongPassword();
        }

        $authResult = [
            'status'    => true,
            'direct'    => 'set_user',
            'userdata'  => ['user_id' => $user->id],
            'condition' => 'auth'
        ];

        $loginType = key($loginType);

        if ($this->settings['auth']['use2fa'] || $loginType === 'phone') {
            $authResult['userdata']['code'] = $this->_setSecretCode($user->id, 'auth');
            if ($loginType === 'phone' || $this->settings['auth']['2faField'] === 'phone') {
                $authResult['userdata']['phone'] = $user->phone;
            } elseif ($this->settings['auth']['2faField'] === 'email') {
                $authResult['userdata']['email'] = $user->email;
            } else {
                throw AuthorizationExceptions::forFailSendAuthCode();
            }

            $authResult['direct'] = 'send_code';
        }

        return $authResult;
    }

    /**
     * @param  array  $data
     * @return array[]
     * @throws AuthorizationExceptions|ValidationException|Exception
     */
    public function checkCode(array $data): array
    {
        if (empty($data)) {
            throw AuthorizationExceptions::forNoData();
        }

        if ( ! $this->validate($this->_validate('check_code'), $data)) {
            throw new ValidationException($this->validator->getErrors());
        }

        $type = $this->_checkType($data['pointer']);

        $conditions = [
            'expires >' => 0,
            'secret !=' => '',
            ...$type
        ];

        if (($user = $this->LM->getUser($conditions)) === null) {
            throw AuthorizationExceptions::forUnknownUser();
        }

        if ($user->expires < now($this->settings['env']['timezone'])) {
            throw AuthorizationExceptions::forCodeExpired();
        }

        if ($user->secret !== $this->_hashCode((int) $data['code'])) {
            throw AuthorizationExceptions::forWrongCode();
        }

        if ($data['condition'] === 'recovery') {
            $hash = $this->_hashCode($this->_setSecretCode($user->id, 'password'));
        }

        return match ($data['condition']) {
            'auth'     => [
                'status'   => true,
                'direct'   => 'set_user',
                'userdata' => ['user_id' => $user->id]
            ],
            'recovery' => [
                'status'   => true,
                'direct'   => 'password',
                'userdata' => ['user_id' => $user->id, 'hash' => $hash ?? '']
            ],
            default    => throw AuthorizationExceptions::forWrongCode()
        };
    }

    /**
     * @param  int  $userId
     * @param  array  $userData
     * @return array[]
     * @throws ReflectionException|Exception
     */
    public function setUser(int $userId, array $userData = []): array
    {
        if (($user = $this->LM->getUser(['id' => $userId])) === null) {
            throw AuthorizationExceptions::forUnknownUser();
        }

        unset($user->password, $user->secret, $user->expires, $user->reset);

        $URM = model(UserRolesModel::class);

        $roles = $URM->getUserRoles($user->id)->findColumn('role');

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
                'avatar'   => $user->avatar,
                'phone'    => $user->phone,
                'email'    => $user->email,
                'extra'    => $user->extra,
                'roles'    => $roles,
                ...$userData
            ]
        ];

        $request = Services::request();

        $userAgent = $request->getUserAgent()->getAgentString();
        $userIp = $request->getIPAddress();

        if ($this->settings['auth']['useJwt']) {
            if (empty($token = $this->_signatureTokenJWT($userSession['user']))) {
                throw AuthorizationExceptions::forCreateToken();
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
                throw AuthorizationExceptions::forCreateToken();
            }
        }

        if ($this->settings['auth']['useSession']) {
            initClientSession();
            $this->_setClientSession($userSession);
        }

        $this->LM->save(
            (new UserEntity([
                'id'         => $user->id,
                'secret'     => '',
                'expires'    => 0,
                'reset'      => 0,
                'condition'  => '',
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
     * @throws AuthorizationExceptions|ValidationException|Exception
     */
    public function recovery(array $data): array
    {
        if ($this->settings['auth']['useRecovery'] === false) {
            throw AuthorizationExceptions::forFailForbidden();
        }

        if (empty($data)) {
            throw AuthorizationExceptions::forNoData();
        }

        if ( ! $this->validate($this->_validate('recovery'), $data)) {
            throw new ValidationException($this->validator->getErrors());
        }

        $field = $this->settings['auth']['recoveryField'];

        if (($user = $this->LM->getUser([$field => $data['recovery_field']])) === null) {
            throw AuthorizationExceptions::forUnknownUser();
        }

        $code = $this->_setSecretCode($user->id, 'recovery');

        $recoveryResult = [
            'status'   => true,
            'direct'   => 'send_code',
            'userdata' => [
                'user_id'   => $user->id,
                'code'      => $code,
                'condition' => 'recovery',
                'hash'      => $this->_hashCode($code)
            ],
        ];

        match ($field) {
            'phone' => ($recoveryResult['userdata']['phone'] = $user->phone),
            'email',
            'login' => ($recoveryResult['userdata']['email'] = $user->email),
            default => throw AuthorizationExceptions::forFailSendAuthCode()
        };

        return $recoveryResult;
    }

    /**
     * @param  array  $data
     * @return array
     * @throws ValidationException|ReflectionException|ValidationException|Exception
     */
    public function setPassword(array $data): array
    {
        if ($this->settings['auth']['useRecovery'] === false) {
            throw AuthorizationExceptions::forFailForbidden();
        }

        if (empty($data)) {
            throw AuthorizationExceptions::forNoData();
        }

        if ( ! $this->validate($this->_validate('password'), $data)) {
            throw new ValidationException($this->validator->getErrors());
        }

        $conditions = [
            'expires >' => 0,
            'secret'    => $data['hash'],
            'condition' => 'recovery'
        ];

        if (($user = $this->LM->getUser($conditions)) === null) {
            throw AuthorizationExceptions::forUnknownUser();
        }

        if ($user->expires < now($this->settings['env']['timezone'])) {
            throw AuthorizationExceptions::forCodeExpired();
        }

        $request = Services::request();

        $update = $this->LM->save(
            (new LoginEntity([
                'id'         => $user->id,
                'secret'     => '',
                'expires'    => 0,
                'reset'      => 0,
                'password'   => $data['password'],
                'condition'  => '',
                'last_ip'    => $request->getIPAddress(),
                'last_agent' => $request->getUserAgent()->getAgentString(),
                'active_at'  => now($user->timezone)
            ]))
        );

        if ($update === false) {
            throw AuthorizationExceptions::forFailPasswordUpdate();
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
     * @throws ReflectionException|ValidationException
     * @throws Exception
     */
    public function refresh(array $data): array
    {
        $request = Services::request();

        if (empty($authHeader = explode(' ', $request->getServer('HTTP_AUTHORIZATION'))) || count(
                $authHeader
            ) !== 2) {
            throw AuthorizationExceptions::forFailUnauthorized();
        }

        $token = match ($authHeader[0]) {
            'Token'  => $this->settings['auth']['useToken'] ? $authHeader[1] : false,
            'Bearer' => $this->settings['auth']['useJwt'] ? $authHeader[1] : false,
            default  => false
        };

        if ($token === false || count($token = explode('.', $token)) !== 3) {
            throw AuthorizationExceptions::forFailUnauthorized();
        }

        if (($payload = JWT::jsonDecode(JWT::urlsafeB64Decode($token[1]))) === null) {
            throw AuthorizationExceptions::forFailUnauthorized();
        }

        if (empty($data)) {
            throw AuthorizationExceptions::forNoData();
        }

        if ( ! $this->validate($this->_validate('refresh_token'), $data)) {
            throw new ValidationException($this->validator->getErrors());
        }

        if (empty($tokens = $this->UTM->getUserTokens($payload->data->userId)->findAll())) {
            throw AuthorizationExceptions::forFailUnauthorized();
        }

        foreach ($tokens as $item) {
            if (hash_equals($item->refresh_token, $data['token'])) {
                if ($item->expires < now()) {
                    throw AuthorizationExceptions::forFailUnauthorized('expiresToken');
                }

                if (empty($jwt = $this->_signatureTokenJWT((array) $payload->data))) {
                    throw AuthorizationExceptions::forCreateToken();
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

        throw AuthorizationExceptions::forFailUnauthorized('tokenNotFound');
    }

    /**
     * @return void
     */
    public function logout(): void
    {
        $this->_setClientSession();
    }

    protected function validate(array $rules, array $data): bool
    {
        $this->validator = Services::validation();

        return $this->validator->setRules($rules)->run($data);
    }

    /**
     * @param  int  $userId
     * @param  string  $condition
     * @return int
     * @throws ReflectionException|Exception
     */
    private function _setSecretCode(int $userId, string $condition): int
    {
        $code = $this->_getCode();

        $this->LM->save(
            (new LoginEntity(
                [
                    'id'        => $userId,
                    'secret'    => $this->_hashCode($code),
                    'expires'   => $this->_setExpiresTime($condition),
                    'condition' => $condition
                ]
            ))
        );

        return $code;
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
        return match ($condition) {
                'auth'     => $this->settings['auth']['verifyCodeTime'],
                'recovery',
                'password' => $this->settings['auth']['recoveryCodeTime'],
            } * (now($this->settings['env']['timezone']) * MINUTE);
    }

    /**
     * @param  array  $userData
     * @return string
     */
    public function _signatureTokenJWT(array $userData): string
    {
        $issuedAtTime = time();
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
     * @throws AuthorizationExceptions
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

        throw AuthorizationExceptions::forUnknownLoginField();
    }

    /**
     * @param  array  $userdata
     * @return void
     */
    private function _setClientSession(array $userdata = []): void
    {
        $currentSession['avegacms'] = $this->session->get('avegacms');
        $currentSession['avegacms']['admin'] = $userdata;
        $this->session->set($currentSession);
        unset($currentSession);
    }

    /**
     * @param  string  $type
     * @return array[]
     * @throws ValidationException
     */
    private function _validate(string $type): array
    {
        $phone = 'exact_length[11]|regex_match[/^79\d{9}/]';
        $login = 'required|max_length[36]';
        $email = 'max_length[255]|valid_email';
        $password = 'required|min_length[6]|max_length[255]|alpha_numeric_punct';
        $condition = 'required|in_list[auth,recovery,password]';
        $code = 'required|numeric|exact_length[' . $this->settings['auth']['verifyCodeLength'] . ']';
        $token = 'required|max_length[255]|alpha_numeric';
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
                    'rules' => 'required|' . (($recoveryField == 'phone') ? $phone : $email)
                ]
            ],
            'password'      => [
                'password' => [
                    'label' => lang('Authorization.fields.password'),
                    'rules' => $password
                ],
                'passconf' => [
                    'label' => lang('Authorization.fields.passconf'),
                    'rules' => 'required|max_length[255]|matches[password]'
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
            default         => throw ValidationException::forRulesNotFound()
        };
    }
}
