<?php

namespace AvegaCms\Database\Seeds;

use CodeIgniter\Database\Seeder;
use AvegaCms\Models\Admin\{SettingsModel, LoginModel, RolesModel, UserRolesModel};
use AvegaCms\Entities\{LoginEntity, RolesEntity, SettingsEntity, UserRolesEntity};

class AvegaCmsInstallSeeder extends Seeder
{
    public function run()
    {
        $user = model(LoginModel::class);
        $settings = model(SettingsModel::class);
        $roles = model(RolesModel::class);
        $userRoles = model(UserRolesModel::class);

        $userId = $user->insert(
            (new LoginEntity(
                [
                    'login'    => 'admin',
                    'email'    => 'admin@avegacms.ru',
                    'password' => 123456,
                    'status'   => 'active'
                ]
            ))
        );

        $rolesList = [
            [
                'role'          => 'root',
                'description'   => '',
                'color'         => '#',
                'path'          => '/',
                'priority'      => 1,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            [
                'role'          => 'admin',
                'description'   => '',
                'color'         => '#',
                'path'          => '/',
                'priority'      => 2,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            [
                'role'          => 'manager',
                'description'   => '',
                'color'         => '#',
                'path'          => '/',
                'priority'      => 3,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            [
                'role'          => 'content-manager',
                'description'   => '',
                'color'         => '#',
                'path'          => '/',
                'priority'      => 4,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ]
        ];

        $rolesEntity = new RolesEntity();

        foreach ($rolesList as $item) {
            $roles->insert($rolesEntity->fill($item));
        }

        $userRoles->insert(
            (new UserRolesEntity())->fill(
                [
                    'role_id'       => 1,
                    'user_id'       => $userId,
                    'created_by_id' => $userId,
                ]
            )
        );

        //'integer','float','string','boolean','array','datetime','timestamp','json'

        $settingsList = [
            [
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'timezone',
                'value'         => 'Europe/Moscow',
                'default_value' => 'Europe/Moscow',
                'return_type'   => 'string',
                'label'         => 'settings.label.env.timezone',
                'context'       => 'settings.context.env.timezone',
                'rules'         => 'required|timezone'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'secretKey',
                'value'         => bin2hex(random_bytes(32)),
                'default_value' => '',
                'return_type'   => 'string',
                'label'         => 'settings.label.env.secretKey',
                'context'       => 'settings.context.env.secretKey',
                'rules'         => 'required'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useCors',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => 'boolean',
                'label'         => 'settings.label.auth.useCors',
                'context'       => 'settings.context.auth.useCors',
                'rules'         => 'required|in_list[0,1]'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useSession',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => 'boolean',
                'label'         => 'settings.label.auth.useSession',
                'context'       => 'settings.context.auth.useSession',
                'rules'         => 'required|in_list[0,1]'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useToken',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => 'boolean',
                'label'         => 'settings.label.auth.useToken',
                'context'       => 'settings.context.auth.useToken',
                'rules'         => 'required|in_list[0,1]'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useJwt',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => 'boolean',
                'label'         => 'settings.label.auth.useJwt',
                'context'       => 'settings.context.auth.useJwt',
                'rules'         => 'required|in_list[0,1]'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtSecretKey',
                'value'         => bin2hex(random_bytes(32)),
                'default_value' => '',
                'return_type'   => 'string',
                'label'         => 'settings.label.auth.jwtSecretKey',
                'context'       => 'settings.context.auth.jwtSecretKey',
                'rules'         => 'required'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtSessionsLimit',
                'value'         => 3,
                'default_value' => 3,
                'return_type'   => 'integer',
                'label'         => 'settings.label.auth.jwtSessionsLimit',
                'context'       => 'settings.context.auth.jwtSessionsLimit',
                'rules'         => 'required'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtLiveTime',
                'value'         => 30,
                'default_value' => 30,
                'return_type'   => 'integer',
                'label'         => 'settings.label.auth.jwtLiveTime',
                'context'       => 'settings.context.auth.jwtLiveTime',
                'rules'         => 'required|integer'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtRefreshTime',
                'value'         => 30,
                'default_value' => 30,
                'return_type'   => 'integer',
                'label'         => 'settings.label.auth.jwtLiveTime',
                'context'       => 'settings.context.auth.jwtLiveTime',
                'rules'         => 'required|integer'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtAlg',
                'value'         => 'HS256',
                'default_value' => 'HS256',
                'return_type'   => 'string',
                'label'         => 'settings.label.auth.jwtAlg',
                'context'       => 'settings.context.auth.jwtAlg',
                'rules'         => 'required|in_list[ES384,ES256,ES256K,HS256,HS384,HS512,RS256,RS384,RS512]'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useWhiteIpList',
                'value'         => 1,
                'default_value' => 0,
                'return_type'   => 'boolean',
                'label'         => 'settings.label.auth.useWhiteIpList',
                'context'       => 'settings.context.auth.useWhiteIpList',
                'rules'         => 'required|in_list[0,1]'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'whiteIpList',
                'value'         => serialize([]),
                'default_value' => serialize([]),
                'return_type'   => 'array',
                'label'         => 'settings.label.auth.whiteIpList',
                'context'       => 'settings.context.auth.whiteIpList',
                'rules'         => 'required'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'loginTypeList',
                'value'         => serialize(
                    [
                        'login',
                        'email',
                        'phone',
                        'login:email',
                        'email:phone',
                        'login:email:phone'
                    ]
                ),
                'default_value' => serialize(
                    [
                        'login',
                        'email',
                        'phone',
                        'login:email',
                        'email:phone',
                        'login:email:phone'
                    ]
                ),
                'return_type'   => 'array',
                'label'         => 'settings.label.auth.loginTypeList',
                'context'       => 'settings.context.auth.loginTypeList',
                'rules'         => 'required|in_list[login,email,phone,login:email,email:phone,login:email:phone]'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'loginType',
                'value'         => 'email',
                'default_value' => 'email',
                'return_type'   => 'string',
                'label'         => 'settings.label.auth.loginType',
                'context'       => 'settings.context.auth.loginType',
                'rules'         => 'required|in_list[login,email,phone,login:email,email:phone,login:email:phone]'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'use2fa',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => 'boolean',
                'label'         => 'settings.label.auth.use2fa',
                'context'       => 'settings.context.auth.use2fa',
                'rules'         => 'required|in_list[0,1]'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => '2faField',
                'value'         => 'email',
                'default_value' => 'email',
                'return_type'   => 'string',
                'label'         => 'settings.label.auth.2faField',
                'context'       => 'settings.context.auth.2faField',
                'rules'         => 'required|in_list[email,phone]'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'verifyCodeLength',
                'value'         => 4,
                'default_value' => 4,
                'return_type'   => 'integer',
                'label'         => 'settings.label.auth.verifyCodeLength',
                'context'       => 'settings.context.auth.verifyCodeLength',
                'rules'         => 'required|integer|exact_length[4,5,6]'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'verifyCodeTime',
                'value'         => 5,
                'default_value' => 5,
                'return_type'   => 'integer',
                'label'         => 'settings.label.auth.verifyCodeTime',
                'context'       => 'settings.context.auth.verifyCodeTime',
                'rules'         => 'required|greater_than_equal_to[1]|less_than_equal_to[60]'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useRecovery',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => 'boolean',
                'label'         => 'settings.label.auth.useRecovery',
                'context'       => 'settings.context.auth.useRecovery',
                'rules'         => 'required|in_list[0,1]'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'recoveryField',
                'value'         => 'email',
                'default_value' => 'email',
                'return_type'   => 'string',
                'label'         => 'settings.label.auth.recoveryField',
                'context'       => 'settings.context.auth.recoveryField',
                'rules'         => 'required|in_list[login,email,phone]'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'recoveryCodeTime',
                'value'         => 10,
                'default_value' => 10,
                'return_type'   => 'integer',
                'label'         => 'settings.label.auth.recoveryCodeTime',
                'context'       => 'settings.context.auth.recoveryCodeTime',
                'rules'         => 'required|greater_than_equal_to[1]|less_than_equal_to[60]'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'authSmsMessage',
                'value'         => '',
                'default_value' => '',
                'return_type'   => 'string',
                'label'         => 'settings.label.auth.authSmsMessage',
                'context'       => 'settings.context.auth.authSmsMessage',
                'rules'         => 'required'
            ],
        ];

        $settingEntity = new SettingsEntity();

        foreach ($settingsList as $item) {
            $settings->insert($settingEntity->fill($item));
        }
    }
}
