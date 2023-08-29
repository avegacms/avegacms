<?php

namespace AvegaCms\Database\Seeds;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Seeder;
use Config\Database;
use CodeIgniter\CLI\CLI;
use AvegaCms\Config\AvegaCms;
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
    protected string         $version = AvegaCms::AVEGACMS_VERSION;
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
        $this->_createPublicFolders();

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
                'is_core'       => 1,
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
                'is_core'       => 1,
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
                'is_core'       => 1,
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
                'is_core'       => 1,
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
                    'is_core'       => 1,
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
                    'is_core'       => 1,
                    'is_plugin'     => 0,
                    'is_system'     => 1,
                    'slug'          => 'permissions',
                    'name'          => 'Cms.modules.name.permissions',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.permissions',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => 1,
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
                    'is_core'       => 1,
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
                    'is_core'       => 1,
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
                    'is_core'       => 1,
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
                [
                    'parent'        => $list['settings'],
                    'is_core'       => 1,
                    'is_plugin'     => 0,
                    'is_system'     => 1,
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
                    'parent'        => $list['settings'],
                    'is_core'       => 1,
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
                ]
            ],
            'content'  => [
                [
                    'parent'        => $list['content'],
                    'is_core'       => 1,
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
                    'is_core'       => 1,
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
                    'is_core'       => 1,
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
                    'is_core'       => 1,
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
                'module_id'     => 0,
                'is_module'     => 1,
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
                'is_module'     => 0,
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
                'is_module'     => 0,
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
                'is_module'     => 1,
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
                'is_module'     => 0,
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
                'is_module'     => 0,
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
                'is_module'     => 1,
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
                'is_module'     => 0,
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
                'is_module'     => 1,
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
                'is_module'     => 0,
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
                'is_module'     => 0,
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
                'is_module'     => 0,
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
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'timezone',
                'value'         => 'Europe/Moscow',
                'default_value' => 'Europe/Moscow',
                'return_type'   => 'string',
                'label'         => 'Settings.label.env.timezone',
                'context'       => 'Settings.context.env.timezone'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'secretKey',
                'value'         => bin2hex(random_bytes(32)),
                'default_value' => '',
                'return_type'   => 'string',
                'label'         => 'Settings.label.env.secretKey',
                'context'       => 'Settings.context.env.secretKey'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'defLanguage',
                'value'         => 'ru',
                'default_value' => 'ru',
                'return_type'   => 'string',
                'label'         => 'Settings.label.env.defLanguage',
                'context'       => 'Settings.context.env.defLanguage'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'useMultiLanguages',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => 'boolean',
                'label'         => 'Settings.label.env.useMultiLanguages',
                'context'       => 'Settings.context.env.useMultiLanguages'
            ],

            // auth
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useCors',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => 'boolean',
                'label'         => 'Settings.label.auth.useCors',
                'context'       => 'Settings.context.auth.useCors'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useSession',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => 'boolean',
                'label'         => 'Settings.label.auth.useSession',
                'context'       => 'Settings.context.auth.useSession'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 0,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useToken',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => 'boolean',
                'label'         => 'Settings.label.auth.useToken',
                'context'       => 'Settings.context.auth.useToken'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useJwt',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => 'boolean',
                'label'         => 'Settings.label.auth.useJwt',
                'context'       => 'Settings.context.auth.useJwt'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtSecretKey',
                'value'         => bin2hex(random_bytes(32)),
                'default_value' => '',
                'return_type'   => 'string',
                'label'         => 'Settings.label.auth.jwtSecretKey',
                'context'       => 'Settings.context.auth.jwtSecretKey'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtSessionsLimit',
                'value'         => 3,
                'default_value' => 3,
                'return_type'   => 'integer',
                'label'         => 'Settings.label.auth.jwtSessionsLimit',
                'context'       => 'Settings.context.auth.jwtSessionsLimit'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtLiveTime',
                'value'         => 30,
                'default_value' => 30,
                'return_type'   => 'integer',
                'label'         => 'Settings.label.auth.jwtLiveTime',
                'context'       => 'Settings.context.auth.jwtLiveTime'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtRefreshTime',
                'value'         => 30,
                'default_value' => 30,
                'return_type'   => 'integer',
                'label'         => 'Settings.label.auth.jwtLiveTime',
                'context'       => 'Settings.context.auth.jwtLiveTime'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtAlg',
                'value'         => 'HS256',
                'default_value' => 'HS256',
                'return_type'   => 'string',
                'label'         => 'Settings.label.auth.jwtAlg',
                'context'       => 'Settings.context.auth.jwtAlg'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useWhiteIpList',
                'value'         => 1,
                'default_value' => 0,
                'return_type'   => 'boolean',
                'label'         => 'Settings.label.auth.useWhiteIpList',
                'context'       => 'Settings.context.auth.useWhiteIpList'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'whiteIpList',
                'value'         => serialize([]),
                'default_value' => serialize([]),
                'return_type'   => 'array',
                'label'         => 'Settings.label.auth.whiteIpList',
                'context'       => 'Settings.context.auth.whiteIpList'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
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
                'label'         => 'Settings.label.auth.loginTypeList',
                'context'       => 'Settings.context.auth.loginTypeList'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'loginType',
                'value'         => 'email',
                'default_value' => 'email',
                'return_type'   => 'string',
                'label'         => 'Settings.label.auth.loginType',
                'context'       => 'Settings.context.auth.loginType'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'use2fa',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => 'boolean',
                'label'         => 'Settings.label.auth.use2fa',
                'context'       => 'Settings.context.auth.use2fa'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => '2faField',
                'value'         => 'email',
                'default_value' => 'email',
                'return_type'   => 'string',
                'label'         => 'Settings.label.auth.2faField',
                'context'       => 'Settings.context.auth.2faField'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'verifyCodeLength',
                'value'         => 4,
                'default_value' => 4,
                'return_type'   => 'integer',
                'label'         => 'Settings.label.auth.verifyCodeLength',
                'context'       => 'Settings.context.auth.verifyCodeLength'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'verifyCodeTime',
                'value'         => 5,
                'default_value' => 5,
                'return_type'   => 'integer',
                'label'         => 'Settings.label.auth.verifyCodeTime',
                'context'       => 'Settings.context.auth.verifyCodeTime'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useRecovery',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => 'boolean',
                'label'         => 'Settings.label.auth.useRecovery',
                'context'       => 'Settings.context.auth.useRecovery'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'recoveryField',
                'value'         => 'email',
                'default_value' => 'email',
                'return_type'   => 'string',
                'label'         => 'Settings.label.auth.recoveryField',
                'context'       => 'Settings.context.auth.recoveryField'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'recoveryCodeTime',
                'value'         => 10,
                'default_value' => 10,
                'return_type'   => 'integer',
                'label'         => 'Settings.label.auth.recoveryCodeTime',
                'context'       => 'Settings.context.auth.recoveryCodeTime'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'authSmsMessage',
                'value'         => '',
                'default_value' => '',
                'return_type'   => 'string',
                'label'         => 'Settings.label.auth.authSmsMessage',
                'context'       => 'Settings.context.auth.authSmsMessage'
            ]
        ];

        $settingEntity = new SettingsEntity();

        foreach ($settingsList as $item) {
            $this->SM->insert($settingEntity->fill($item));
        }
    }

    /**
     * @return void
     */
    private function _createPublicFolders(): void
    {
        $directories = [
            'uploads',
            'uploads/users',
            'uploads/content',
            'uploads/content/thumbs'
        ];

        foreach ($directories as $directory) {
            if ( ! is_dir($directory = FCPATH . $directory) && mkdir($directory, 0777, true)) {
                file_put_contents($directory . '/index.html', '');
            } else {
                CLI::write('Can\'t create directory: ' . $directory);
            }
        }
    }
}
