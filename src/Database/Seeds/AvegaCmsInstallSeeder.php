<?php

namespace AvegaCms\Database\Seeds;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Seeder;
use Config\Database;
use AvegaCms\Models\Admin\{ModulesModel,
    SettingsModel,
    LoginModel,
    RolesModel,
    UserRolesModel,
    PermissionsModel,
    LocalesModel
};
use AvegaCms\Entities\{ModulesEntity,
    LoginEntity,
    RolesEntity,
    SettingsEntity,
    UserRolesEntity,
    PermissionsEntity,
    LocalesEntity
};
use ReflectionException;
use Exception;

class AvegaCmsInstallSeeder extends Seeder
{
    protected string         $version = '0.0.0.1';
    protected ModulesModel   $MM;
    protected LoginModel     $LM;
    protected SettingsModel  $SM;
    protected RolesModel     $RM;
    protected UserRolesModel $URM;

    protected PermissionsModel $PM;

    protected LocalesModel $LLM;

    public function __construct(Database $config, ?BaseConnection $db = null)
    {
        parent::__construct($config, $db);

        $this->MM = model(ModulesModel::class);
        $this->LM = model(LoginModel::class);
        $this->SM = model(SettingsModel::class);
        $this->RM = model(RolesModel::class);
        $this->PM = model(PermissionsModel::class);
        $this->URM = model(UserRolesModel::class);
        $this->LLM = model(LocalesModel::class);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function run(): void
    {
        $userId = $this->_createUser();
        $this->_createRoles($userId);
        $this->_createUserRoles($userId);
        $this->_installCmsModules($userId);
        $this->_createPermissions($userId);
        $this->_createLocales($userId);
        $this->_createSettings();
        cache()->clean();
    }

    /**
     * @return int
     * @throws ReflectionException
     */
    private function _createUser(): int
    {
        return $this->LM->insert(
            (new LoginEntity(
                [
                    'login'    => 'admin',
                    'email'    => 'admin@avegacms.ru',
                    'password' => 123456,
                    'status'   => 'active'
                ]
            ))
        );
    }

    /**
     * @param  int  $userId
     * @return void
     * @throws ReflectionException
     */
    private function _createRoles(int $userId): void
    {
        $roles = [
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
                'role'          => 'default',
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

        foreach ($roles as $role) {
            $this->RM->insert($rolesEntity->fill($role));
        }
    }

    /**
     * @param  int  $userId
     * @return void
     * @throws ReflectionException
     */
    private function _createUserRoles(int $userId): void
    {
        $this->URM->insert(
            (new UserRolesEntity())->fill(
                [
                    'role_id'       => 1,
                    'user_id'       => $userId,
                    'created_by_id' => $userId,
                ]
            )
        );
    }

    /**
     * @param  int  $userId
     * @return void
     * @throws ReflectionException
     */
    private function _installCmsModules(int $userId): void
    {
        $modules = [
            [
                'parent'        => 0,
                'is_plugin'     => 0,
                'is_system'     => 0,
                'slug'          => 'settings',
                'name'          => 'Cms.modules.name.settings',
                'version'       => $this->version,
                'description'   => 'Cms.modules.description.settings',
                'extra'         => '',
                'in_sitemap'    => 0,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            [
                'parent'        => 0,
                'is_plugin'     => 0,
                'is_system'     => 0,
                'slug'          => 'menu',
                'name'          => 'Cms.modules.name.menu',
                'version'       => $this->version,
                'description'   => 'Cms.modules.description.menu',
                'extra'         => '',
                'in_sitemap'    => 0,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            [
                'parent'        => 0,
                'is_plugin'     => 0,
                'is_system'     => 0,
                'slug'          => 'content',
                'name'          => 'Cms.modules.name.content',
                'version'       => $this->version,
                'description'   => 'Cms.modules.description.content',
                'extra'         => '',
                'in_sitemap'    => 0,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            [
                'parent'        => 0,
                'is_plugin'     => 1,
                'is_system'     => 1,
                'slug'          => 'content_builder',
                'name'          => 'Cms.modules.name.content_builder',
                'version'       => $this->version,
                'description'   => 'Cms.modules.description.content_builder',
                'extra'         => '',
                'in_sitemap'    => 0,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            [
                'parent'        => 0,
                'is_plugin'     => 1,
                'is_system'     => 1,
                'slug'          => 'uploader',
                'name'          => 'Cms.modules.name.uploader',
                'version'       => $this->version,
                'description'   => 'Cms.modules.description.uploader',
                'extra'         => '',
                'in_sitemap'    => 0,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ]
        ];

        foreach ($modules as $module) {
            $modulesEntity[] = (new ModulesEntity($module));
        }

        $this->MM->insertBatch($modulesEntity);
        unset($modulesEntity);

        $subModules = $this->MM->select(['id', 'slug'])->whereIn('slug', ['settings', 'content'])->findAll();

        $list = [];

        foreach ($subModules as $subModule) {
            $list[$subModule->slug] = $subModule->id;
        }

        $modules = [

            'settings' => [
                [
                    'parent'        => $list['settings'],
                    'is_plugin'     => 0,
                    'is_system'     => 1,
                    'slug'          => 'roles',
                    'name'          => 'Cms.modules.name.roles',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.roles',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['settings'],
                    'is_plugin'     => 0,
                    'is_system'     => 1,
                    'slug'          => 'users',
                    'name'          => 'Cms.modules.name.users',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.users',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['settings'],
                    'is_plugin'     => 0,
                    'is_system'     => 1,
                    'slug'          => 'modules',
                    'name'          => 'Cms.modules.name.modules',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.modules',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['settings'],
                    'is_plugin'     => 0,
                    'is_system'     => 1,
                    'slug'          => 'locales',
                    'name'          => 'Cms.modules.name.locales',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.locales',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['settings'],
                    'is_plugin'     => 0,
                    'is_system'     => 0,
                    'slug'          => 'seo',
                    'name'          => 'Cms.modules.name.seo',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.seo',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
            ],
            'content'  => [
                [
                    'parent'        => $list['content'],
                    'is_plugin'     => 0,
                    'is_system'     => 0,
                    'slug'          => 'rubrics',
                    'name'          => 'Cms.modules.name.rubrics',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.rubrics',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['content'],
                    'is_plugin'     => 0,
                    'is_system'     => 0,
                    'slug'          => 'pages',
                    'name'          => 'Cms.modules.name.pages',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.pages',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['content'],
                    'is_plugin'     => 0,
                    'is_system'     => 0,
                    'slug'          => 'posts',
                    'name'          => 'Cms.modules.name.posts',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.posts',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['content'],
                    'is_plugin'     => 0,
                    'is_system'     => 0,
                    'slug'          => 'tags',
                    'name'          => 'Cms.modules.name.tags',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.tags',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ]
            ]
        ];

        $modulesEntity = [];

        foreach ($subModules as $subModule) {
            foreach ($modules as $slug => $list) {
                foreach ($list as $item) {
                    if ($slug === $subModule->slug) {
                        $item['parent'] = $subModule->id;
                        $modulesEntity[] = (new ModulesEntity($item));
                    }
                }
            }
        }

        $this->MM->insertBatch($modulesEntity);
    }

    /**
     * @param  int  $userId
     * @return void
     * @throws ReflectionException
     */
    private function _createPermissions(int $userId): void
    {
        $permissions = [
            // Default permission Module
            [
                'role_id'       => 0,
                'parent'        => 0,
                'module_id'     => 1,
                'is_system'     => 0,
                'is_plugin'     => 0,
                'slug'          => '',
                'access'        => 0,
                'self'          => 0,
                'create'        => 0,
                'read'          => 0,
                'update'        => 0,
                'delete'        => 0,
                'moderated'     => 0,
                'settings'      => 0,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            // Default permission System
            [
                'role_id'       => 0,
                'parent'        => 0,
                'module_id'     => 0,
                'is_system'     => 1,
                'is_plugin'     => 0,
                'slug'          => '',
                'access'        => 0,
                'self'          => 0,
                'create'        => 0,
                'read'          => 0,
                'update'        => 0,
                'delete'        => 0,
                'moderated'     => 0,
                'settings'      => 0,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            // Default permission Plugin
            [
                'role_id'       => 0,
                'parent'        => 0,
                'module_id'     => 0,
                'is_system'     => 0,
                'is_plugin'     => 1,
                'slug'          => '',
                'access'        => 0,
                'self'          => 0,
                'create'        => 0,
                'read'          => 0,
                'update'        => 0,
                'delete'        => 0,
                'moderated'     => 0,
                'settings'      => 0,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],

            // root Module
            [
                'role_id'       => 1,
                'parent'        => 0,
                'module_id'     => 0,
                'is_system'     => 0,
                'is_plugin'     => 0,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 1,
                'read'          => 1,
                'update'        => 1,
                'delete'        => 1,
                'moderated'     => 0,
                'settings'      => 1,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            // root System
            [
                'role_id'       => 1,
                'parent'        => 0,
                'module_id'     => 0,
                'is_system'     => 1,
                'is_plugin'     => 0,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 1,
                'read'          => 1,
                'update'        => 1,
                'delete'        => 1,
                'moderated'     => 0,
                'settings'      => 1,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            // root Plugin
            [
                'role_id'       => 1,
                'parent'        => 0,
                'module_id'     => 0,
                'is_system'     => 0,
                'is_plugin'     => 1,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 1,
                'read'          => 1,
                'update'        => 1,
                'delete'        => 1,
                'moderated'     => 0,
                'settings'      => 1,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],

            // Admin Module
            [
                'role_id'       => 2,
                'parent'        => 0,
                'module_id'     => 0,
                'is_system'     => 0,
                'is_plugin'     => 0,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 1,
                'read'          => 1,
                'update'        => 1,
                'delete'        => 1,
                'moderated'     => 0,
                'settings'      => 1,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            // Admin Plugin
            [
                'role_id'       => 2,
                'parent'        => 0,
                'module_id'     => 0,
                'is_system'     => 0,
                'is_plugin'     => 1,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 1,
                'read'          => 1,
                'update'        => 1,
                'delete'        => 1,
                'moderated'     => 0,
                'settings'      => 1,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],

            // Manager Module
            [
                'role_id'       => 3,
                'parent'        => 0,
                'module_id'     => 0,
                'is_system'     => 0,
                'is_plugin'     => 0,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 1,
                'read'          => 1,
                'update'        => 1,
                'delete'        => 1,
                'moderated'     => 0,
                'settings'      => 0,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            // Manager Plugin
            [
                'role_id'       => 3,
                'parent'        => 0,
                'module_id'     => 0,
                'is_system'     => 0,
                'is_plugin'     => 1,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 1,
                'read'          => 1,
                'update'        => 1,
                'delete'        => 1,
                'moderated'     => 0,
                'settings'      => 0,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],

            // Default Module
            [
                'role_id'       => 4,
                'parent'        => 0,
                'module_id'     => 0,
                'is_system'     => 0,
                'is_plugin'     => 0,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 0,
                'read'          => 0,
                'update'        => 0,
                'delete'        => 0,
                'moderated'     => 0,
                'settings'      => 0,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            // Default Plugin
            [
                'role_id'       => 4,
                'parent'        => 0,
                'module_id'     => 0,
                'is_system'     => 0,
                'is_plugin'     => 1,
                'slug'          => '',
                'access'        => 1,
                'self'          => 0,
                'create'        => 0,
                'read'          => 0,
                'update'        => 0,
                'delete'        => 0,
                'moderated'     => 0,
                'settings'      => 0,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ]
        ];

        foreach ($permissions as $permission) {
            $defPermissions[] = (new PermissionsEntity($permission));
        }

        $this->PM->insertBatch($defPermissions);

        $modules = $this->MM->select(['id', 'parent', 'is_system', 'is_plugin', 'slug'])->findAll();
        $roles = $this->RM->select(['id', 'role'])->findAll();

        foreach ($modules as $module) {
            foreach ($roles as $role) {
                foreach ($permissions as $permission) {
                    if ($permission['role_id'] === $role->id && $module->is_system === $permission['is_system'] && $module->is_plugin === $permission['is_plugin']) {
                        $perm = $permission;
                        $perm['parent'] = $module->parent;
                        $perm['module_id'] = $module->id;
                        $perm['slug'] = $module->slug;

                        $moduleRolePermission[] = (new PermissionsEntity($perm));
                    }
                }
            }
        }

        $this->PM->insertBatch($moduleRolePermission ?? null);
    }

    /**
     * @param  int  $userId
     * @return void
     * @throws ReflectionException
     */
    private function _createLocales(int $userId): void
    {
        $locales = [
            [
                'slug'          => 'ru',
                'locale'        => 'ru_RU',
                'locale_name'   => 'Русская версия',
                'home'          => 'Главная',
                'is_default'    => 1,
                'active'        => 1,
                'created_by_id' => $userId
            ],
            [
                'slug'          => 'en',
                'locale'        => 'en_EN',
                'locale_name'   => 'English version',
                'home'          => 'Home',
                'is_default'    => 0,
                'active'        => 0,
                'created_by_id' => $userId
            ],
            [
                'slug'          => 'de',
                'locale'        => 'de_DE',
                'locale_name'   => 'Deutsche version',
                'home'          => 'Startseite',
                'is_default'    => 0,
                'active'        => 0,
                'created_by_id' => $userId
            ]
        ];

        foreach ($locales as $locale) {
            $localesEntity[] = (new LocalesEntity($locale));
        }

        $this->LLM->insertBatch($localesEntity);
    }

    /**
     * @return void
     * @throws ReflectionException|Exception
     */
    private function _createSettings(): void
    {
        $settingsList = [
            // .env
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
                'slug'          => 'env',
                'key'           => 'defLanguage',
                'value'         => 'ru',
                'default_value' => 'ru',
                'return_type'   => 'string',
                'label'         => 'settings.label.env.defLanguage',
                'context'       => 'settings.context.env.defLanguage',
                'rules'         => 'required|timezone'
            ],
            [
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'useMultiLanguages',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => 'boolean',
                'label'         => 'settings.label.env.useMultiLanguages',
                'context'       => 'settings.context.env.useMultiLanguages',
                'rules'         => 'required|timezone'
            ],

            // auth
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
            $this->SM->insert($settingEntity->fill($item));
        }
    }
}
